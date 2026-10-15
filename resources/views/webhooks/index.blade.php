<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Webhooks</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="flex justify-between items-center mb-3"><h2 class="font-bold text-lg">Webhooks</h2><a href="{{ route('tools.index') }}" class="text-sm text-blue-600 underline">Ferramentas</a></div>
<form method="POST" action="{{ route('webhooks.store') }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-2 gap-2">@csrf
<input name="name" placeholder="Nome (ex: Meu CRM)" required class="border rounded-lg px-3 py-2">
<input name="url" placeholder="https://…" required class="border rounded-lg px-3 py-2">
<input name="secret" placeholder="Segredo HMAC (opcional)" class="border rounded-lg px-3 py-2">
<div class="text-sm text-gray-600 flex items-center gap-3">Eventos: @foreach(\App\Models\Webhook::EVENTS as $k=>$l)<label class="inline-flex items-center gap-1"><input type="checkbox" name="events[]" value="{{ $k }}" checked class="rounded"> {{ $l }}</label>@endforeach</div>
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg sm:col-span-2">Criar webhook</button></form>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Nome</th><th>URL</th><th>Falhas</th><th>Ativo</th><th></th></tr></thead>
<tbody>@forelse(($webhooks??[]) as $w)<tr class="border-b"><td class="p-3 font-medium">{{ $w->name }}</td><td class="text-xs truncate max-w-[220px]">{{ $w->url }}</td><td>{{ $w->failed_deliveries??0 }}</td><td>{{ $w->active?'Sim':'Não' }}</td>
<td class="p-3 text-right whitespace-nowrap">
<a href="{{ route('webhooks.deliveries',$w) }}" class="text-gray-600 text-xs mr-2">Entregas</a>
<form method="POST" action="{{ route('webhooks.test',$w) }}" class="inline">@csrf<button class="text-purple-600 text-xs mr-2">Testar</button></form>
<form method="POST" action="{{ route('webhooks.toggle',$w) }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">On/Off</button></form>
<form method="POST" action="{{ route('webhooks.destroy',$w) }}" class="inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form>
</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-400">Sem webhooks.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($webhooks)&&method_exists($webhooks,'links'))<div class="mt-3">{{ $webhooks->links() }}</div>@endif
</div></x-app-layout>
