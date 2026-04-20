<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description'
    ];

    public function reports()
    {
        return $this->hasMany(Report::class, 'status_id');
    }

    static function getStatusByName($name)
    {
        return self::where('name', $name)->first();
    } 
}
