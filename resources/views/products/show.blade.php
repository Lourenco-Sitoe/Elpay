<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $product->title??'Produto' }} — Elpay</title>
<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#168a45">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"></head>
<body class="bg-slate-50 font-sans antialiased">
<header class="bg-white/90 backdrop-blur border-b border-slate-200 sticky top-0 z-10"><div class="max-w-5xl mx-auto px-4 h-16 flex items-center gap-4"><a href="{{ url('/') }}" class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><x-logo /> Elpay</a><div class="ml-auto text-xs text-slate-500 hidden sm:block"><i class="bi bi-shield-lock-fill text-blue-600"></i> Compra 100% segura</div></div></header>
<main class="max-w-5xl mx-auto px-4 py-8">
<nav class="text-xs text-slate-400 mb-4"><a href="{{ url('/') }}" class="hover:text-blue-600">Início</a> / <span>{{ $product->type??'' }}</span> / <span class="text-slate-600 font-medium">{{ \Illuminate\Support\Str::limit($product->title??'',40) }}</span></nav>
<div class="grid md:grid-cols-2 gap-8 items-start">
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
@if(!empty($product->cover??''))<img src="{{ asset('storage/'.$product->cover) }}" class="w-full aspect-[4/3] object-cover" alt="">@else<div class="aspect-[4/3] bg-gradient-to-br from-blue-100 via-indigo-50 to-slate-100 flex items-center justify-center"><i class="bi bi-box text-7xl text-blue-300"></i></div>@endif
<div class="p-4 flex items-center gap-4 text-xs text-slate-500 border-t">
<span><i class="bi bi-lightning-charge-fill text-blue-500"></i> Acesso imediato</span>
<span><i class="bi bi-phone text-blue-500"></i> M-Pesa / e-Mola</span>
<span><i class="bi bi-people text-blue-500"></i> {{ $product->sales_count??0 }} vendas</span>
</div>
</div>
<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 md:p-8 md:sticky md:top-24">
<span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 uppercase">{{ $product->type??'' }}</span>
<h1 class="text-3xl font-extrabold mt-3 leading-tight">{{ $product->title??'' }}</h1>
<p class="text-slate-600 mt-3 whitespace-pre-line leading-relaxed">{{ $product->description??'' }}</p>
<div class="flex items-end gap-2 mt-5"><span class="text-4xl font-extrabold text-blue-600">{{ number_format($product->price??0,2) }}</span><span class="font-bold text-slate-400 mb-1">MT</span></div>
<p class="text-xs text-slate-400">Pagamento único · acesso vitalício</p>
<a href="{{ Route::has('checkout.show')?route('checkout.show',$product->slug??$product->id??0):'#' }}" class="block text-center mt-5 px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-extrabold rounded-2xl transition shadow-xl shadow-blue-600/25"><i class="bi bi-cart-fill me-1"></i> Comprar agora</a>
<div class="mt-4 grid grid-cols-3 gap-2 text-center text-[11px] text-slate-500">
<div class="bg-slate-50 rounded-xl p-2.5"><i class="bi bi-shield-lock-fill text-blue-500 text-base block"></i>Seguro</div>
<div class="bg-slate-50 rounded-xl p-2.5"><i class="bi bi-lightning-charge-fill text-blue-500 text-base block"></i>Imediato</div>
<div class="bg-slate-50 rounded-xl p-2.5"><i class="bi bi-headset text-blue-500 text-base block"></i>Suporte</div>
</div>
</div>
</div>
</main>
<footer class="text-center text-xs text-slate-400 py-8">Elpay © {{ date('Y') }} · Vendido por {{ $product->producer->name??'produtor verificado' }}</footer>
</body></html>
