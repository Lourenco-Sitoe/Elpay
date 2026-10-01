<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">{{ $lesson->title }}</h2><a href="{{ Route::has('learn.course')?route('learn.course',$product->id):'#' }}" class="text-sm text-blue-600 underline">Voltar ao curso</a></div></x-slot>
<div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl p-6">
@if(($lesson->kind??'text')==='video' && !empty($lesson->url))
<div class="aspect-video bg-black rounded-lg overflow-hidden mb-4"><iframe src="{{ $lesson->url }}" class="w-full h-full" allowfullscreen></iframe></div>
@endif
@if(!empty($lesson->body))<div class="prose max-w-none text-sm whitespace-pre-line">{{ $lesson->body }}</div>@endif
@if(!empty($lesson->file_path))<a href="{{ asset('storage/'.$lesson->file_path) }}" class="mt-3 inline-block text-sm px-4 py-2 bg-gray-900 text-white rounded-lg"><i class="bi bi-download"></i> Descarregar anexo</a>@endif
@if(($lesson->kind??'')==='file' && !empty($lesson->url) && empty($lesson->file_path))<a href="{{ $lesson->url }}" target="_blank" class="mt-3 inline-block text-sm px-4 py-2 bg-gray-900 text-white rounded-lg">Abrir ficheiro</a>@endif
@if(empty($lesson->body) && empty($lesson->url) && empty($lesson->file_path))<p class="text-sm text-gray-400">Conteúdo em breve.</p>@endif
<div class="mt-6 border-t pt-4">
@if(!empty($completed))<p class="text-sm text-blue-600 font-semibold"><i class="bi bi-check-circle-fill"></i> Concluída</p>
@else<form method="POST" action="{{ Route::has('learn.complete')?route('learn.complete',$lesson->id):'#' }}">@csrf<button class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold">Marcar como concluída</button></form>@endif
</div>
</div></div></x-app-layout>
