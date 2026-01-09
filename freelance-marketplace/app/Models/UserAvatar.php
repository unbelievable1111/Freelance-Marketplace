<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAvatar extends Model
{
    public $timestamps = false;

    protected $fillable = ['path', 'user_id', ];
}