<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pixel extends Model
{
    use HasFactory;

    protected $fillable = ['producer_id', 'name', 'platform', 'pixel_id', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }
}
