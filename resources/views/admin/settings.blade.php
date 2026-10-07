@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-3xl">
<h2 class="font-bold mb-1">Configurações da plataforma</h2>
<p class="text-sm text-gray-500 mb-4">Só o Admin altera taxas, pagamentos e sistema. O produtor nunca vê este ecrã.</p>
@include('layouts.partials.flash')
<form method="POST" action="{{ route('admin.settings.update') }}">@csrf
@php $s = $settings ?? []; $v = fn($k, $d='') => old('settings.'.$k, $s[$k] ?? $d); @endphp
<div class="grid sm:grid-cols-2 gap-4">
<div><label class="text-sm font-medium">Taxa da plataforma (%)</label><input type="number" step="0.01" min="0" max="50" name="settings[platform_fee_percent]" value="{{ $v('platform_fee_percent', 5) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Comissão afiliado padrão (%)</label><input type="number" step="0.1" min="0" max="90" name="settings[default_commission_rate]" value="{{ $v('default_commission_rate', 10) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Saque mínimo (MZN)</label><input type="number" step="1" min="0" name="settings[min_withdrawal]" value="{{ $v('min_withdrawal', 100) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Taxa de saque (%)</label><input type="number" step="0.01" min="0" max="30" name="settings[withdrawal_fee_percent]" value="{{ $v('withdrawal_fee_percent', 2) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Gateway ativo</label>
<select name="settings[payment_driver]" class="mt-1 w-full border rounded-lg px-3 py-2">
@foreach(['mock'=>'Mock (testes)','mpesa'=>'M-Pesa','emola'=>'e-Mola'] as $k=>$l)
<option value="{{ $k }}" @selected($v('payment_driver','mock')==$k)>{{ $l }}</option>
@endforeach</select></div>
<div class="grid grid-cols-2 gap-3">
<div><label class="text-sm font-medium">M-Pesa ativo</label>
<select name="settings[mpesa_enabled]" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="1" @selected($v('mpesa_enabled','1')=='1')>Sim</option><option value="0" @selected($v('mpesa_enabled')=='0')>Não</option></select></div>
<div><label class="text-sm font-medium">e-Mola ativo</label>
<select name="settings[emola_enabled]" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="1" @selected($v('emola_enabled','1')=='1')>Sim</option><option value="0" @selected($v('emola_enabled')=='0')>Não</option></select></div>
</div>
<div><label class="text-sm font-medium">Nome do site</label><input name="settings[site_name]" value="{{ $v('site_name','Elpay') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Email de suporte</label><input type="email" name="settings[support_email]" value="{{ $v('support_email','suporte@elpay.co.mz') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Manutenção</label>
<select name="settings[maintenance]" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="0" @selected($v('maintenance','0')=='0')>Desligada</option><option value="1" @selected($v('maintenance')=='1')>Ligada</option></select></div>
</div>
<button class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg">Guardar</button>
</form>
</div>
@endsection
