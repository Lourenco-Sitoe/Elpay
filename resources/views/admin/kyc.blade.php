@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Verificações KYC</h2>
<form method="GET" class="flex gap-2"><select name="status" class="border rounded-lg px-2 py-1.5 text-sm"><option value="">Todos</option>@foreach(['pending','approved','rejected'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select><button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Utilizador</th><th>Documento</th><th>Estado</th><th>Data</th><th></th></tr></thead>
<tbody>@forelse(($kycs??$verifications??[]) as $k)<tr class="border-b"><td>{{ $k->user->name??'-' }}</td><td>{{ $k->document_type??'-' }}</td><td>{!! statusBadge($k->status??'-') !!}</td><td>{{ $k->created_at }}</td>
<td class="text-right whitespace-nowrap">
<form method="POST" action="{{ Route::has('admin.kyc.approve')?route('admin.kyc.approve',$k):'#' }}" class="inline">@csrf<button class="text-blue-600 text-xs mr-2">Aprovar</button></form>
<form method="POST" action="{{ Route::has('admin.kyc.reject')?route('admin.kyc.reject',$k):'#' }}" class="inline">@csrf<input type="hidden" name="reason" id="k-{{ $k->id }}"><button class="text-red-600 text-xs" onclick="document.getElementById('k-{{ $k->id }}').value=prompt('Motivo:')||''">Rejeitar</button></form>
</td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem registos.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($kycs)&&method_exists($kycs,'links'))<div class="mt-3">{{ $kycs->withQueryString()->links() }}</div>@endif
</div>
@endsection
