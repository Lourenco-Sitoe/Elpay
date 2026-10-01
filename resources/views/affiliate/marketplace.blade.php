<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Marketplace de afiliado</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="GET" class="mb-4 flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Pesquisar…" class="flex-1 border rounded-lg px-3 py-2"><button class="px-4 py-2 bg-gray-900 text-white rounded-lg">Buscar</button></form>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
@forelse(($products??[]) as $p)
<div class="bg-white rounded-xl shadow p-4 flex flex-col">
<h3 class="font-semibold">{{ $p->title }}</h3>
<p class="text-xs text-gray-500 mt-1">Comissão: {{ $p->commission_rate??0 }}% (~{{ number_format(($p->price??0)*($p->commission_rate??0)/100,2) }} MT)</p>
<div class="font-bold text-blue-600 mt-1">{{ number_format($p->price??0,2) }} MT</div>
<form method="POST" action="{{ Route::has('affiliate.join')?route('affiliate.join',$p->id):'#' }}" class="mt-3">@csrf<button class="w-full text-center text-sm px-3 py-2 bg-blue-600 text-white rounded-lg">Promover</button></form>
</div>
@empty<div class="col-span-full text-center text-gray-400 py-8">Sem produtos.</div>@endforelse
</div>
@if(isset($products)&&method_exists($products,'links'))<div class="mt-4">{{ $products->links() }}</div>@endif
</div></x-app-layout>
