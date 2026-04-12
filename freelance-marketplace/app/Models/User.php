<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function UserRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id', 'id');
    }

    public function UserAvatar(): HasOne
    {
        return $this->hasOne(UserAvatar::class, 'user_id', 'id');
    }

    public function getAvatarAttribute(): string
    {
        return $this->UserAvatar?->path ? asset('storage/avatars/' . $this->UserAvatar->path) : asset('storage/avatars/no-avatar.png');
    }

    public function BankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'user_id', 'id');
    }

    public function Balance(): HasOne
    {
        return $this->hasOne(Balance::class, 'user_id', 'id');
    }

    public function ordersAsCustomer(): HasMany
    {
        if ($this->UserRole->name !== 'customer') {
            throw new \Exception('User is not a customer');
        }

        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function ordersAsExecutor(): HasMany
    {
        if ($this->UserRole->name !== 'executor') {
            throw new \Exception('User is not a executor');
        }

        return $this->hasMany(Order::class, 'executor_id', 'id');
    }

    public function approves(): HasMany
    {
        if ($this->UserRole->name !== 'executor') {
            throw new \Exception('User is not a executor');
        }

        return $this->hasMany(OrderApprove::class, 'user_id', 'id');
    }

    public function Reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'target_id', 'id');
    }

    public function getAverageRatingAttribute()
    {
        return Review::where('target_id', $this->id)->avg('score');
    }

    public function isExecutor()
    {
        return $this->UserRole->name === 'executor';
    }

    public function isCustomer()
    {
        return $this->UserRole->name === 'customer';
    }

    public function hasReviews()
    {
        return $this->Reviews()->exists();
    }
}