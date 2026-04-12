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

    protected $casts = [ 'deadline_date' => 'datetime' ];

    public function fileAttachments() : HasMany
    {
        return $this->hasMany(OrderFileAttachment::class);
    }

    public function status() : HasOne
    {
        return $this->hasOne(OrderStatus::class, 'id', 'status_id');
    }

    public function customer() : HasOne
    {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

    public function executor() : HasOne
    {
        return $this->hasOne(User::class, 'id', 'executor_id');
    }

    public function subCategory() : HasOne
    {
        return $this->hasOne(SubOrderCategory::class, 'id', 'sub_category_id');
    }

    public function orderApproves() : HasMany
    {
        return $this->hasMany(OrderApprove::class);
    }

    public function isInProgress() : bool
    {
        return $this->status_id == OrderStatus::where('name', 'in_progress')->first()->id; 
    }

    public function isPublished() : bool
    {
        return $this->status_id == OrderStatus::where('name', 'published')->first()->id; 
    }

    public function isExpired() : bool
    {
        return $this->status_id == OrderStatus::where('name', 'expired')->first()->id; 
    }

    public function isCompleted() : bool
    {
        return $this->status_id == OrderStatus::where('name', 'completed')->first()->id; 
    }

    public function comments() : HasMany
    {
        return $this->hasMany(OrderComment::class); 
    }

    public function canBeCanceledBy($user)
    {
        $role = $user->UserRole->name;

        return (
            ($role === 'customer' && ($this->isInProgress() || $this->isExpired())) ||
            ($role === 'executor' && $this->isInProgress())
        );
    }

    public function canExtendDeadlineBy($user)
    {
        return $user->UserRole->name === 'customer'
            && $this->deadline_date
            && ($this->isInProgress() || $this->isExpired());
    }

    public function canBeCompletedBy($user)
    {
        return $user->UserRole->name === 'customer'
            && $this->isInProgress();
    }

    public function checkDeadline()
    {
        if ($this->executor_id !== null) 
        {
            if ($this->deadline_date && now()->greaterThan($this->deadline_date) && $this->status()->first()->name === 'in_progress') {
                $this->status_id = OrderStatus::where('name', 'expired')->value('id');
            }

            if ($this->deadline_date && now()->lessThan($this->deadline_date) && $this->status()->first()->name === 'expired') {
                $this->status_id = OrderStatus::where('name', 'in_progress')->value('id');
            }

            $this->save();

            return;
        }
    }

    public function hasReviewForCustomer()
    {
        return Review::where('order_id', $this->id)
            ->where(function ($q) {
                $q->where('author_id', $this->executor_id);
            })->exists();
    }

    public function hasReviewForExecutor()
    {
        return Review::where('order_id', $this->id)
            ->where(function ($q) {
                $q->where('author_id', $this->customer_id);
            })->exists();
    }

    public function reviewForExecutor()
    {
        return Review::where('order_id', $this->id)
            ->where(function ($q) {
                $q->where('author_id', $this->customer_id);
            })->first();
    }

    public function reviewForCustomer()
    {
        return Review::where('order_id', $this->id)
            ->where(function ($q) {
                $q->where('author_id', $this->executor_id);
            })->first();
    }

    public function userBelongsToOrder()
    {
        return auth()->id() === $this->customer_id || auth()->id() === $this->executor_id;
    }
}