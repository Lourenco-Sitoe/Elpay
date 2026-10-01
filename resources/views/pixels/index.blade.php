<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Meta Pixels</h2><a href="{{ route('tools.index') }}" class="text-sm text-blue-600 underline">Ferramentas</a></div></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ route('pixels.store') }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-4 gap-2">@csrf
<input name="name" placeholder="Nome (ex: Pixel principal)" required class="border rounded-lg px-3 py-2">
<select name="platform" class="border rounded-lg px-3 py-2"><option value="facebook">Facebook/Instagram</option><option value="tiktok">TikTok</option><option value="google">Google</option></select>
<input name="pixel_id" placeholder="ID do pixel" required class="border rounded-lg px-3 py-2">
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Adicionar</button></form>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Nome</th><th>Plataforma</th><th>ID</th><th>Ativo</th><th></th></tr></thead>
<tbody>@forelse(($pixels??[]) as $p)<tr class="border-b"><td class="p-3 font-medium">{{ $p->name }}</td><td>{{ ucfirst($p->platform) }}</td><td class="font-mono text-xs">{{ $p->pixel_id }}</td><td>{{ $p->active?'Sim':'Não' }}</td>
<td class="p-3 text-right whitespace-nowrap">
<form method="POST" action="{{ route('pixels.toggle',$p) }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Ativar/Desativar</button></form>
<form method="POST" action="{{ route('pixels.destroy',$p) }}" class="inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form>
</td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-400">Sem pixels.</td></tr>@endforelse</tbody>
</table></div></div>
@if(isset($pixels)&&method_exists($pixels,'links'))<div class="mt-3">{{ $pixels->links() }}</div>@endif
</div></x-app-layout>
