<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Cupons</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('coupons.store')?route('coupons.store'):'#' }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-5 gap-2">@csrf
<input name="code" placeholder="CÓDIGO" required class="border rounded-lg px-3 py-2">
<input type="number" step="0.01" min="1" max="90" name="percent" placeholder="% desconto" required class="border rounded-lg px-3 py-2">
<select name="product_id" class="border rounded-lg px-3 py-2"><option value="">Todos os produtos</option>@foreach(($myProducts??[]) as $p)<option value="{{ $p->id }}">{{ $p->title }}</option>@endforeach</select>
<input type="number" name="max_uses" placeholder="Usos máx." class="border rounded-lg px-3 py-2">
<input type="date" name="expires_at" class="border rounded-lg px-3 py-2">
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Criar</button></form>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Código</th><th>%</th><th>Usos</th><th>Ativo</th><th></th></tr></thead>
<tbody>@forelse(($coupons??[]) as $c)<tr class="border-b"><td class="p-3 font-mono font-bold">{{ $c->code }}</td><td>{{ $c->percent }}%</td><td>{{ $c->used_count??0 }}/{{ $c->max_uses??'∞' }}</td><td>{{ !empty($c->active)?'Sim':'Não' }}</td>
<td class="p-3 text-right whitespace-nowrap">
<form method="POST" action="{{ Route::has('coupons.toggle')?route('coupons.toggle',$c):'#' }}" class="inline">@csrf<button class="text-blue-600 mr-2 text-xs">Ativar/Desativar</button></form>
<form method="POST" action="{{ Route::has('coupons.destroy')?route('coupons.destroy',$c):'#' }}" class="inline" onsubmit="return confirm('Apagar?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form>
</td></tr>@empty<tr><td colspan="6" class="p-6 text-center text-gray-400">Sem cupons.</td></tr>@endforelse</tbody>
</table></div></div></div></x-app-layout>
