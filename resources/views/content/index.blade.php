<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Conteúdo do curso: {{ $product->title }}</h2><a href="{{ Route::has('products.index')?route('products.index'):'#' }}" class="text-sm text-blue-600 underline">Voltar</a></div></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('content.modules.store')?route('content.modules.store',$product->id):'#' }}" class="bg-white shadow rounded-xl p-4 mb-4 flex gap-2">@csrf
<input name="title" required placeholder="Novo módulo…" class="flex-1 border rounded-lg px-3 py-2"><button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Adicionar módulo</button></form>
@forelse(($product->modules??[]) as $m)
<div class="bg-white shadow rounded-xl p-4 mb-4">
<div class="flex justify-between items-center gap-2">
<form method="POST" action="{{ Route::has('content.modules.update')?route('content.modules.update',$m->id):'#' }}" class="flex gap-2 flex-1">@csrf @method('PUT')<input name="title" value="{{ $m->title }}" required class="flex-1 border rounded-lg px-3 py-1.5 text-sm font-semibold"><button class="text-xs text-blue-600">Guardar</button></form>
<form method="POST" action="{{ Route::has('content.modules.destroy')?route('content.modules.destroy',$m->id):'#' }}" onsubmit="return confirm('Apagar módulo e aulas?')">@csrf @method('DELETE')<button class="text-xs text-red-600">Apagar</button></form>
</div>
<div class="mt-3 space-y-2">
@forelse(($m->lessons??[]) as $l)
<div class="flex justify-between items-center border rounded-lg px-3 py-2 text-sm"><span><i class="bi {{ $l->kind==='video'?'bi-play-circle':($l->kind==='file'?'bi-file-earmark':'bi-text-paragraph') }}"></i> {{ $l->title }} <span class="text-gray-400">({{ $l->kind }}{{ $l->duration_minutes?' · '.$l->duration_minutes.'min':'' }})</span></span>
<span class="flex gap-2 items-center">
@if(Route::has('quizzes.index'))<a href="{{ route('quizzes.index', $l) }}" class="text-xs text-blue-600">Quizzes</a>@endif
<form method="POST" action="{{ Route::has('content.lessons.destroy')?route('content.lessons.destroy',$l->id):'#' }}" class="inline" onsubmit="return confirm('Apagar aula?')">@csrf @method('DELETE')<button class="text-xs text-red-600">Apagar</button></form>
</span>
</div>
@empty<p class="text-xs text-gray-400">Sem aulas neste módulo.</p>@endforelse
</div>
<form method="POST" action="{{ Route::has('content.lessons.store')?route('content.lessons.store',$m->id):'#' }}" enctype="multipart/form-data" class="mt-3 grid sm:grid-cols-6 gap-2 border-t pt-3">@csrf
<input name="title" required placeholder="Título da aula" class="border rounded-lg px-2 py-1.5 text-sm sm:col-span-2">
<select name="kind" class="border rounded-lg px-2 py-1.5 text-sm"><option value="video">Vídeo</option><option value="text">Texto</option><option value="file">Ficheiro</option></select>
<input name="url" placeholder="URL (YouTube/ficheiro)" class="border rounded-lg px-2 py-1.5 text-sm sm:col-span-2">
<input type="number" name="duration_minutes" placeholder="Min" class="border rounded-lg px-2 py-1.5 text-sm">
<input type="file" name="file" class="text-xs sm:col-span-3">
<textarea name="body" placeholder="Texto da aula (opcional)" rows="2" class="border rounded-lg px-2 py-1.5 text-sm sm:col-span-3"></textarea>
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg sm:col-span-6">Adicionar aula</button></form>
</div>
@empty<div class="bg-white shadow rounded-xl p-8 text-center text-gray-400">Sem módulos. Adiciona o primeiro acima.</div>@endforelse
</div></x-app-layout>
