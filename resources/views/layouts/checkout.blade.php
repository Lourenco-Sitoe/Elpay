<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name','Elpay') }} — Checkout seguro</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#2563eb">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 font-sans antialiased">
<header class="bg-white border-b">
<div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
<a href="{{ url('/') }}" class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><x-logo /> Elpay</a>
<div class="hidden sm:flex items-center gap-2 text-xs text-gray-500">
<span class="px-2 py-1 rounded bg-blue-100 text-blue-700">1 Produto</span>→
<span class="px-2 py-1 rounded bg-gray-100">2 Pagamento</span>→
<span class="px-2 py-1 rounded bg-gray-100">3 Acesso</span>
</div>
<div class="text-xs text-gray-600"><i class="bi bi-shield-lock-fill text-blue-600"></i> Compra segura · <span class="font-semibold">M-Pesa</span> / <span class="font-semibold">e-Mola</span></div>
</div>
</header>
<main class="max-w-3xl mx-auto px-4 py-6">
@include('layouts.partials.flash')
@yield('content')
</main>
<footer class="text-center text-xs text-gray-400 py-6">Elpay © {{ date('Y') }} · Pagamento 100% seguro</footer>
@stack('scripts')
@if(!empty($product ?? null))
@include('partials.pixels', ['product' => $product, 'purchaseValue' => ($sale->final_price ?? null)])
@elseif(!empty($sale->product ?? null))
@include('partials.pixels', ['product' => $sale->product, 'purchaseValue' => ($sale->final_price ?? null)])
@endif
</body>
</html>
