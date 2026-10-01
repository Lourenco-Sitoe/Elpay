<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Meus links de afiliado</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Produto</th><th>Link</th><th>Vendas</th><th></th></tr></thead>
<tbody>@forelse(($links??$products??[]) as $l)
@php $url = url('/checkout/'.($l->product->slug ?? $l->product->id ?? $l->id).'?ref='.($l->affiliate_code ?? '')); @endphp
<tr class="border-b"><td class="p-3">{{ $l->product->title??$l->title??'-' }}</td>
<td class="p-3"><code class="text-xs bg-gray-100 px-2 py-1 rounded">{{ $url }}</code></td>
<td class="p-3">{{ $l->total_sales??0 }}</td>
<td class="p-3"><button onclick="navigator.clipboard.writeText('{{ $url }}');this.textContent='Copiado!'" class="px-3 py-1.5 bg-gray-900 text-white text-xs rounded-lg">Copiar</button></td></tr>
@empty<tr><td colspan="4" class="p-6 text-center text-gray-400">Sem links. Vai ao <a class="underline text-blue-600" href="{{ Route::has('affiliate.marketplace')?route('affiliate.marketplace'):'#' }}">marketplace</a>.</td></tr>@endforelse</tbody>
</table></div></div></div></x-app-layout>
