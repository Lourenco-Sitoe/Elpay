<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $webhooks = Webhook::withCount(['deliveries as failed_deliveries' => fn ($q) => $q->where('ok', false)])
            ->where('producer_id', auth()->id())->latest()->paginate(15);

        return view('webhooks.index', ['webhooks' => $webhooks, 'events' => Webhook::EVENTS]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);

        Webhook::create([
            'producer_id' => auth()->id(),
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => $data['events'] ?? array_keys(Webhook::EVENTS),
            'secret' => $data['secret'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        return back()->with('success', 'Webhook criado.');
    }

    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        $this->own($webhook);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'nullable|array',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
            'active' => 'nullable|boolean',
        ]);
        $webhook->update([
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => $data['events'] ?? $webhook->events,
            'secret' => $data['secret'] ?? $webhook->secret,
            'active' => $data['active'] ?? $webhook->active,
        ]);

        return back()->with('success', 'Webhook atualizado.');
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->own($webhook);
        $webhook->delete();

        return back()->with('success', 'Webhook removido.');
    }

    public function toggle(Webhook $webhook): RedirectResponse
    {
        $this->own($webhook);
        $webhook->active = ! $webhook->active;
        $webhook->save();

        return back()->with('success', 'Webhook atualizado.');
    }

    public function test(Webhook $webhook): RedirectResponse
    {
        $this->own($webhook);
        $delivery = WebhookDispatcher::send($webhook, 'sale.paid', ['ping' => true, 'at' => now()->toIso8601String()]);

        return back()->with('success', 'Teste enviado. HTTP ' . ($delivery->status_code ?? 'erro') . '.');
    }

    public function deliveries(Webhook $webhook): View
    {
        $this->own($webhook);
        $deliveries = $webhook->deliveries()->paginate(20);

        return view('webhooks.deliveries', compact('webhook', 'deliveries'));
    }

    public function resend(\App\Models\WebhookDelivery $delivery): RedirectResponse
    {
        $this->own($delivery->webhook);
        WebhookDispatcher::send($delivery->webhook, $delivery->event, ['resend_of' => $delivery->id]);

        return back()->with('success', 'Reenvio efetuado.');
    }

    protected function own(Webhook $webhook): void
    {
        abort_unless((int) $webhook->producer_id === (int) auth()->id() || (auth()->user()->type ?? '') === 'admin', 403);
    }
}
