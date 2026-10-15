<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Order Bumps</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="flex justify-between items-center mb-3"><h2 class="font-bold text-lg">Order Bumps</h2><a href="{{ Route::has('bumps.create')?route('bumps.create'):'#' }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">+ Novo bump</a></div>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Título</th><th>Produto principal</th><th>Bump</th><th>Preço</th><th>Ativo</th><th></th></tr></thead>
<tbody>@forelse(($bumps??[]) as $b)<tr class="border-b"><td class="p-3">{{ $b->title }}</td><td>{{ $b->mainProduct->title??'-' }}</td><td>{{ $b->bumpProduct->title??'-' }}</td><td>{{ number_format($b->price??0,2) }} MT</td><td>{{ !empty($b->active)?'Sim':'Não' }}</td>
<td class="p-3 text-right whitespace-nowrap"><a href="{{ Route::has('bumps.edit')?route('bumps.edit',$b):'#' }}" class="text-blue-600 text-xs mr-2">Editar</a><form method="POST" action="{{ Route::has('bumps.destroy')?route('bumps.destroy',$b):'#' }}" class="inline" onsubmit="return confirm('Apagar?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form></td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-gray-400">Sem bumps.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($bumps)&&method_exists($bumps,'links'))<div class="mt-3">{{ $bumps->links() }}</div>@endif
</div></x-app-layout>
