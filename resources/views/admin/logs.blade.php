@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<h2 class="font-bold mb-3">Logs do sistema</h2>
<div class="overflow-x-auto"><table class="w-full text-sm font-mono">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Data</th><th>Nível</th><th>Mensagem</th></tr></thead>
<tbody>@forelse(($logs??[]) as $l)<tr class="border-b"><td class="whitespace-nowrap">{{ $l->created_at??$l['date']??'-' }}</td><td>{{ $l->level??$l['level']??'-' }}</td><td class="text-xs">{{ $l->message??$l['message']??'' }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-gray-400 font-sans">Sem logs.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($logs)&&method_exists($logs,'links'))<div class="mt-3">{{ $logs->withQueryString()->links() }}</div>@endif
</div>
@endsection
