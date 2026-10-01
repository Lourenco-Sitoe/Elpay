<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Editar order bump</h2></x-slot>
<div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('bumps.update')?route('bumps.update',$bump->id??0):'#' }}" class="bg-white shadow rounded-xl p-6 grid gap-3">@csrf @method('PUT')
<div><label class="text-sm font-medium">Título da oferta</label><input name="title" value="{{ old('title',$bump->title??'') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Descrição (opcional)</label><input name="description" value="{{ old('description',$bump->description??'') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Preço do bump (MT)</label><input type="number" step="0.01" min="1" name="price" value="{{ old('price',$bump->price??'') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm"><input type="checkbox" name="active" value="1" @checked(!empty($bump->active))> Ativo</label></div>
<button class="py-2 bg-blue-600 text-white rounded-lg font-semibold">Guardar</button>
</form></div></x-app-layout>
