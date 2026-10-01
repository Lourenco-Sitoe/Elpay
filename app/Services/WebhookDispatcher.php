<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;

class WebhookDispatcher
{
    /**
     * Dispara $event para os webhooks ativos do produtor (ou todos se $producerId null).
     * O envio é enfileirado (jobs) para não atrasar o checkout. Requer `queue:work`.
     */
    public static function dispatch(string $event, array $payload, ?int $producerId = null): void
    {
        try {
            $query = Webhook::where('active', true);
            if ($producerId) {
                $query->where('producer_id', $producerId);
            }
            $hooks = $query->get()->filter(fn ($h) => $h->wants($event));
            foreach ($hooks as $hook) {
                \App\Jobs\SendWebhookDelivery::dispatch($hook->id, $event, $payload);
            }
        } catch (\Throwable) {
        }
    }

    public static function send(Webhook $hook, string $event, array $payload): WebhookDelivery
    {
        $body = ['event' => $event, 'data' => $payload, 'sent_at' => now()->toIso8601String()];
        $signature = $hook->secret ? hash_hmac('sha256', json_encode($body), $hook->secret) : null;
        $start = microtime(true);
        $ok = false;
        $code = null;
        $response = null;

        try {
            $res = Http::timeout(5)->withHeaders(array_filter(['X-Elpay-Signature' => $signature]))
                ->post($hook->url, $body);
            $code = $res->status();
            $ok = $res->successful();
            $response = mb_substr($res->body(), 0, 2000);
        } catch (\Throwable $e) {
            $response = mb_substr($e->getMessage(), 0, 2000);
        }

        return WebhookDelivery::create([
            'webhook_id' => $hook->id,
            'event' => $event,
            'status_code' => $code,
            'ok' => $ok,
            'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            'response' => $response,
        ]);
    }
}
