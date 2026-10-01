<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'producer_id', 'title', 'slug', 'description', 'price', 'type',
        'file_path', 'external_url', 'cover', 'commission_rate',
        'status', 'rejection_reason', 'sales_count',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'commission_rate' => 'decimal:2'];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function affiliates(): HasMany
    {
        return $this->hasMany(Affiliate::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function bumps(): HasMany
    {
        return $this->hasMany(Bump::class, 'main_product_id');
    }

    public function upsells(): HasMany
    {
        return $this->hasMany(Upsell::class, 'main_product_id');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('position');
    }
}
