<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Carteira</h2><a href="{{ Route::has('wallet.request')?route('wallet.request'):'#' }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">Solicitar saque</a></div></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="grid sm:grid-cols-3 gap-4 mb-6">
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Saldo disponível</div><div class="font-bold text-xl text-blue-600">{{ number_format($balance??$available??0,2) }} MT</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Pendente</div><div class="font-bold text-xl">{{ number_format($pending??0,2) }} MT</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Total levantado</div><div class="font-bold text-xl">{{ number_format($withdrawn??0,2) }} MT</div></div>
</div>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="p-4 font-semibold border-b">Extrato de saques</div><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Valor</th><th>Método</th><th>Estado</th><th>Data</th></tr></thead>
<tbody>@forelse(($withdrawals??[]) as $w)<tr class="border-b"><td class="p-3">{{ number_format($w->amount??0,2) }} MT</td><td>{{ $w->method??$w->phone??'-' }}</td><td>{!! statusBadge($w->status??'-') !!}</td><td class="p-3">{{ $w->created_at }}</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-gray-400">Sem saques.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($withdrawals)&&method_exists($withdrawals,'links'))<div class="mt-4">{{ $withdrawals->links() }}</div>@endif
</div></x-app-layout>
