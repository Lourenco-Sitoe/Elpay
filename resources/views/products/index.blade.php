<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Gerir produtos</h2></x-slot>
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')

<div class="flex flex-wrap items-start gap-3 mb-5">
  <div>
    <h1 class="t-page-title">Gerir produtos</h1>
    <p class="t-page-sub">Crie, edite e acompanhe o estado dos seus conteúdos.</p>
  </div>
  <a href="{{ Route::has('products.create') ? route('products.create') : '#' }}" class="t-btn ml-auto"><i class="bi bi-plus-circle"></i> Criar novo produto</a>
</div>

{{-- Filtros --}}
<div class="t-card p-5 mb-6">
  <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div>
      <label class="t-label">Pesquisar</label>
      <div class="relative">
        <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Nome do produto" class="t-input" style="padding-left:2.5rem">
      </div>
    </div>
    <div>
      <label class="t-label">Estado</label>
      <select name="status" class="t-input">
        <option value="">Todos</option>
        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Ativo</option>
        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitado</option>
      </select>
    </div>
    <div>
      <label class="t-label">Categoria</label>
      <select class="t-input"><option>Todas</option></select>
    </div>
    <div>
      <label class="t-label">Moeda</label>
      <select class="t-input"><option>MZN — Metical</option></select>
    </div>
    <div class="sm:col-span-2 lg:col-span-4"><button class="t-btn t-btn-sm"><i class="bi bi-search"></i> Pesquisar</button></div>
  </form>
</div>

<h2 class="font-bold text-[22px] mb-4">Meus produtos</h2>
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
@forelse(($products ?? []) as $p)
  <a href="{{ Route::has('products.show') ? route('products.show', $p) : '#' }}" class="t-prod">
    <div class="relative">
      @if(!empty($p->cover))
        <img src="{{ asset('storage/' . $p->cover) }}" class="t-cover" alt="">
      @else
        <div class="t-cover bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center" style="height:11rem"><i class="bi bi-box text-5xl text-blue-600/50"></i></div>
      @endif
      @if(($p->status ?? '') === 'approved')
        <span class="t-tag">Ativo</span>
      @elseif(($p->status ?? '') === 'pending')
        <span class="t-tag" style="background:#fef3c7;color:#b45309">Pendente</span>
      @else
        <span class="t-tag" style="background:#fee2e2;color:#b91c1c">Rejeitado</span>
      @endif
    </div>
    <div class="p-4 flex-1 flex flex-col">
      <h3 class="font-bold leading-snug">{{ $p->title }}</h3>
      <p class="text-sm text-slate-500 mt-0.5">{{ ucfirst($p->type ?? 'Produto digital') }}</p>
      <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-700" style="margin-top:1rem;padding-top:.9rem">
        <span class="text-sm text-slate-500">#{{ $p->id }}</span>
        <span class="font-extrabold">MZN {{ number_format($p->price ?? 0, 2) }}</span>
      </div>
    </div>
  </a>
@empty
  <div class="col-span-full t-card p-10 text-center text-slate-500">Sem produtos. <a class="text-blue-700 font-semibold underline" href="{{ Route::has('products.create') ? route('products.create') : '#' }}">Criar o primeiro</a>.</div>
@endforelse
</div>
@if(isset($products) && method_exists($products, 'links'))<div class="mt-5">{{ $products->links() }}</div>@endif
</div>
</x-app-layout>
