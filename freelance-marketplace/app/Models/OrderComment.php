<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderComment extends Model
{
    protected $fillable = 
    [
        'value',
        'user_id',
        'order_id',
    ];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fileAttachments() : HasMany
    {
        return $this->hasMany(OrderCommentFileAttachment::class);
    }
}