<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Minhas comissões</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="grid grid-cols-3 gap-4 mb-4">
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Pendentes</div><div class="font-bold">{{ number_format($pending??0,2) }} MT</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Aprovadas</div><div class="font-bold">{{ number_format($approved??0,2) }} MT</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Total</div><div class="font-bold">{{ number_format($total??0,2) }} MT</div></div>
</div>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Venda</th><th>Valor</th><th>Estado</th><th>Data</th></tr></thead>
<tbody>@forelse(($commissions??[]) as $c)<tr class="border-b"><td class="p-3">#{{ $c->sale_id??$c->id }}</td><td>{{ number_format($c->amount??0,2) }} MT</td><td>{!! statusBadge($c->status??'-') !!}</td><td class="p-3">{{ $c->created_at }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-gray-400">Sem comissões.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($commissions)&&method_exists($commissions,'links'))<div class="mt-4">{{ $commissions->links() }}</div>@endif
</div></x-app-layout>
