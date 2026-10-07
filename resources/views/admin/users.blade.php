@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex flex-wrap gap-2 justify-between items-center mb-3">
<h2 class="font-bold">Utilizadores</h2>
<div class="flex gap-2 items-center">
<form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Nome/email…" class="border rounded-lg px-3 py-1.5 text-sm">
<select name="type" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['admin','producer','affiliate','client'] as $r)<option value="{{ $r }}" @selected(request('type')==$r)>{{ $r }}</option>@endforeach</select>
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form>
@if(Route::has('admin.users.create'))<a href="{{ route('admin.users.create') }}" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg">+ Novo</a>@endif
</div>
</div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Nome</th><th>Email</th><th>Role</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($users??[]) as $u)<tr class="border-b"><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->type??'-' }}</td><td>{!! statusBadge($u->status??'active') !!}</td>
<td class="text-right whitespace-nowrap">
@if(Route::has('admin.users.edit'))<a href="{{ route('admin.users.edit', $u) }}" class="text-blue-600 text-xs mr-2">Editar</a>@endif
<form method="POST" action="{{ Route::has('admin.users.status')?route('admin.users.status',$u):'#' }}" class="inline">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="text-blue-600 text-xs mr-2">Ativar</button></form>
<form method="POST" action="{{ Route::has('admin.users.status')?route('admin.users.status',$u):'#' }}" class="inline" onsubmit="return confirm('Bloquear utilizador?')">@csrf @method('PATCH')<input type="hidden" name="status" value="banned"><button class="text-red-600 text-xs mr-2">Banir</button></form>
<form method="POST" action="{{ Route::has('admin.users.destroy')?route('admin.users.destroy',$u):'#' }}" class="inline" onsubmit="return confirm('Eliminar utilizador?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Eliminar</button></form>
</td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($users)&&method_exists($users,'links'))<div class="mt-3">{{ $users->withQueryString()->links() }}</div>@endif
</div>
@endsection
