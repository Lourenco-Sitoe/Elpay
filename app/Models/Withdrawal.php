<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'amount', 'fee', 'net', 'method',
        'phone', 'status', 'rejection_reason', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', 'fee' => 'decimal:2',
            'net' => 'decimal:2', 'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
