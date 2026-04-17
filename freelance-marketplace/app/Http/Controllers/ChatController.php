<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\User;
use App\Models\Order;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function getUserChats()
    {
        $latestMessages = ChatMessage::selectRaw('chat_id, MAX(created_at) as last_message_at')
            ->groupBy('chat_id');

        return Chat::query()
            ->where(function ($q) {
                $q->where('creator_id', auth()->id())
                    ->orWhere('participant_id', auth()->id());
            })
            ->leftJoinSub($latestMessages, 'lm', function ($join) {
                $join->on('chats.id', '=', 'lm.chat_id');
            })
            ->orderByRaw('lm.last_message_at IS NULL, lm.last_message_at DESC')
            ->select('chats.*', 'lm.last_message_at')
            ->paginate(25);
    }

    public function index()
    {
        $chats = $this->getUserChats();
        return view('components.pages.chats.index', compact('chats'));
    }

    public function show(Chat $chat)
    {
        $chats = $this->getUserChats();

        if ($chat->creator_id === auth()->id() || $chat->participant_id === auth()->id()) {
            $chat->messages()
                ->where('sender_id', '!=', auth()->id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        } else {
            abort(403);
        }

        $messages = $chat->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->reverse();

        if (request()->ajax()) {
            return view('components.pages.chats.new-messages', compact('messages'))->render();
        }

        return view('components.pages.chats.index', compact('chats', 'chat', 'messages'));
    }

    public function getNewMessages(Chat $chat)
    {
        $chats = $this->getUserChats();
        $lastId = request('last_id');

        $messages = $chat->messages()
            ->with('sender')
            ->where('id', '>', $lastId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('components.pages.chats.new-messages', compact('messages'));
    }

    public function startChat(Order $order, User $receiver)
    {
        $chat = Chat::where(function ($q) use ($receiver, $order) {
            $q->where('creator_id', auth()->id())
                ->where('participant_id', $receiver->id)
                ->where('order_id', $order->id);
        })
            ->orWhere(function ($q) use ($receiver, $order) {
                $q->where('creator_id', $receiver->id)
                    ->where('participant_id', auth()->id())
                    ->where('order_id', $order->id);
            })
            ->first();

        if (!$chat) {
            $chat = Chat::create([
                'creator_id'     => auth()->id(),
                'participant_id' => $receiver->id,
                'order_id'       => $order->id,
            ]);
        }

        return redirect()->route('chat.show', $chat);
    }

    public function sendMessage(Chat $chat)
    {
        $this->validate(request(), [
            'message' => 'required|string|max:500',
        ]);

        if ($chat->creator_id !== auth()->id() && $chat->participant_id !== auth()->id()) {
            abort(403);
        }

        $message = $chat->messages()->create([
            'sender_id' => auth()->id(),
            'message'   => request('message'),
            'is_read'   => false,
        ]);

        return view('components.pages.chats.message', compact('message'));
    }

    public function getOlderMessages(Chat $chat)
    {
        if ($chat->creator_id !== auth()->id() && $chat->participant_id !== auth()->id()) {
            abort(403);
        }

        $firstId = request('first_id');

        $messages = $chat->messages()
            ->with('sender')
            ->where('id', '<', $firstId)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get()
            ->reverse();

        return view('components.pages.chats.new-messages', compact('messages'));
    }

    public function getUnreadStatus()
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return response()->json([]);
            }

            $chats = Chat::where(function ($q) use ($userId) {
                $q->where('creator_id', $userId)
                ->orWhere('participant_id', $userId);
            })->get();

            $result = [];

            foreach ($chats as $chat) {
                $result[$chat->id] = $chat->messages()
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', false)
                    ->exists();
            }

            return response()->json($result);

        } catch (\Throwable $e) {
            \Log::error('UnreadStatus error: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Server error'
            ], 500);
        }
    }
}