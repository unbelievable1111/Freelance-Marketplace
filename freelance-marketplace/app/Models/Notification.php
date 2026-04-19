<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = 
    [
        'user_id',
        'notification_types_id',
        'title',
        'message',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notificationType()
    {
        return $this->belongsTo(NotificationType::class, 'notification_types_id');
    }

    static public function createNotification(User $user, NotificationType $notificationType, string $title, string $message): self
    {
        return self::create([
            'user_id' => $user->id,
            'notification_types_id' => $notificationType->id,
            'title' => $title,
            'message' => $message,
        ]);
    }

    static public function getUnreadAmount(User $user): int
    {
        return self::where('user_id', $user->id)->where('is_read', false)->count();
    }

    public function markAsRead(): void
    {
        $this->is_read = true;
        $this->save();
    }
}