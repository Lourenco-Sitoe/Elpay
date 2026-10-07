<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Materiais de divulgação</h2></x-slot>
<div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
<div class="grid gap-3">
@forelse($materials as $m)
<div class="bg-white rounded-xl shadow p-4 text-sm">
<div class="font-bold">{{ $m->product->title ?? '' }} — {{ $m->title }}</div>
<div class="text-xs text-gray-500">{{ $m->kind }}</div>
@if($m->content)<p class="mt-1 break-words">{{ $m->content }}</p>@endif
@if($m->file_url)<a href="{{ $m->file_url }}" target="_blank" class="text-blue-600 text-xs">Abrir ficheiro</a>@endif
</div>
@empty<p class="text-center text-gray-400 text-sm">Sem materiais disponíveis.</p>
@endforelse
</div>
</div></x-app-layout>
