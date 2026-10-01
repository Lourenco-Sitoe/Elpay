<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'producer_id', 'product_id', 'code', 'percent',
        'max_uses', 'used_count', 'expires_at', 'active',
    ];

    protected function casts(): array
    {
        return ['percent' => 'decimal:2', 'expires_at' => 'datetime', 'active' => 'boolean'];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
