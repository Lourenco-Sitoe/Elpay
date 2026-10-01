@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-2xl">
<h2 class="font-bold mb-3">Configurações</h2>
<form method="POST" action="{{ Route::has('admin.settings.update')?route('admin.settings.update'):(Route::has('admin.settings')?route('admin.settings'):'#') }}">@csrf
@forelse(($settings??[]) as $k=>$v)
<div class="mb-3"><label class="text-sm font-medium">{{ $k }}</label><input name="settings[{{ $k }}]" value="{{ old('settings.'.$k,$v) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
@empty
<div class="mb-3"><label class="text-sm font-medium">Taxa da plataforma (%)</label><input name="settings[platform_fee]" value="{{ old('settings.platform_fee',$settings['platform_fee']??5) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Saque mínimo (MT)</label><input name="settings[min_withdrawal]" value="{{ old('settings.min_withdrawal',$settings['min_withdrawal']??100) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div class="mb-3"><label class="text-sm font-medium">Modo manutenção</label><select name="settings[maintenance]" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="0">Desligado</option><option value="1">Ligado</option></select></div>
@endforelse
<button class="px-6 py-2 bg-blue-600 text-white rounded-lg">Guardar</button></form>
</div>
@endsection
