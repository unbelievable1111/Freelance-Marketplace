<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class OrderCommentController extends Controller
{
    public function leaveComment(Request $request, Order $order)
    {
        if (Auth::id() !== $order->customer_id && Auth::id() !== $order->executor_id) {
            return redirect()->back()->with('leaveCommentError', 'You do not have permission to add attachments to this order.');
        }

        if (!$order->isInProgress() && !$order->isExpired()) 
        {
            return redirect()->back()->with('leaveCommentError', 'Only orders in progress or expired status can have comments added.');
        }

        try 
        {
            $request->validate([
                'value' => ['bail', 'required', 'string', 'max:1024'],
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => [
                    'bail',
                    'file',
                    'mimes:png,jpg,jpeg,pdf,doc,docx,csv,xls,xlsx,txt',
                    'max:10240',
                ],
            ]);

            DB::beginTransaction();
            $attachments = $request->file('attachments');

            $createdComment = $order->comments()->create
            ([
                'value' => $request->input('value'),
                'user_id' => Auth::id(),
            ]);

            $storedFiles = [];

            if ($attachments) {
                foreach ($attachments as $attachment) 
                {
                    $originalName = $attachment->getClientOriginalName();
                    $storedName = Str::uuid() . '.' . $attachment->extension();

                    Storage::disk('public')->putFileAs
                    (
                        'public_order_attachments',
                        $attachment,
                        $storedName
                    );

                    $storedFiles[] = $storedName;

                    $createdComment->fileAttachments()->create([
                        'stored_filename' => $storedName,
                        'original_filename' => $originalName,
                        'order_id' => $order->id,
                    ]);
                }
            }

            #Notification
            $reciever = Auth::id() === $order->customer_id ? User::find($order->executor_id) : User::find($order->customer_id);
            Notification::createNotification(
                $reciever,
                NotificationType::getByName('order_comment_received'),
                'Order comment received',
                'You have received a new comment on order ' .
                    '<a href="' . route('order.show-order', $order->id) . '" class="text-decoration-none">"' . e($order->title) . '"</a>' .
                    ' from user ' .
                    '<a href="' . route('public-profile.overview', Auth::id()) . '" class="text-decoration-none">' . e(Auth::user()->name) . '</a>' .
                    '. Open the <a href="' . route('order.show-order', $order->id) . '" class="text-decoration-none">order</a> to view the comment.'
            );

            DB::commit();
            
            return redirect()->back()->with('leaveCommentSuccess', 'Comment added successfully!');
        }
        catch (\Exception $e) 
        {
            return redirect()->back()->with('leaveCommentError', 'Failed to leave comment: ' . $e->getMessage());
        }
    }

    public function addAttachment(Request $request, Order $order) 
    {
    }
}