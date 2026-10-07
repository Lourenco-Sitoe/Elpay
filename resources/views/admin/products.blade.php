@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Produtos</h2>
<form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Pesquisar…" class="border rounded-lg px-3 py-1.5 text-sm">
<select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(($statusLabels ?? ['pending'=>'pending','approved'=>'approved','rejected'=>'rejected']) as $v=>$l)<option value="{{ $v }}" @selected(request('status')==$v)>{{ $l }}</option>@endforeach</select>
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Título</th><th>Dono</th><th>Preço</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($products??[]) as $p)<tr class="border-b"><td>{{ $p->title }}</td><td>{{ $p->producer->name??'-' }}</td><td>{{ number_format($p->price??0,2) }}</td><td>{!! statusBadge($p->status??'pending') !!}</td>
<td class="text-right whitespace-nowrap">
@if(Route::has('admin.products.show'))<a href="{{ route('admin.products.show', $p) }}" class="text-blue-600 text-xs mr-2">Ver</a>@endif
@if(Route::has('admin.products.edit'))<a href="{{ route('admin.products.edit', $p) }}" class="text-blue-600 text-xs mr-2">Editar</a>@endif
<form method="POST" action="{{ Route::has('admin.products.approve')?route('admin.products.approve',$p):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Aprovar</button></form>
<form method="POST" action="{{ Route::has('admin.products.reject')?route('admin.products.reject',$p):'#' }}" class="inline">@csrf<input type="hidden" name="reason" id="r-{{ $p->id }}"><button class="text-amber-600 text-xs mr-2" onclick="document.getElementById('r-{{ $p->id }}').value=prompt('Motivo da rejeição:')||''">Rejeitar</button></form>
<form method="POST" action="{{ Route::has('admin.products.suspend')?route('admin.products.suspend',$p):'#' }}" class="inline" onsubmit="return confirm('Suspender produto? Sai da loja até reativares.')">@csrf<button class="text-amber-600 text-xs mr-2">Suspender</button></form>
<form method="POST" action="{{ Route::has('admin.products.destroy')?route('admin.products.destroy',$p):'#' }}" class="inline" onsubmit="return confirm('Remover produto?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($products)&&method_exists($products,'links'))<div class="mt-3">{{ $products->withQueryString()->links() }}</div>@endif
</div>
@endsection
