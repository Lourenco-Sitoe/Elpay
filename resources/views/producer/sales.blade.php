<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Minhas vendas</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<form method="GET" class="grid md:grid-cols-6 gap-2 text-sm">
<input name="q" value="{{ request('q') }}" placeholder="Cliente/email…" class="border rounded-lg px-3 py-2">
<select name="product_id" class="border rounded-lg px-2 py-2"><option value="">Todos produtos</option>
@foreach($products as $p)<option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->title }}</option>@endforeach</select>
<select name="status" class="border rounded-lg px-2 py-2"><option value="">Todos estados</option>
@foreach(['pending','paid','cancelled','refunded'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select>
<input type="date" name="from" value="{{ request('from') }}" class="border rounded-lg px-2 py-2">
<input type="date" name="to" value="{{ request('to') }}" class="border rounded-lg px-2 py-2">
<button class="px-3 py-2 bg-gray-900 text-white rounded-lg">Filtrar</button>
</form>
<p class="text-xs text-gray-500 mt-2">Total: {{ $summary['count'] }} vendas · Pago: MZN {{ number_format($summary['paid'], 2) }} · Só vês os teus produtos.</p>
</div>
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">#</th><th>Data</th><th>Produto</th><th>Cliente</th><th>Afiliado</th><th>Estado</th><th class="text-right px-3">Valor</th></tr></thead>
<tbody>
@forelse($sales as $s)
<tr class="border-b">
<td class="px-3">{{ $s->id }}</td>
<td class="whitespace-nowrap">{{ $s->created_at?->format('d/m/Y H:i') }}</td>
<td>{{ $s->product->title ?? '-' }}</td>
<td><div class="font-medium">{{ $s->buyer_name }}</div><div class="text-xs text-gray-500">{{ $s->buyer_email }}</div></td>
<td class="text-xs">{{ $s->affiliate_code ?? '-' }}</td>
<td>{!! statusBadge($s->status) !!}</td>
<td class="text-right px-3 font-bold">MZN {{ number_format($s->final_price, 2) }}</td>
</tr>
@empty<tr><td colspan="7" class="py-6 text-center text-gray-400">Sem vendas.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $sales->withQueryString()->links() }}</div>
</div></x-app-layout>
