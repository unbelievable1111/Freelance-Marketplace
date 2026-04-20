<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = 
    [
        'title',
        'description',
        'reporter_id',
        'order_id',
        'status_id'
    ];

    public function comments()
    {
        return $this->hasMany(ReportComment::class, 'report_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function status()
    {
        return $this->belongsTo(ReportStatus::class, 'status_id');
    }
}