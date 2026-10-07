<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'type', 'status',
        'phone', 'balance', 'api_token', 'avatar', 'email_verified_at',
        'specialty', 'locale', 'theme',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'producer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'producer_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Sale::class, 'buyer_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(KycVerification::class);
    }

    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'affiliate_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }
}
