<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Elpay — Vende e compra produtos digitais em Moçambique</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#168a45">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900">
<header class="bg-white/90 backdrop-blur border-b border-slate-200 sticky top-0 z-20">
<div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-6">
<a href="{{ url('/') }}" class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><x-logo /> Elpay</a>
<nav class="hidden md:flex gap-5 text-sm font-medium text-slate-600">
<a href="#produtos" class="hover:text-blue-600">Produtos</a>
<a href="#como" class="hover:text-blue-600">Como funciona</a>
<a href="{{ route('affiliate.marketplace') }}" class="hover:text-blue-600">Marketplace</a>
</nav>
<div class="ml-auto flex items-center gap-2">
<button onclick="window.ElpayTheme?.toggle()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100" title="Tema"><i class="bi bi-moon"></i></button>
@auth<a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">Painel</a>
@else<a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-blue-600">Entrar</a>
@if(Route::has('register'))<a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold bg-slate-900 hover:bg-slate-700 text-white rounded-xl transition">Criar conta</a>@endif
@endauth
</div>
</div>
</header>

<section class="relative overflow-hidden bg-slate-900 text-white">
<div class="absolute inset-0 opacity-30" style="background:radial-gradient(600px 300px at 15% 20%,#168a4555,transparent),radial-gradient(500px 300px at 85% 80%,#1da45555,transparent)"></div>
<div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-10 items-center">
<div>
<span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-blue-500/15 text-blue-300 border border-blue-500/30"><i class="bi bi-lightning-charge-fill"></i> Feito para Moçambique · MZN</span>
<h1 class="text-4xl md:text-6xl font-extrabold leading-[1.05] mt-5">Vende cursos e ebooks com <span class="text-blue-400">M-Pesa</span> e <span class="text-orange-400">e-Mola</span></h1>
<p class="mt-5 text-slate-300 text-lg">Checkout em segundos, comissões automáticas para afiliados, order bumps, upsells e levantamentos rápidos.</p>
<div class="mt-8 flex flex-wrap gap-3">
<a href="#produtos" class="px-7 py-3.5 bg-blue-500 hover:bg-blue-400 text-slate-900 font-bold rounded-xl transition shadow-lg shadow-blue-500/25">Explorar produtos</a>
<a href="{{ Route::has('register')?route('register'):'#' }}" class="px-7 py-3.5 border border-white/25 hover:border-white/60 font-semibold rounded-xl transition">Começar a vender</a>
</div>
<div class="mt-8 flex gap-6 text-sm text-slate-300">
<div><b class="text-white text-xl block">{{ \App\Models\Product::where('status','approved')->count() }}+</b>produtos</div>
<div><b class="text-white text-xl block">{{ \App\Models\Sale::where('status','paid')->count() }}+</b>vendas</div>
<div><b class="text-white text-xl block">2%</b>taxa de saque</div>
</div>
</div>
<div class="hidden md:block">
<div class="bg-white/10 backdrop-blur border border-white/15 rounded-2xl p-5 shadow-2xl">
<div class="flex items-center justify-between text-sm"><span class="text-slate-300">Checkout Elpay</span><span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 text-xs font-semibold">AO VIVO</span></div>
<div class="mt-4 bg-white rounded-xl p-4 text-slate-900">
<div class="flex justify-between text-sm"><span>Curso Completo de Marketing</span><b>999,00 MT</b></div>
<div class="flex justify-between text-sm mt-1 text-blue-600"><span>Cupom PROMO10</span><span>-99,90 MT</span></div>
<div class="border-t mt-2 pt-2 flex justify-between font-extrabold"><span>Total</span><span>899,10 MT</span></div>
</div>
<div class="grid grid-cols-2 gap-3 mt-3">
<div class="rounded-xl p-3 text-center font-bold bg-red-500/90">M-Pesa</div>
<div class="rounded-xl p-3 text-center font-bold bg-orange-500/90">e-Mola</div>
</div>
<p class="text-xs text-slate-400 mt-3 text-center"><i class="bi bi-shield-lock-fill"></i> Pagamento 100% seguro · Acesso imediato</p>
</div>
</div>
</div>
</section>

