<x-app-layout>
<x-slot name="header"><div class="flex justify-between items-center"><h2 class="font-semibold text-xl">Verificação KYC</h2><a href="{{ Route::has('kyc.create')?route('kyc.create'):'#' }}" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg">Nova submissão</a></div></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white shadow rounded-xl p-4 mb-4">Estado atual: <b>{{ $kyc->status ?? 'não submetido' }}</b>
@if(!empty($kyc->rejection_reason))<p class="text-sm text-red-600 mt-1">Motivo: {{ $kyc->rejection_reason }}</p>@endif
@if(!empty($kyc->admin_comment))<p class="text-sm text-gray-600 mt-1">Nota: {{ $kyc->admin_comment }}</p>@endif</div>
@if(!empty($kyc))
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Tipo doc.</th><th>Estado</th><th>Data</th></tr></thead>
<tbody><tr class="border-b"><td class="p-3">{{ $kyc->document_type ?? '-' }}</td><td>{{ $kyc->status ?? '-' }}</td><td class="p-3">{{ $kyc->created_at }}</td></tr></tbody>
</table></div></div>
@endif
</div></x-app-layout>
