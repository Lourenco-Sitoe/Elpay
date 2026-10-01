<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Entregas — {{ $webhook->name }}</h2><a href="{{ route('webhooks.index') }}" class="text-sm text-blue-600 underline">Voltar</a></div></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Evento</th><th>HTTP</th><th>OK</th><th>ms</th><th>Data</th><th></th></tr></thead>
<tbody>@forelse(($deliveries??[]) as $d)<tr class="border-b"><td class="p-3 font-mono text-xs">{{ $d->event }}</td><td>{{ $d->status_code??'-' }}</td><td>{!! statusBadge($d->ok?'paid':'rejected') !!}</td><td>{{ $d->duration_ms }}</td><td class="text-xs">{{ $d->created_at }}</td>
<td class="p-3 text-right"><form method="POST" action="{{ route('webhooks.resend',$d) }}">@csrf<button class="text-blue-600 text-xs">Reenviar</button></form></td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-gray-400">Sem entregas.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($deliveries)&&method_exists($deliveries,'links'))<div class="mt-3">{{ $deliveries->links() }}</div>@endif
</div></x-app-layout>
