<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Meus clientes</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<form method="GET" class="flex flex-wrap gap-2 text-sm">
<input name="q" value="{{ request('q') }}" placeholder="Nome/email…" class="border rounded-lg px-3 py-2">
<select name="product_id" class="border rounded-lg px-2 py-2"><option value="">Todos produtos</option>
@foreach($products as $p)<option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->title }}</option>@endforeach</select>
<button class="px-3 py-2 bg-gray-900 text-white rounded-lg">Filtrar</button>
</form>
</div>
<div class="bg-white rounded-xl shadow p-4 mb-4">
<h3 class="font-bold text-sm mb-2">Conceder acesso manual</h3>
<form method="POST" action="{{ route('producer.access.grant') }}" class="flex flex-wrap gap-2 text-sm">
@csrf
<select name="product_id" required class="border rounded-lg px-2 py-2">
@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select>
<input type="email" name="email" required placeholder="email do cliente" class="border rounded-lg px-3 py-2">
<button class="px-3 py-2 bg-blue-600 text-white rounded-lg">Conceder</button>
</form>
</div>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">Cliente</th><th>Produto</th><th>Desde</th><th></th></tr></thead>
<tbody>
@forelse($accesses as $a)
<tr class="border-b">
<td class="px-3"><div class="font-medium">{{ $a->user->name ?? '-' }}</div><div class="text-xs text-gray-500">{{ $a->user->email ?? '' }}</div></td>
<td>{{ $a->product->title ?? '-' }}</td>
<td class="whitespace-nowrap">{{ $a->created_at?->format('d/m/Y') }}</td>
<td class="text-right whitespace-nowrap px-3">
<a href="{{ route('producer.customers.history', $a->user_id) }}" class="text-blue-600 text-xs mr-2">Histórico</a>
<form method="POST" action="{{ route('producer.access.revoke', $a) }}" class="inline" onsubmit="return confirm('Remover acesso?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</td></tr>
@empty<tr><td colspan="4" class="py-6 text-center text-gray-400">Sem clientes.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $accesses->withQueryString()->links() }}</div>
</div></x-app-layout>
