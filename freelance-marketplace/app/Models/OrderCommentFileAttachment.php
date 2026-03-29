<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCommentFileAttachment extends Model 
{
    public $timestamps = false;

    protected $fillable = [
        'stored_filename',
        'original_filename',
        'order_comment_id',
    ];

    public function orderComment() : BelongsTo
    {
        return $this->belongsTo(OrderComment::class);
    }
}