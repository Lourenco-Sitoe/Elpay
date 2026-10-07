@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<h2 class="font-bold mb-3">Novo banner / destaque</h2>
<form method="POST" action="{{ route('admin.banners.store') }}" class="grid md:grid-cols-3 gap-3">
@csrf
<input name="title" required placeholder="Título" class="border rounded-lg px-3 py-2 text-sm">
<input name="image_url" placeholder="URL da imagem" class="border rounded-lg px-3 py-2 text-sm">
<input name="link_url" placeholder="URL destino" class="border rounded-lg px-3 py-2 text-sm">
<select name="position" class="border rounded-lg px-3 py-2 text-sm">
<option value="home_top">home_top</option><option value="home_mid">home_mid</option>
<option value="checkout_top">checkout_top</option><option value="app_home">app_home</option>
</select>
<input type="number" name="sort_order" value="0" min="0" class="border rounded-lg px-3 py-2 text-sm" title="Ordem">
<button class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">Criar banner</button>
</form>
</div>
<div class="bg-white rounded-xl shadow p-4">
<h2 class="font-bold mb-3">Banners ({{ $banners->total() ?? count($banners) }})</h2>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Título</th><th>Posição</th><th>Ordem</th><th>Estado</th><th></th></tr></thead>
<tbody>
@forelse($banners as $b)
<tr class="border-b">
<td><div class="font-medium">{{ $b->title }}</div><div class="text-xs text-gray-500">{{ $b->link_url }}</div></td>
<td>{{ $b->position }}</td><td>{{ $b->sort_order }}</td>
<td>{!! statusBadge($b->active ? 'active' : 'inactive') !!}</td>
<td class="text-right whitespace-nowrap">
<form method="POST" action="{{ route('admin.banners.toggle', $b) }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">{{ $b->active ? 'Desativar' : 'Ativar' }}</button></form>
<form method="POST" action="{{ route('admin.banners.destroy', $b) }}" class="inline" onsubmit="return confirm('Remover banner?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</td></tr>
@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem banners.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $banners->links() }}</div>
</div>
@endsection
