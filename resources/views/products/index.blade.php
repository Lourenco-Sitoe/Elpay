<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Meus produtos</h2><a href="{{ Route::has('products.create')?route('products.create'):'#' }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">+ Novo</a></div></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Produto</th><th>Preço</th><th>Tipo</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($products??[]) as $p)<tr class="border-b">
<td class="p-3 font-medium">{{ $p->title }}</td><td>{{ number_format($p->price,2) }} MT</td><td>{{ $p->type }}</td>
<td><span class="px-2 py-1 text-xs rounded {{ ($p->status??'')=='approved'?'bg-blue-100 text-blue-700':'bg-amber-100 text-amber-700' }}">{{ $p->status??'pendente' }}</span></td>
<td class="p-3 text-right whitespace-nowrap">
<a href="{{ Route::has('products.show')?route('products.show',$p):'#' }}" class="text-blue-600 mr-2">Ver</a>
@if(($p->type??'')==='course')<a href="{{ Route::has('content.index')?route('content.index',$p):'#' }}" class="text-purple-600 mr-2">Conteúdo</a>@endif
<a href="{{ Route::has('products.edit')?route('products.edit',$p):'#' }}" class="text-blue-600 mr-2">Editar</a>
<form method="POST" action="{{ Route::has('products.destroy')?route('products.destroy',$p):'#' }}" class="inline" onsubmit="return confirm('Eliminar?')">@csrf @method('DELETE')<button class="text-red-600">Apagar</button></form>
</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-400">Sem produtos.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($products)&&method_exists($products,'links'))<div class="mt-4">{{ $products->links() }}</div>@endif
</div></x-app-layout>
