<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Histórico — {{ $user->name }}</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">#</th><th>Data</th><th>Produto</th><th>Estado</th><th class="text-right px-3">Valor</th></tr></thead>
<tbody>
@forelse($sales as $s)
<tr class="border-b"><td class="px-3">{{ $s->id }}</td><td>{{ $s->created_at?->format('d/m/Y H:i') }}</td>
<td>{{ $s->product->title ?? '-' }}</td><td>{!! statusBadge($s->status) !!}</td>
<td class="text-right px-3">MZN {{ number_format($s->final_price, 2) }}</td></tr>
@empty<tr><td colspan="5" class="py-6 text-center text-gray-400">Sem compras.</td></tr>
@endforelse
</tbody></table></div>
<a href="{{ route('producer.customers') }}" class="inline-block mt-3 text-sm text-blue-600">← Voltar</a>
</div></x-app-layout>
