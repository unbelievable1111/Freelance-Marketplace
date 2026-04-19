<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    public $timestamps = false;

    protected $fillable = 
    [
        'name',
        'description',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'notification_types_id');
    }

    static public function getByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }
}