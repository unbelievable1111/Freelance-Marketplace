<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'card_number',
        'user_id',
    ];
}