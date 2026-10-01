@extends('layouts.checkout')
@section('content')
<div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8 md:p-10 text-center max-w-2xl mx-auto">
@if(($sale->status??'')!=='paid')
<div class="w-20 h-20 mx-auto rounded-full bg-amber-100 flex items-center justify-center"><i class="bi bi-hourglass-split text-4xl text-amber-500"></i></div>
<h1 class="text-2xl md:text-3xl font-extrabold mt-5">A aguardar pagamento…</h1>
<p class="text-slate-500 mt-2">Confirma o pagamento de <b class="text-slate-800">{{ number_format($sale->final_price??0,2) }} MT</b> no teu telemóvel ({{ strtoupper($sale->payment_method??'') }}). O acesso é libertado automaticamente.</p>
<p class="text-xs text-slate-400 mt-2 font-mono">Ref: {{ $sale->payments->first()->transaction_ref ?? '#' . $sale->id }}</p>
<div class="mt-6"><a href="{{ url('/') }}" class="text-blue-600 font-semibold underline text-sm">Voltar ao início</a></div>
@else
<div class="w-20 h-20 mx-auto rounded-full bg-blue-100 flex items-center justify-center"><i class="bi bi-check-lg text-4xl text-blue-600"></i></div>
<h1 class="text-2xl md:text-3xl font-extrabold mt-5">Pagamento recebido!</h1>
<p class="text-slate-500 mt-2">Obrigado pela tua compra{{ isset($sale)?' <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">#'.$sale->id.'</span>':'' }}. Enviámos os detalhes para o teu email.</p>
@include('layouts.partials.flash')
@if(!empty($needsPassword??false))
<form method="POST" action="{{ Route::has('checkout.set-password')?route('checkout.set-password'):'#' }}" class="max-w-sm mx-auto mt-6 text-left bg-slate-50 rounded-2xl p-5 border">@csrf
<input type="hidden" name="sale_id" value="{{ $sale->id ?? '' }}">
<h3 class="font-bold mb-1">Cria a tua password</h3><p class="text-xs text-slate-500 mb-3">Para acederes aos teus produtos quando quiseres.</p>
<input type="password" name="password" required placeholder="Nova password" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 mb-2 outline-none focus:ring-2 focus:ring-blue-500">
<input type="password" name="password_confirmation" required placeholder="Confirmar password" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 outline-none focus:ring-2 focus:ring-blue-500">
<button class="mt-3 w-full py-2.5 bg-slate-900 hover:bg-slate-700 text-white font-semibold rounded-xl transition">Guardar e entrar</button></form>
@endif
@if(!empty($upsell) && empty($showDownsell))
@php $op = $upsell->upsellProduct ?? null; @endphp
@if($op)
<div class="mt-8 rounded-2xl p-6 md:p-8 bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-xl shadow-blue-600/25">
<p class="text-xs font-bold tracking-widest text-blue-200">OFERTA ÚNICA · SÓ AGORA</p>
<h3 class="font-extrabold text-xl mt-1">{{ $op->title }}</h3>
<p class="text-sm text-blue-50 mt-1">{{ \Illuminate\Support\Str::limit($op->description??'',120) }}</p>
<p class="mt-2"><span class="line-through text-blue-200 text-sm">{{ number_format($op->price??0,2) }} MT</span> <b class="text-2xl">{{ number_format($upsell->special_price ?? $op->price ?? 0,2) }} MT</b></p>
<div class="mt-4 flex justify-center gap-3">
<form method="POST" action="{{ Route::has('checkout.upsell.accept')?route('checkout.upsell.accept',$sale->id??0):'#' }}">@csrf<button class="px-7 py-3 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition">Sim, quero!</button></form>
<form method="POST" action="{{ Route::has('checkout.upsell.decline')?route('checkout.upsell.decline',$sale->id??0):'#' }}">@csrf<button class="px-6 py-3 border border-white/40 rounded-xl hover:bg-white/10 transition">Não, obrigado</button></form>
</div></div>
@endif
@endif
@if(!empty($upsell) && !empty($showDownsell) && !empty($upsell->downsellProduct))
@php $dp = $upsell->downsellProduct; @endphp
<div class="mt-6 border-2 border-dashed border-slate-200 rounded-2xl p-6 bg-slate-50">
<p class="text-xs font-bold tracking-widest text-slate-400">ALTERNATIVA MAIS EM CONTA</p>
<h3 class="font-extrabold text-lg mt-1">{{ $dp->title }} — {{ number_format($upsell->downsell_price ?? $dp->price ?? 0,2) }} MT</h3>
<div class="mt-4 flex justify-center gap-3">
<form method="POST" action="{{ Route::has('checkout.downsell.accept')?route('checkout.downsell.accept',$sale->id??0):'#' }}">@csrf<button class="px-7 py-3 bg-slate-900 hover:bg-slate-700 text-white font-bold rounded-xl transition">Aceitar</button></form>
<a href="{{ url('/') }}" class="px-6 py-3 border rounded-xl hover:bg-white transition">Ir para o início</a>
</div></div>
@endif
<div class="mt-8 flex items-center justify-center gap-5 text-xs text-slate-400">
<span><i class="bi bi-shield-lock-fill text-blue-500"></i> Compra segura</span>
<span><i class="bi bi-lightning-charge-fill text-blue-500"></i> Acesso imediato</span>
<a href="{{ url('/') }}" class="text-blue-600 font-semibold underline">Voltar ao início</a>
</div>
@endif
</div>
@endsection
