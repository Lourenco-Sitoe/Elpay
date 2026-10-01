<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Suporte</h2></div></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('support.store')?route('support.store'):'#' }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-3 gap-2">@csrf
<input name="subject" placeholder="Assunto" required class="border rounded-lg px-3 py-2 sm:col-span-1">
<input name="message" placeholder="Descreve o problema…" required class="border rounded-lg px-3 py-2 sm:col-span-1">
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Abrir ticket</button></form>
<div class="bg-white shadow rounded-xl divide-y">
@forelse(($tickets??$messages??[]) as $t)
<a href="{{ Route::has('support.show')?route('support.show',$t->id??$t):'#' }}" class="block p-4 hover:bg-gray-50"><div class="font-medium text-sm">{{ $t->subject??'Ticket #'.$t->id }}</div><div class="text-xs text-gray-500">{{ $t->status??'-' }} · {{ $t->created_at }}</div></a>
@empty<div class="p-8 text-center text-gray-400">Sem tickets.</div>@endforelse
</div></div></x-app-layout>
