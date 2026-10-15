<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Upsells / Downsells</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="flex justify-between items-center mb-3"><h2 class="font-bold text-lg">Upsells / Downsells</h2><a href="{{ Route::has('upsells.create')?route('upsells.create'):'#' }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">+ Novo upsell</a></div>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Principal</th><th>Upsell</th><th>Preço especial</th><th>Downsell</th><th>Ativo</th><th></th></tr></thead>
<tbody>@forelse(($upsells??[]) as $u)<tr class="border-b"><td class="p-3">{{ $u->mainProduct->title??'-' }}</td><td>{{ $u->upsellProduct->title??'-' }}</td><td>{{ number_format($u->special_price??0,2) }} MT</td><td>{{ $u->downsellProduct->title??'-' }}</td><td>{{ !empty($u->active)?'Sim':'Não' }}</td>
<td class="p-3 text-right whitespace-nowrap"><a href="{{ Route::has('upsells.edit')?route('upsells.edit',$u):'#' }}" class="text-blue-600 text-xs mr-2">Editar</a><form method="POST" action="{{ Route::has('upsells.destroy')?route('upsells.destroy',$u):'#' }}" class="inline" onsubmit="return confirm('Apagar?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form></td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-gray-400">Sem upsells.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($upsells)&&method_exists($upsells,'links'))<div class="mt-3">{{ $upsells->links() }}</div>@endif
</div></x-app-layout>
