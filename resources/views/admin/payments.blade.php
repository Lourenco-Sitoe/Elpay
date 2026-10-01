@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Pagamentos</h2>
<form method="GET" class="flex gap-2"><select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','completed','failed'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select>
<select name="method" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Métodos</option>@foreach(['mpesa','emola'] as $s)<option @selected(request('method')==$s)>{{ $s }}</option>@endforeach</select>
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">ID</th><th>Venda</th><th>Método</th><th>Valor</th><th>Estado</th><th>Data</th><th></th></tr></thead>
<tbody>@forelse(($payments??[]) as $p)<tr class="border-b"><td>#{{ $p->id }}</td><td>{{ $p->sale_id??'-' }}</td><td>{{ $p->method??'-' }}</td><td>{{ number_format($p->amount??0,2) }}</td><td>{!! statusBadge($p->status??'-') !!}</td><td>{{ $p->created_at }}</td>
<td class="text-right"><form method="POST" action="{{ Route::has('admin.payments.approve')?route('admin.payments.approve',$p):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs">Confirmar</button></form></td></tr>@empty<tr><td colspan="7" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($payments)&&method_exists($payments,'links'))<div class="mt-3">{{ $payments->withQueryString()->links() }}</div>@endif
</div>
@endsection
