<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Novo upsell</h2></x-slot>
<div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('upsells.store')?route('upsells.store'):'#' }}" class="bg-white shadow rounded-xl p-6 grid gap-3">@csrf
<div><label class="text-sm font-medium">Produto principal</label><select name="main_product_id" required class="mt-1 w-full border rounded-lg px-3 py-2">@foreach(($products??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Produto do upsell</label><select name="upsell_product_id" required class="mt-1 w-full border rounded-lg px-3 py-2">@foreach(($products??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }} — {{ number_format($p->price??0,2) }} MT</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Preço especial (MT)</label><input type="number" step="0.01" min="1" name="special_price" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Downsell (opcional)</label><select name="downsell_product_id" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="">— nenhum —</option>@foreach(($products??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select></div>
<div><label class="text-sm font-medium">Preço downsell (MT, opcional)</label><input type="number" step="0.01" min="1" name="downsell_price" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<button class="py-2 bg-blue-600 text-white rounded-lg font-semibold">Criar upsell</button>
</form></div></x-app-layout>
