<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Contas de saque</h2></x-slot>
<div class="py-6 max-w-4xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ route('payouts.accounts.store') }}" class="bg-white shadow rounded-xl p-4 mb-4 grid sm:grid-cols-4 gap-2">@csrf
<select name="method" class="border rounded-lg px-3 py-2"><option value="mpesa">M-Pesa</option><option value="emola">e-Mola</option><option value="bank">Banco</option></select>
<input name="label" placeholder="Rótulo (ex: M-Pesa pessoal)" required class="border rounded-lg px-3 py-2">
<input name="phone" placeholder="Número / IBAN" class="border rounded-lg px-3 py-2">
<label class="text-sm flex items-center gap-2"><input type="checkbox" name="is_default" value="1" class="rounded"> Predefinida</label>
<button class="px-4 py-2 bg-blue-600 text-white rounded-lg sm:col-span-4">Adicionar conta</button></form>
<div class="bg-white shadow rounded-xl overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b bg-gray-50"><th class="p-3">Rótulo</th><th>Método</th><th>Destino</th><th>Predef.</th><th></th></tr></thead>
<tbody>@forelse(($accounts??[]) as $a)<tr class="border-b"><td class="p-3 font-medium">{{ $a->label }}</td><td>{{ strtoupper($a->method) }}</td><td>{{ $a->phone??'-' }}</td><td>{{ $a->is_default?'Sim':'Não' }}</td>
<td class="p-3 text-right"><form method="POST" action="{{ route('payouts.accounts.destroy',$a) }}" class="inline" onsubmit="return confirm('Remover?')">@csrf @method('DELETE')<button class="text-red-600 text-xs">Apagar</button></form></td></tr>@empty<tr><td colspan="5" class="p-6 text-center text-gray-400">Sem contas guardadas.</td></tr>@endforelse</tbody>
</table></div></div></div></x-app-layout>
