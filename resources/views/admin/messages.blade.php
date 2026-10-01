@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<h2 class="font-bold mb-3">Mensagens de suporte</h2>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">De</th><th>Assunto</th><th>Estado</th><th>Data</th><th></th></tr></thead>
<tbody>@forelse(($messages??$tickets??[]) as $m)<tr class="border-b"><td>{{ $m->user->name??$m->name??'-' }}</td><td><div class="font-medium">{{ $m->subject??\Illuminate\Support\Str::limit($m->message??'',50) }}</div><div class="text-xs text-gray-500 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($m->message??'',120) }}</div>@if(!empty($m->admin_reply))<div class="text-xs text-blue-700 mt-1">Resposta: {{ $m->admin_reply }}</div>@endif</td><td>{{ $m->status??'-' }}</td><td>{{ $m->created_at }}</td>
<td class="text-right"><form method="POST" action="{{ Route::has('admin.messages.reply')?route('admin.messages.reply',$m):'#' }}" class="flex gap-1 justify-end">@csrf<input name="reply" required placeholder="Responder…" class="border rounded-lg px-2 py-1 text-xs w-40"><button class="text-blue-600 text-xs">Enviar</button></form></td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem mensagens.</td></tr>@endforelse</tbody>
</table></div>
@if(isset($messages)&&method_exists($messages,'links'))<div class="mt-3">{{ $messages->withQueryString()->links() }}</div>@endif
</div>
@endsection
