<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">As minhas compras</h2></x-slot>
@php $s = $stats ?? []; @endphp
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<h1 class="t-page-title mb-5">As minhas compras</h1>

<div class="grid md:grid-cols-3 gap-5 mb-6">
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['purchases'] ?? count($purchases ?? $recentSales ?? []) }}</div><div class="t-stat-div"></div><div class="t-stat-label">Compras</div></div>
  <div class="t-card p-6"><div class="t-stat-num green">{{ number_format($s['spent'] ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label">Gasto total</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['items'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label">Produtos</div></div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
@forelse(($purchases ?? $recentSales ?? []) as $p)
  <div class="t-prod">
    @if(!empty($p->product->cover))
      <img src="{{ asset('storage/' . $p->product->cover) }}" class="t-cover" alt="">
    @else
      <div class="t-cover bg-gradient-to-br from-green-100 to-emerald-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center" style="height:11rem"><i class="bi bi-box text-5xl text-green-600/50"></i></div>
    @endif
    <div class="p-4 flex-1 flex flex-col">
      <h3 class="font-bold leading-snug">{{ $p->product->title ?? $p->title ?? 'Produto' }}</h3>
      <p class="text-sm text-slate-500">{{ $p->created_at ?? '' }}</p>
      <div class="mt-3 flex flex-wrap gap-2">
        @if(!empty($p->product_id))<a class="t-btn t-btn-sm" href="{{ Route::has('learn.course') ? route('learn.course', $p->product_id) : '#' }}">Estudar</a>@endif
        @if(!empty($p->product->file_path))<a class="t-btn t-btn-sm" style="background:#0f172a" href="{{ asset('storage/' . $p->product->file_path) }}">Descarregar</a>@endif
        @if(!empty($p->product->external_url))<a class="text-sm px-4 py-2 border border-slate-200 rounded-full font-semibold" href="{{ $p->product->external_url }}">Aceder</a>@endif
      </div>
    </div>
  </div>
@empty
  <div class="col-span-full t-card p-10 text-center text-slate-500">Ainda não compraste nada. <a class="text-green-700 font-semibold underline" href="{{ url('/') }}#produtos">Explorar produtos</a></div>
@endforelse
</div>
<div class="mt-5 text-center"><a href="{{ Route::has('certificates.mine') ? route('certificates.mine') : '#' }}" class="text-sm text-green-700 font-semibold underline"><i class="bi bi-award"></i> Meus certificados</a></div>
</div>
</x-app-layout>
