<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Quizzes — {{ $lesson->title ?? 'Aula' }}</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<h3 class="font-bold text-sm mb-2">Novo quiz (JSON de perguntas)</h3>
<p class="text-xs text-gray-500 mb-2">Formato: [{"q":"Pergunta?","options":["A","B"],"answer":"0"}] — answer = índice da correta.</p>
<form method="POST" action="{{ route('quizzes.store', $lesson) }}" class="grid gap-2 text-sm">
@csrf
<input name="title" required placeholder="Título do quiz" class="border rounded-lg px-3 py-2">
<input type="number" name="passing_score" value="70" min="0" max="100" class="border rounded-lg px-3 py-2 w-32" title="Nota mínima">
<textarea name="questions_json" required rows="4" class="border rounded-lg px-3 py-2 font-mono text-xs" placeholder='[{"q":"2+2?","options":["3","4"],"answer":"1"}]'></textarea>
<button class="px-3 py-2 bg-blue-600 text-white rounded-lg">Criar quiz</button>
</form>
</div>
@forelse($quizzes as $q)
<div class="bg-white rounded-xl shadow p-4 mb-3 text-sm">
<div class="flex justify-between"><strong>{{ $q->title }}</strong><span class="text-xs text-gray-500">{{ count($q->questions ?? []) }} perguntas · mín {{ $q->passing_score }}% · {{ $q->attempts_count }} tentativas</span></div>
<form method="POST" action="{{ route('quizzes.destroy', $q) }}" class="mt-2" onsubmit="return confirm('Remover quiz?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Remover</button></form>
</div>
@empty<p class="text-center text-gray-400 text-sm">Sem quizzes nesta aula.</p>
@endforelse
</div></x-app-layout>
