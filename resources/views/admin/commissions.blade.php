@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Comissões</h2>
<form method="GET" class="flex gap-2"><select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','approved','paid'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select><button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Afiliado</th><th>Venda</th><th>Valor</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($commissions??[]) as $c)<tr class="border-b"><td>{{ $c->user->name??$c->affiliate->name??'-' }}</td><td>#{{ $c->sale_id??'-' }}</td><td>{{ number_format($c->amount??0,2) }}</td><td>{!! statusBadge($c->status??'-') !!}</td>
<td class="text-right"><form method="POST" action="{{ Route::has('admin.commissions.paid')?route('admin.commissions.paid',$c):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs">Marcar paga</button></form></td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($commissions)&&method_exists($commissions,'links'))<div class="mt-3">{{ $commissions->withQueryString()->links() }}</div>@endif
</div>
<div class="bg-white rounded-xl shadow p-4 mt-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Ganhos de parceiros (coprodução)</h2></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Parceiro</th><th>Produto</th><th>Venda</th><th>Valor</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($partnerEarnings??[]) as $e)<tr class="border-b"><td>{{ $e->partner->name??'-' }}</td><td>{{ $e->product->title??'-' }}</td><td>#{{ $e->sale_id }}</td><td>{{ number_format($e->amount??0,2) }}</td><td>{{ $e->status??'-' }}</td>
<td class="text-right"><form method="POST" action="{{ Route::has('admin.partner-earnings.paid')?route('admin.partner-earnings.paid',$e):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs">Marcar pago</button></form></td></tr>@empty<tr><td colspan="6" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($partnerEarnings)&&method_exists($partnerEarnings,'links'))<div class="mt-3">{{ $partnerEarnings->withQueryString()->links() }}</div>@endif
</div>
@endsection
