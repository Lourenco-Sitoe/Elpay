@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
<h2 class="font-bold mb-3">Comunicação / Broadcast</h2>
<form method="POST" action="{{ Route::has('admin.communication.send')?route('admin.communication.send'):(Route::has('admin.communication')?route('admin.communication'):'#') }}">@csrf
<div class="mb-3"><label class="text-sm font-medium">Destinatários</label><select name="target" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="all">Todos</option><option value="admin">Admins</option><option value="producer">Produtores</option><option value="affiliate">Afiliados</option><option value="client">Clientes</option></select></div>
<div class="mb-3"><label class="text-sm font-medium">Título</label><input name="title" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Mensagem</label><textarea name="message" rows="5" required class="mt-1 w-full border rounded-lg px-3 py-2"></textarea></div>
<button class="px-6 py-2 bg-blue-600 text-white rounded-lg">Enviar</button></form>
</div>
@endsection
