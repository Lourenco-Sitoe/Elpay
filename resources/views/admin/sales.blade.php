@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Vendas</h2>
<form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="ID/email…" class="border rounded-lg px-3 py-1.5 text-sm">
<select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','paid','failed','refunded'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select>
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">ID</th><th>Produto</th><th>Comprador</th><th>Valor</th><th>Estado</th><th>Data</th><th></th></tr></thead>
<tbody>@forelse(($sales??[]) as $s)<tr class="border-b"><td>#{{ $s->id }}</td><td>{{ $s->product->title??'-' }}</td><td>{{ $s->buyer->email??$s->buyer_email??'-' }}</td><td>{{ number_format($s->final_price??0,2) }}</td><td>{!! statusBadge($s->status??'-') !!}</td><td>{{ $s->created_at }}</td>
<td class="text-right whitespace-nowrap">
<form method="POST" action="{{ Route::has('admin.sales.cancel')?route('admin.sales.cancel',$s):'#' }}" class="inline" onsubmit="return confirm('Cancelar venda?')">@csrf<button class="text-red-600 text-xs mr-2">Cancelar</button></form>
<form method="POST" action="{{ Route::has('admin.sales.refund')?route('admin.sales.refund',$s):'#' }}" class="inline" onsubmit="return confirm('Reembolsar venda?')">@csrf<button class="text-orange-600 text-xs">Reembolsar</button></form>
</td></tr>@empty<tr><td colspan="7" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($sales)&&method_exists($sales,'links'))<div class="mt-3">{{ $sales->withQueryString()->links() }}</div>@endif
</div>
@endsection
