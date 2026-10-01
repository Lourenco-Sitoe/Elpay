<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Ganhos de parceria</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4"><span class="text-sm text-gray-500">Total acumulado:</span> <b class="text-blue-600">{{ number_format($total??0,2) }} MT</b></div>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Produto</th><th>Venda</th><th>%</th><th>Valor</th><th>Estado</th></tr></thead>
<tbody>@forelse(($earnings??[]) as $e)<tr class="border-b"><td class="p-3">{{ $e->product->title??'-' }}</td><td>#{{ $e->sale_id }}</td><td>{{ $e->share_percent }}%</td><td>{{ number_format($e->amount??0,2) }} MT</td><td>{!! statusBadge($e->status) !!}</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-400">Sem ganhos de parceria.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($earnings)&&method_exists($earnings,'links'))<div class="mt-3">{{ $earnings->links() }}</div>@endif
</div></x-app-layout>
