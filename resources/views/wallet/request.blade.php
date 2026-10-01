<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Solicitar saque</h2></x-slot>
<div class="py-6 max-w-xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<form method="POST" action="{{ Route::has('wallet.store')?route('wallet.store'):(Route::has('wallet.request')?route('wallet.request'):'#') }}" class="bg-white shadow rounded-xl p-6">@csrf
<div class="mb-3"><label class="text-sm font-medium">Valor (MT) — saldo: {{ number_format($balance??0,2) }} · mín. {{ number_format(setting('min_withdrawal',100),2) }} · taxa {{ setting('withdrawal_fee_percent',2) }}%</label><input type="number" step="0.01" min="{{ setting('min_withdrawal',100) }}" name="amount" value="{{ old('amount') }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Método</label><select name="method" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="mpesa">M-Pesa</option><option value="emola">e-Mola</option></select></div>
@if(!empty($accounts??[]) && count($accounts))
<div class="mb-3"><label class="text-sm font-medium">Ou usar conta guardada</label><select name="payout_account_id" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="">— escrever manualmente —</option>@foreach($accounts as $a)<option value="{{ $a->id }}">{{ $a->label }} ({{ $a->summary() }})</option>@endforeach</select></div>
@endif
<div class="mb-3"><label class="text-sm font-medium">Número destino</label><input name="phone" value="{{ old('phone') }}" pattern="[0-9]{9}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<button class="w-full py-2 bg-blue-600 text-white rounded-lg font-semibold">Confirmar pedido</button>
</form></div></x-app-layout>
