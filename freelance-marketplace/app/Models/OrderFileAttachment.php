<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFileAttachment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stored_filename',
        'original_filename',
        'order_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}