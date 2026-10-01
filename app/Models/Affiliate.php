<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affiliate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'affiliate_code', 'status',
        'custom_rate', 'balance', 'total_earned', 'total_sales',
    ];

    protected function casts(): array
    {
        return [
            'custom_rate' => 'decimal:2', 'balance' => 'decimal:2',
            'total_earned' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
