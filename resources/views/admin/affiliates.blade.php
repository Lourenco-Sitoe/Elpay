@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Afiliados</h2>
<form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Pesquisar…" class="border rounded-lg px-3 py-1.5 text-sm"><select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','approved','rejected'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select><button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Nome</th><th>Email</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($affiliates??$users??[]) as $a)<tr class="border-b"><td>{{ $a->user->name??$a->name }}</td><td>{{ $a->user->email??$a->email }}</td><td>{{ $a->product->title??'-' }}</td><td>{{ $a->affiliate_code??'-' }}</td><td>{!! statusBadge($a->status??'-') !!}</td>
<td class="text-right whitespace-nowrap">
<form method="POST" action="{{ Route::has('admin.affiliates.approve')?route('admin.affiliates.approve',$a):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Aprovar</button></form>
<form method="POST" action="{{ Route::has('admin.affiliates.block')?route('admin.affiliates.block',$a):'#' }}" class="inline" onsubmit="return confirm('Bloquear afiliado?')">@csrf<button class="text-red-600 text-xs">Bloquear</button></form>
</td></tr>@empty<tr><td colspan="6" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($affiliates)&&method_exists($affiliates,'links'))<div class="mt-3">{{ $affiliates->withQueryString()->links() }}</div>@endif
</div>
@endsection
