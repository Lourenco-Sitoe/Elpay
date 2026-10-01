<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bump extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_product_id', 'bump_product_id', 'title', 'description', 'price', 'active',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'active' => 'boolean'];
    }

    public function mainProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'main_product_id');
    }

    public function bumpProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'bump_product_id');
    }
}
