<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Novo order bump</h2></x-slot>
<div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('bumps.store')?route('bumps.store'):'#' }}" class="bg-white shadow rounded-xl p-6 grid gap-3">@csrf
<div><label class="text-sm font-medium">Produto principal (checkout)</label><select name="main_product_id" required class="mt-1 w-full border rounded-lg px-3 py-2">@foreach(($products??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Produto do bump</label><select name="bump_product_id" required class="mt-1 w-full border rounded-lg px-3 py-2">@foreach(($products??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }} — {{ number_format($p->price??0,2) }} MT</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Título da oferta</label><input name="title" required placeholder="Ex: Adiciona o ebook por apenas…" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Descrição (opcional)</label><input name="description" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Preço do bump (MT)</label><input type="number" step="0.01" min="1" name="price" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<button class="py-2 bg-blue-600 text-white rounded-lg font-semibold">Criar bump</button>
</form></div></x-app-layout>
