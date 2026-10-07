@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-4">
<div class="flex justify-between items-center mb-3"><h2 class="font-bold">Cursos / Áreas de membros</h2>
<form method="GET" class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Pesquisar…" class="border rounded-lg px-3 py-1.5 text-sm">
<button class="px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg">Filtrar</button></form></div>
<p class="text-xs text-gray-500 mb-3">O Admin pode gerir o conteúdo de qualquer curso. Usa “Conteúdo” para módulos, aulas e quizzes.</p>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Curso</th><th>Produtor</th><th>Módulos</th><th>Estado</th><th></th></tr></thead>
<tbody>
@forelse($products as $p)
<tr class="border-b">
<td>{{ $p->title }}</td><td>{{ $p->producer->name ?? '-' }}</td>
<td>{{ $p->modules->count() }}</td><td>{!! statusBadge($p->status) !!}</td>
<td class="text-right"><a href="{{ route('content.index', $p) }}" class="text-blue-600 text-xs">Gerir conteúdo</a></td>
</tr>
@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem cursos.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