<section id="produtos" class="max-w-7xl mx-auto px-4 py-14">
<div class="flex items-end justify-between mb-6">
<div><h2 class="text-2xl md:text-3xl font-extrabold">Produtos em destaque</h2><p class="text-slate-500 mt-1">Curadoria de produtores moçambicanos.</p></div>
<a href="{{ route('affiliate.marketplace') }}" class="text-sm font-semibold text-blue-600 hover:underline">Ver marketplace →</a>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
@forelse(($products ?? []) as $p)
<div class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-slate-100 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-1">
<div class="relative">
@if(!empty($p->cover))<img src="{{ asset('storage/'.$p->cover) }}" class="h-44 w-full object-cover group-hover:scale-105 transition duration-300" alt="">@else<div class="h-44 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center"><i class="bi bi-box text-5xl text-blue-400"></i></div>@endif
<span class="absolute top-3 left-3 text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-900/80 text-white uppercase">{{ $p->type ?? '' }}</span>
</div>
<div class="p-4 flex-1 flex flex-col">
<h3 class="font-bold leading-snug">{{ $p->title ?? 'Produto' }}</h3>
<p class="text-sm text-slate-500 line-clamp-2 mt-1">{{ \Illuminate\Support\Str::limit($p->description ?? '',90) }}</p>
<div class="mt-auto pt-4 flex items-center justify-between">
<span class="font-extrabold text-lg text-blue-600">{{ number_format($p->price ?? 0,2) }} <span class="text-xs font-semibold">MT</span></span>
<a href="{{ route('checkout.show',$p->slug ?? $p->id) }}" class="text-sm font-semibold px-4 py-2 bg-slate-900 hover:bg-blue-600 text-white rounded-xl transition">Comprar</a>
</div>
</div>
</div>
@empty
<div class="col-span-full text-center text-slate-500 py-12 bg-white rounded-2xl border border-dashed">Novos produtos em breve. <a class="text-blue-600 font-semibold underline" href="{{ Route::has('register')?route('register'):'#' }}">Sê o primeiro a vender</a>.</div>
@endforelse
</div>
</section>

<section id="como" class="bg-white border-y border-slate-200">
<div class="max-w-7xl mx-auto px-4 py-14">
<h2 class="text-2xl md:text-3xl font-extrabold text-center">Como funciona</h2>
<div class="grid md:grid-cols-3 gap-6 mt-8 text-center">
@foreach([['bi-upload','bg-blue-100 text-blue-600','1. Publica','Carrega o teu curso, ebook ou link e define a comissão de afiliados.'],['bi-share','bg-blue-100 text-blue-600','2. Divulga','Afiliados promovem com links únicos e ganham por cada venda.'],['bi-cash-coin','bg-amber-100 text-amber-600','3. Recebe','Recebe via M-Pesa/e-Mola e levanta para a tua conta.']] as $s)
<div class="p-8 rounded-2xl bg-slate-50 hover:bg-blue-50/60 transition">
<span class="inline-flex w-14 h-14 rounded-2xl {{ $s[1] }} items-center justify-center text-2xl"><i class="bi {{ $s[0] }}"></i></span>
<h3 class="font-bold mt-3">{{ $s[2] }}</h3><p class="text-sm text-slate-500 mt-1">{{ $s[3] }}</p>
</div>
@endforeach
</div>
</div>
</section>

<section class="max-w-7xl mx-auto px-4 py-14">
<div class="rounded-3xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-center px-6 py-14 shadow-xl">
<h2 class="text-3xl font-extrabold">Pronto para vender hoje?</h2>
<p class="text-blue-50 mt-2">Cria a conta em 1 minuto. Sem cartão, sem complicações.</p>
<a href="{{ Route::has('register')?route('register'):'#' }}" class="inline-block mt-6 px-8 py-3.5 bg-white text-blue-700 font-bold rounded-xl hover:bg-blue-50 transition">Criar conta grátis</a>
</div>
</section>

<footer class="border-t border-slate-200">
<div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center gap-3 text-sm text-slate-500">
<a href="{{ url('/') }}" class="font-extrabold text-blue-600 text-lg flex items-center gap-2"><x-logo class="h-7 w-7" /> Elpay</a>
<span>© {{ date('Y') }} · Plataforma de produtos digitais · Moçambique</span>
<span class="md:ml-auto flex gap-4"><a href="{{ route('login') }}" class="hover:text-blue-600">Entrar</a><a href="{{ route('support.index') }}" class="hover:text-blue-600">Suporte</a></span>
</div>
</footer>
</body>
</html>
