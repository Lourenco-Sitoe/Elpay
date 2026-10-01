<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">As minhas compras</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
@include('layouts.partials.flash')
@php $s=$stats??[]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Compras</div><div class="font-bold text-xl">{{ $s['purchases']??count($purchases??$recentSales??[]) }}</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Gasto total (MT)</div><div class="font-bold text-xl">{{ $s['spent']??0 }}</div></div>
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">Produtos</div><div class="font-bold text-xl">{{ $s['items']??0 }}</div></div>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse(($purchases??$recentSales??[]) as $p)
<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold">{{ $p->product->title??$p->title??'Produto' }}</h3><p class="text-sm text-gray-500">{{ $p->created_at??'' }}</p>
<div class="mt-2 flex flex-wrap gap-2">
@if(!empty($p->product_id))<a class="text-sm px-3 py-1.5 bg-blue-600 text-white rounded" href="{{ Route::has('learn.course')?route('learn.course',$p->product_id):'#' }}">Estudar</a>@endif
@if(!empty($p->product->file_path))<a class="text-sm px-3 py-1.5 bg-gray-900 text-white rounded" href="{{ asset('storage/'.$p->product->file_path) }}">Descarregar</a>@endif
@if(!empty($p->product->external_url))<a class="text-sm px-3 py-1.5 border rounded" href="{{ $p->product->external_url }}">Aceder</a>@endif
</div>
</div>
@empty<div class="col-span-full bg-white rounded-xl shadow p-8 text-center text-gray-500">Ainda não compraste nada. <a class="text-blue-600 underline" href="{{ url('/') }}#produtos">Explorar produtos</a></div>@endforelse
</div>
<div class="mt-4 text-center"><a href="{{ Route::has('certificates.mine')?route('certificates.mine'):'#' }}" class="text-sm text-blue-600 underline"><i class="bi bi-award"></i> Meus certificados</a></div>
</div>
</x-app-layout>
