<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = 
    [
        'user_id',
        'order_id',
        'amount',
        'transaction_type_id',
        'bank_account_id',
        'related_user_id',
        'transfer_uuid',
        'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function TransactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, "transaction_type_id", "id");
    }

    public function BankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, "bank_account_id", "id");
    }

    public function ReletedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, "related_user_id", "id");
    }

    public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class, "order_id", "id");
    }
}