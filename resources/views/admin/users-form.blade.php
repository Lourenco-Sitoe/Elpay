@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
<h2 class="font-bold mb-4">{{ isset($user) ? 'Editar utilizador' : 'Novo utilizador' }}</h2>
@include('layouts.partials.flash')
<form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
@csrf @if(isset($user)) @method('PUT') @endif
<div class="grid gap-3">
<div><label class="text-sm font-medium">Nome</label><input name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="grid sm:grid-cols-2 gap-3">
<div><label class="text-sm font-medium">Email</label><input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Telefone</label><input name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
<div><label class="text-sm font-medium">Password {{ isset($user) ? '(vazio = manter)' : '' }}</label><input type="password" name="password" {{ isset($user) ? '' : 'required' }} minlength="8" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="grid sm:grid-cols-2 gap-3">
<div><label class="text-sm font-medium">Tipo</label>
<select name="type" class="mt-1 w-full border rounded-lg px-3 py-2">
@foreach(['admin'=>'Admin','producer'=>'Produtor','affiliate'=>'Afiliado','client'=>'Cliente'] as $v=>$l)
<option value="{{ $v }}" @selected(old('type', $user->type ?? 'client')==$v)>{{ $l }}</option>
@endforeach</select>
<p class="text-xs text-gray-500 mt-1">Admin vê e gere tudo. Produtor só gere o próprio negócio.</p></div>
<div><label class="text-sm font-medium">Estado</label>
<select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
@foreach(['active'=>'Ativo','inactive'=>'Inativo','banned'=>'Bloqueado'] as $v=>$l)
<option value="{{ $v }}" @selected(old('status', $user->status ?? 'active')==$v)>{{ $l }}</option>
@endforeach</select></div>
</div>
</div>
<div class="mt-4 flex gap-2">
<button class="px-6 py-2 bg-blue-600 text-white rounded-lg">Guardar</button>
<a href="{{ route('admin.users') }}" class="px-6 py-2 border rounded-lg">Voltar</a>
</div>
</form>
</div>
@endsection
