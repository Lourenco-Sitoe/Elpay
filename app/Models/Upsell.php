<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upsell extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_product_id', 'upsell_product_id', 'special_price',
        'downsell_product_id', 'downsell_price', 'active',
    ];

    protected function casts(): array
    {
        return [
            'special_price' => 'decimal:2', 'downsell_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function upsellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsell_product_id');
    }

    public function downsellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'downsell_product_id');
    }
}
