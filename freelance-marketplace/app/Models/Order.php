<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'title',
        'requirement_skills',
        'short_description',
        'full_description',
        'budget',
        'customer_id',
        'executor_id',
        'status_id',
        'sub_category_id',
        'deadline_in_days',
        'deadline_date',
    ];

    public function fileAttachments() : HasMany
    {
        return $this->hasMany(OrderFileAttachment::class);
    }

    public function status() : HasOne
    {
        return $this->hasOne(OrderStatus::class, 'id', 'status_id');
    }

    public function user() : HasOne
    {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

    public function subCategory() : HasOne
    {
        return $this->hasOne(SubOrderCategory::class, 'id', 'sub_category_id');
    }
}