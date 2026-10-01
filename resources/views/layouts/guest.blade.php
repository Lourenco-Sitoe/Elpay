<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name','Elpay') }}</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#2563eb">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Elpay">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-600 to-indigo-700 min-h-screen">
<div class="min-h-screen flex flex-col items-center justify-center p-4">
<a href="{{ url('/') }}" class="mb-6 flex flex-col items-center gap-2"><x-logo class="h-16 w-16 shadow-xl" /><span class="text-3xl font-extrabold text-white">Elpay</span></a>
<div class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
@include('layouts.partials.flash')
{{ $slot }}
</div>
<p class="text-white/70 text-xs mt-4">Pagamentos via M-Pesa & e-Mola · Moçambique</p>
</div>
</body>
</html>
