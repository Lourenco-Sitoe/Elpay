<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;

    public const EVENTS = [
        'sale.paid' => 'Venda paga',
        'commission.created' => 'Comissão criada',
        'withdrawal.requested' => 'Saque solicitado',
        'withdrawal.approved' => 'Saque aprovado',
    ];

    protected $fillable = ['producer_id', 'name', 'url', 'events', 'secret', 'active'];

    protected function casts(): array
    {
        return ['events' => 'array', 'active' => 'boolean'];
    }

    public function producer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class)->latest();
    }

    public function wants(string $event): bool
    {
        $events = $this->events ?? [];

        return in_array($event, $events, true) || in_array('*', $events, true);
    }
}
