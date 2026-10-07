<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'title', 'kind', 'content', 'file_url', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
