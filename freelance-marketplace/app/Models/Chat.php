<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model {
    protected $fillable = [
        'creator_id',
        'participant_id',
        'order_id',
    ];

    public function messages() : HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function participant()
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    public function hasUnreadMessages()
    {
        return $this->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count() > 0;
    }
}