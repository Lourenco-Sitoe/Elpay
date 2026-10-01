<!DOCTYPE html><html lang="pt"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Certificado {{ $certificate->code }} — Elpay</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"></head>
<body class="bg-gray-100 font-sans">
<header class="bg-white border-b"><div class="max-w-3xl mx-auto px-4 py-3"><a href="{{ url('/') }}" class="text-xl font-extrabold text-blue-600 flex items-center gap-2"><x-logo class="h-7 w-7" /> Elpay</a></div></header>
<main class="max-w-3xl mx-auto px-4 py-10">
<div class="bg-white rounded-2xl shadow-xl p-10 text-center border-8 border-double border-blue-600">
<i class="bi bi-award text-6xl text-amber-500"></i>
<p class="tracking-widest text-xs text-gray-500 mt-4">CERTIFICADO DE CONCLUSÃO</p>
<h1 class="text-3xl font-extrabold mt-2">{{ $certificate->user->name }}</h1>
<p class="text-gray-600 mt-3">concluiu com sucesso o curso</p>
<h2 class="text-xl font-bold text-blue-700 mt-1">{{ $certificate->product->title }}</h2>
<p class="font-mono text-sm text-gray-500 mt-4">{{ $certificate->code }} · {{ $certificate->issued_at?->format('d/m/Y') }}</p>
<button onclick="window.print()" class="mt-6 px-6 py-2 bg-gray-900 text-white text-sm rounded-lg">Imprimir</button>
</div></main></body></html>
