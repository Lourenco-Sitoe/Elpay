<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Parceiros</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="flex justify-between items-center mb-3"><h2 class="font-bold text-lg">Parceiros — {{ $product->title }}</h2><a href="{{ route('products.index') }}" class="text-sm text-blue-600 underline">Produtos</a></div>
<form method="POST" action="{{ route('partners.store',$product) }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-3 gap-2">@csrf
<input type="email" name="email" placeholder="Email do parceiro" required class="border rounded-lg px-3 py-2">
<input type="number" step="0.1" min="1" max="90" name="share_percent" placeholder="% de participação" required class="border rounded-lg px-3 py-2">
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Convidar</button></form>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Parceiro</th><th>%</th><th>Estado</th><th></th></tr></thead>
<tbody>@forelse(($partners??[]) as $p)<tr class="border-b"><td class="p-3">{{ $p->user->name??$p->user->email??'-' }}</td><td>{{ $p->share_percent }}%</td><td>{!! statusBadge($p->status) !!}</td>
<td class="p-3 text-right whitespace-nowrap">
@if(($p->status??'')==='pending')<form method="POST" action="{{ route('partners.approve',$p) }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Aprovar</button></form>@endif
<form method="POST" action="{{ route('partners.destroy',$p) }}" class="inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</td></tr>@empty<tr><td colspan="4" class="p-6 text-center text-gray-400">Sem parceiros.</td></tr>@endforelse</tbody>
</table></div></div></div></x-app-layout>
