<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">{{ $product->title }}</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
@php $pct = ($total??0) ? round((($finished??0)/max(1,$total))*100) : 0; @endphp
<div class="bg-white shadow rounded-xl p-4 mb-4"><div class="flex justify-between text-sm mb-1"><span>Progresso</span><b>{{ $finished??0 }}/{{ $total??0 }} ({{ $pct }}%)</b></div><div class="h-2 bg-gray-100 rounded-full"><div class="h-2 bg-blue-500 rounded-full" style="width:{{ $pct }}%"></div></div>
@if(!empty($certificate))<p class="mt-2 text-sm">🎓 Certificado: <a class="text-blue-600 underline font-mono" href="{{ route('certificates.verify',$certificate->code) }}">{{ $certificate->code }}</a></p>@endif</div>
@forelse(($product->modules??[]) as $m)
<div class="bg-white shadow rounded-xl p-4 mb-3"><h3 class="font-semibold">{{ $m->title }}</h3>
<div class="mt-2 divide-y">@forelse(($m->lessons??[]) as $l)
<a href="{{ Route::has('learn.lesson')?route('learn.lesson',$l->id):'#' }}" class="flex justify-between items-center py-2 text-sm hover:text-blue-700"><span>{{ $l->title }} <span class="text-gray-400">({{ $l->kind }}{{ $l->duration_minutes?' · '.$l->duration_minutes.'min':'' }})</span></span>@if(isset($done[$l->id]))<i class="bi bi-check-circle-fill text-blue-500"></i>@else<i class="bi bi-circle text-gray-300"></i>@endif</a>
@empty<p class="text-xs text-gray-400 py-2">Sem aulas.</p>@endforelse</div></div>
@empty<div class="bg-white shadow rounded-xl p-8 text-center text-gray-400">Conteúdo em breve.</div>@endforelse
</div></x-app-layout>
