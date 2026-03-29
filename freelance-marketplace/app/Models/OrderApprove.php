<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderApprove extends Model
{
    use HasFactory;

    // Allow mass assignment
    protected $fillable = [
        'user_id',
        'order_id',
        'comment',
        'proposed_budget',
        'proposed_deadline_in_days',
    ];

    /**
     * The user who approved the order
     **/
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The order being approved
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}