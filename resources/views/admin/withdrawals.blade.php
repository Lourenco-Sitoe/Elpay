@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Saques</h2>
<form method="GET" class="flex gap-2"><select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','approved','rejected','paid'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select><button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Utilizador</th><th>Valor</th><th>Destino</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($withdrawals??[]) as $w)<tr class="border-b"><td>{{ $w->user->name??'-' }}</td><td>{{ number_format($w->amount??0,2) }}</td><td>{{ $w->method??'' }} {{ $w->phone??'' }}</td><td>{!! statusBadge($w->status??'-') !!}</td>
<td class="text-right whitespace-nowrap">
<form method="POST" action="{{ Route::has('admin.withdrawals.approve')?route('admin.withdrawals.approve',$w):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Aprovar</button></form>
<form method="POST" action="{{ Route::has('admin.withdrawals.reject')?route('admin.withdrawals.reject',$w):'#' }}" class="inline">@csrf<input type="hidden" name="reason" id="w-{{ $w->id }}"><button class="text-red-600 text-xs" onclick="document.getElementById('w-{{ $w->id }}').value=prompt('Motivo:')||''">Rejeitar</button></form>
</td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($withdrawals)&&method_exists($withdrawals,'links'))<div class="mt-3">{{ $withdrawals->withQueryString()->links() }}</div>@endif
</div>
@endsection
