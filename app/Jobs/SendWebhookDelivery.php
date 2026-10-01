<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWebhookDelivery implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function __construct(
        public int $webhookId,
        public string $event,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $hook = Webhook::find($this->webhookId);
        if (! $hook || ! $hook->active) {
            return;
        }

        WebhookDispatcher::send($hook, $this->event, $this->payload);
    }
}
