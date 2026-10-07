<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Materiais — {{ $product->title }}</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<h3 class="font-bold text-sm mb-2">Novo material de divulgação</h3>
<form method="POST" action="{{ route('materials.store', $product) }}" class="grid md:grid-cols-4 gap-2 text-sm">
@csrf
<input name="title" required placeholder="Título" class="border rounded-lg px-3 py-2">
<select name="kind" class="border rounded-lg px-2 py-2">
<option value="text">Texto</option><option value="image">Imagem</option><option value="video">Vídeo</option><option value="link">Link</option><option value="banner">Banner</option>
</select>
<input name="file_url" placeholder="URL ficheiro/imagem" class="border rounded-lg px-3 py-2">
<input name="content" placeholder="Texto / HTML / URL vídeo" class="border rounded-lg px-3 py-2">
<button class="px-3 py-2 bg-blue-600 text-white rounded-lg md:col-span-4">Criar</button>
</form>
</div>
<div class="grid gap-3">
@forelse($materials as $m)
<div class="bg-white rounded-xl shadow p-4 text-sm">
<div class="flex justify-between"><strong>{{ $m->title }}</strong><span class="text-xs text-gray-500">{{ $m->kind }}</span></div>
@if($m->content)<p class="text-gray-600 mt-1 break-words">{{ $m->content }}</p>@endif
@if($m->file_url)<a href="{{ $m->file_url }}" target="_blank" class="text-blue-600 text-xs">{{ $m->file_url }}</a>@endif
<form method="POST" action="{{ route('materials.destroy', $m) }}" class="mt-2" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</div>
@empty<p class="text-center text-gray-400 text-sm">Sem materiais. Cria textos, imagens e vídeos para os teus afiliados.</p>
@endforelse
</div>
</div></x-app-layout>
