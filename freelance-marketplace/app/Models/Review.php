<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'author_id',
        'target_id',
        'order_id',
        'score',
        'feedback',
    ];

    public function author() : BelongsTo 
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function target() : BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    public function order() : BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}