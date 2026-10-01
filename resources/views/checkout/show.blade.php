@extends('layouts.checkout')
@section('content')
@php $base=$product->price??0; @endphp
<div class="mb-5 flex items-center justify-center gap-2 text-xs font-semibold">
<span class="px-3 py-1.5 rounded-full bg-blue-600 text-white">1 · Dados</span><span class="text-slate-300">—</span>
<span class="px-3 py-1.5 rounded-full bg-white border text-slate-500">2 · Pagamento</span><span class="text-slate-300">—</span>
<span class="px-3 py-1.5 rounded-full bg-white border text-slate-500">3 · Acesso</span>
</div>
<div class="grid md:grid-cols-5 gap-6" x-data="{ total: {{ (float)$base }}, coupon: 0 }">
<div class="md:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
<div class="flex gap-4 items-start">
@if(!empty($product->cover))<img src="{{ asset('storage/'.$product->cover) }}" class="w-20 h-20 rounded-xl object-cover" alt="">@else<div class="w-20 h-20 rounded-xl bg-blue-100 flex items-center justify-center shrink-0"><i class="bi bi-box text-3xl text-blue-500"></i></div>@endif
<div><span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 uppercase">{{ $product->type??'' }}</span>
<h1 class="text-xl font-extrabold mt-1">{{ $product->title??'' }}</h1>
<p class="text-sm text-slate-500 mt-1">{{ \Illuminate\Support\Str::limit($product->description??'',140) }}</p></div>
</div>
<form id="checkoutForm" method="POST" action="{{ Route::has('checkout.process')?route('checkout.process'):'#' }}">
@csrf
<input type="hidden" name="product_id" value="{{ $product->id }}">
@if(!empty($ref))<input type="hidden" name="ref" value="{{ $ref }}">@endif
<input type="hidden" name="coupon" id="coupon_code" value="">
<h3 class="font-bold mt-6 mb-3 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">1</span> Os teus dados</h3>
<div class="grid sm:grid-cols-2 gap-3">
<div><label class="text-sm font-medium">Nome completo</label><input name="name" value="{{ old('name',auth()->user()->name??'') }}" required placeholder="Nome e apelido" class="mt-1 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
<div><label class="text-sm font-medium">Email <span class="text-slate-400 font-normal">(recebes o acesso aqui)</span></label><input type="email" name="email" value="{{ old('email',auth()->user()->email??'') }}" required placeholder="tu@email.co.mz" class="mt-1 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
<div class="sm:col-span-2"><label class="text-sm font-medium">Telemóvel M-Pesa / e-Mola</label><input name="phone" value="{{ old('phone') }}" required pattern="[0-9]{9}" placeholder="84 / 85 / 86 / 87 + 7 dígitos" class="mt-1 w-full border border-slate-200 rounded-xl px-3.5 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
</div>
@if(!empty($bumps??[]))
<div class="mt-6"><h3 class="font-bold mb-2 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-amber-100 text-amber-700 text-xs flex items-center justify-center font-bold">+</span> Aproveita também <span class="text-xs font-normal text-slate-400">ofertas exclusivas</span></h3>
@foreach(($bumps??[]) as $b)
<label class="flex items-start gap-3 border-2 border-dashed border-amber-200 bg-amber-50/50 rounded-xl p-3.5 mb-2 cursor-pointer hover:border-blue-400 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/60 transition">
<input type="checkbox" name="bumps[]" value="{{ $b->id }}" data-price="{{ (float)($b->price??0) }}" @change="total = {{ (float)$base }} - coupon + [...document.querySelectorAll('input[name=\'bumps[]\']:checked')].reduce((s,el)=>s+parseFloat(el.dataset.price),0)" class="mt-1 w-4 h-4 accent-blue-600">
<span><span class="font-semibold text-sm">{{ $b->title }}</span> <span class="text-blue-600 font-extrabold text-sm">+{{ number_format($b->price??0,2) }} MT</span><br><span class="text-xs text-slate-500">{{ $b->description??'' }}</span></span>
</label>
@endforeach</div>
@endif
<div class="mt-6"><h3 class="font-bold mb-3 flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">2</span> Método de pagamento</h3>
<div class="grid grid-cols-2 gap-3">
<label class="border-2 border-slate-200 rounded-2xl p-4 text-center cursor-pointer has-[:checked]:border-red-500 has-[:checked]:bg-red-50/50 transition"><input type="radio" name="payment_method" value="mpesa" checked class="accent-red-600"><div class="font-extrabold text-red-600 mt-1 text-lg">M-Pesa</div><div class="text-xs text-slate-500">Vodacom · 84/85</div></label>
<label class="border-2 border-slate-200 rounded-2xl p-4 text-center cursor-pointer has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50/50 transition"><input type="radio" name="payment_method" value="emola" class="accent-orange-500"><div class="font-extrabold text-orange-500 mt-1 text-lg">e-Mola</div><div class="text-xs text-slate-500">Movitel · 86/87</div></label>
</div></div>
<button class="mt-6 w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-lg rounded-2xl transition shadow-xl shadow-blue-600/25"><i class="bi bi-lock-fill me-1"></i> Pagar <span x-text="total.toFixed(2)"></span> MT</button>
<p class="text-center text-xs text-slate-400 mt-3"><i class="bi bi-shield-lock-fill text-blue-500"></i> Compra protegida · Acesso imediato após pagamento</p>
</form>
</div>
<div class="md:col-span-2"><div class="bg-slate-900 text-white rounded-2xl shadow-xl p-6 sticky top-4">
<h3 class="font-bold mb-3 text-slate-200">Resumo do pedido</h3>
<div class="flex justify-between text-sm py-1.5"><span class="text-slate-300">{{ $product->title??'' }}</span><span class="font-semibold">{{ number_format($base,2) }} MT</span></div>
<div class="flex justify-between text-sm py-1.5 text-blue-400"><span>Desconto cupom</span><span x-text="'-'+coupon.toFixed(2)"></span></div>
<div class="border-t border-white/10 mt-2 pt-3 flex justify-between font-extrabold text-lg"><span>Total</span><span x-text="total.toFixed(2)+' MT'"></span></div>
<div class="mt-4 flex gap-2">
<input id="couponInput" placeholder="Tens um cupom?" class="flex-1 min-w-0 bg-white/10 border border-white/15 rounded-xl px-3 py-2.5 text-sm placeholder:text-slate-400 outline-none focus:border-blue-400">
<button type="button" id="couponBtn" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-sm font-semibold rounded-xl transition">Aplicar</button>
</div>
<p id="couponMsg" class="text-xs mt-2 text-slate-400"></p>
<div class="mt-4 pt-4 border-t border-white/10 grid grid-cols-3 gap-2 text-center text-[11px] text-slate-400">
<div><i class="bi bi-lightning-charge-fill text-blue-400 text-lg block"></i>Imediato</div>
<div><i class="bi bi-shield-check text-blue-400 text-lg block"></i>Seguro</div>
<div><i class="bi bi-headset text-blue-400 text-lg block"></i>Suporte</div>
</div>
</div></div>
</div>
@push('scripts')
<script>
document.getElementById('couponBtn').addEventListener('click', async ()=>{
const code=document.getElementById('couponInput').value.trim(); if(!code) return;
try{
const r=await fetch("{{ Route::has('checkout.coupon.validate')?route('checkout.coupon.validate'):'' }}",{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({code,product_id:{{ (int)($product->id??0) }}} )});
const d=await r.json();
document.getElementById('couponMsg').textContent=d.message||(d.valid?'Cupom aplicado!':'Cupom inválido.');
if(d.valid){document.getElementById('coupon_code').value=code;const disc=parseFloat(d.discount||0);const base={{ (float)$base }};const bumps=[...document.querySelectorAll('input[name=\'bumps[]\']:checked')].reduce((s,el)=>s+parseFloat(el.dataset.price),0);const app=document.querySelector('[x-data]');if(app&&app._x_dataStack){app._x_dataStack[0].coupon=disc;app._x_dataStack[0].total=Math.max(0,base-disc+bumps);}}else{document.getElementById('coupon_code').value='';}
}catch(e){document.getElementById('couponMsg').textContent='Erro ao validar cupom.';}
});
</script>
@endpush
@endsection
