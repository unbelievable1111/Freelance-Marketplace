<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->with('notificationType')
            ->latest()
            ->paginate(25);

        $ids = $notifications->getCollection()
            ->where('is_read', false)
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            auth()->user()
                ->notifications()
                ->whereIn('id', $ids)
                ->update(['is_read' => true]);
        }

        return view('components.pages.notifications.index', compact('notifications'));
    }

    public function getUnreadCount()
    {
        $unreadCount = Notification::getUnreadAmount(auth()->user());

        return response()->json(['count' => $unreadCount]);
    }
}