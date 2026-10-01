<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Ticket #{{ $ticket->id??$message->id??'' }}</h2></x-slot>
<div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
@php $t=$ticket??$message??null; @endphp
<div class="bg-white shadow rounded-xl p-4 mb-4">
<div class="font-semibold">{{ $t->subject??'' }}</div>
<div class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $t->message??$t->body??'' }}</div>
</div>
<div class="space-y-2 mb-4">
@forelse(($replies??$t->replies??[]) as $r)
<div class="bg-white shadow rounded-xl p-3 {{ ($r->user_id??0)==auth()->id()?'ml-8':'mr-8' }}"><div class="text-xs text-gray-500">{{ $r->user->name??'Suporte' }} · {{ $r->created_at }}</div><div class="text-sm mt-1">{{ $r->message??$r->body??'' }}</div></div>
@empty<p class="text-sm text-gray-400">Sem respostas ainda.</p>@endforelse
</div>
@if(!empty($t->admin_reply))
<div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
<div class="text-xs text-gray-500 mb-1">Resposta do suporte</div>
<div class="text-sm whitespace-pre-line">{{ $t->admin_reply }}</div>
</div>
@else
<p class="text-sm text-gray-400 mb-4">Sem resposta do suporte ainda. Normalmente respondemos em 24h.</p>
@endif
<a href="{{ Route::has('support.index')?route('support.index'):'#' }}" class="text-sm text-blue-600 underline">Voltar às mensagens</a>
</div></x-app-layout>
