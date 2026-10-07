<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Gerir produtos</h2></x-slot>
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')

<div class="flex flex-wrap items-start gap-3 mb-5">
  <div>
    <h1 class="t-page-title">Gerir produtos</h1>
    <p class="t-page-sub">Crie, edite e acompanhe o estado dos seus conteúdos.</p>
  </div>
  <div x-data="{ open:false }" class="ml-auto">
    <button @click="open=true" class="t-btn"><i class="bi bi-plus-circle"></i> Criar novo produto</button>
    <div x-show="open" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" style="display:none">
      <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
      <div class="relative bg-white dark:bg-slate-800 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-lg max-h-[92vh] overflow-y-auto" @click.outside="open=false">
        <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-slate-100 dark:border-slate-700 sticky top-0 bg-white dark:bg-slate-800">
          <h3 class="font-bold text-base sm:text-lg">Cadastrar novo produto</h3>
          <button @click="open=false" class="text-slate-400 hover:text-slate-600 text-xl leading-none px-1">&times;</button>
        </div>
        <form method="POST" action="{{ route('products.quick-store') }}" class="px-4 sm:px-6 py-4 sm:py-5 grid gap-4">
          @csrf
          <div><label class="text-sm font-medium">Nome do produto <span class="text-red-500">*</span></label>
          <input name="title" required placeholder="Ex.: Curso de Marketing Digital" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
          <div><label class="text-sm font-medium">Categoria <span class="text-red-500">*</span></label>
          <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2 text-slate-500">
            <option value="">Selecione uma categoria</option>
            @foreach(\App\Http\Controllers\ProductWizardController::CATEGORIES as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
          </select></div>
          <div><label class="text-sm font-medium">Formato <span class="text-red-500">*</span></label>
          <select name="type" required class="mt-1 w-full border rounded-lg px-3 py-2 text-slate-500">
            <option value="">Selecione um formato</option>
            @foreach(\App\Http\Controllers\ProductWizardController::FORMATS as $v=>$l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
          </select></div>
          <div><label class="text-sm font-medium">Moeda <span class="text-red-500">*</span></label>
          <select name="currency" class="mt-1 w-full border rounded-lg px-3 py-2"><option value="MZN">MZN — Metical</option><option value="USD">USD — Dólar</option></select></div>
          <div><label class="text-sm font-medium">Preço (MZN) <span class="text-red-500">*</span></label>
          <input type="number" step="0.01" min="1" name="price" required placeholder="0.00" class="mt-1 w-full border rounded-lg px-3 py-2">
          <p class="text-xs text-slate-500 mt-1">Mínimo de MZN 100.00 / USD 5.00.</p></div>
          <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
            <button type="button" @click="open=false" class="px-5 py-2.5 sm:py-2 border rounded-full text-sm">Cancelar</button>
            <button class="px-5 py-2.5 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-full">Criar produto</button>
          </div>
        </form>
      </div>
    </div>
  </div>
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
        @foreach(($statusLabels ?? []) as $v => $l)<option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="t-label">Categoria</label>
      <select name="category" class="t-input"><option value="">Todas</option>
        @foreach(($categories ?? []) as $c)<option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach
      </select>
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
@php
$cardStatus = function ($s) use ($statusLabels) { return $statusLabels[$s] ?? $s; };
@endphp
@forelse(($products ?? []) as $p)
  <div class="t-prod overflow-hidden">
    <div class="relative">
      @if(!empty($p->cover))
        <img src="{{ asset('storage/' . $p->cover) }}" class="t-cover" alt="" style="height:11rem">
      @else
        <div class="t-cover bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center" style="height:11rem"><i class="bi bi-box text-5xl text-blue-600/50"></i></div>
      @endif
      <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-2.5">
        <div class="min-w-0">
          @if(($p->status ?? '') === 'approved')
            <span class="inline-block text-[11px] sm:text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700 whitespace-nowrap">Ativo</span>
          @elseif(($p->status ?? '') === 'pending')
            <span class="inline-block text-[11px] sm:text-xs font-semibold px-3 py-1 rounded-full bg-blue-100 text-blue-700 whitespace-nowrap">Em análise</span>
          @elseif(($p->status ?? '') === 'draft')
            <span class="inline-block text-[11px] sm:text-xs font-semibold px-3 py-1 rounded-full bg-slate-200 text-slate-600 whitespace-nowrap">Rascunho</span>
          @elseif(($p->status ?? '') === 'suspended')
            <span class="inline-block text-[11px] sm:text-xs font-semibold px-3 py-1 rounded-full bg-orange-100 text-orange-700 whitespace-nowrap">Suspenso</span>
          @else
            <span class="inline-block text-[11px] sm:text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700 whitespace-nowrap">{{ $cardStatus($p->status) }}</span>
          @endif
        </div>
        <div class="flex gap-2 shrink-0">
          <a href="{{ Route::has('products.wizard') ? route('products.wizard', [$p, 'overview']) : '#' }}" title="Editar" aria-label="Editar produto"
             class="w-10 h-10 sm:w-9 sm:h-9 rounded-full bg-white shadow flex items-center justify-center text-blue-600 hover:bg-blue-50 active:scale-95"><i class="bi bi-pencil"></i></a>
          <form method="POST" action="{{ Route::has('products.destroy') ? route('products.destroy', $p) : '#' }}" onsubmit="return confirm('Eliminar este produto?')" title="Eliminar" class="inline-flex">
            @csrf @method('DELETE')
            <button aria-label="Eliminar produto" class="w-10 h-10 sm:w-9 sm:h-9 rounded-full bg-white shadow flex items-center justify-center text-red-500 hover:bg-red-50 active:scale-95"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
    <a href="{{ Route::has('products.wizard') ? route('products.wizard', [$p, 'overview']) : (Route::has('products.show') ? route('products.show', $p) : '#') }}" class="p-4 flex-1 flex flex-col">
      <h3 class="font-extrabold leading-snug text-blue-700 dark:text-blue-400 uppercase">{{ $p->title }}</h3>
      <p class="text-sm text-slate-500 mt-0.5">{{ $p->category ?? '' }}{{ $p->category ? ' · ' : '' }}{{ ($formats[$p->type] ?? ucfirst($p->type ?? '')) }}</p>
      <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-700" style="margin-top:1rem;padding-top:.9rem">
        <span class="text-sm text-slate-500">#{{ $p->id }}</span>
        <span class="font-extrabold">{{ $p->currency ?? 'MZN' }} {{ number_format($p->price ?? 0, 2) }}</span>
      </div>
    </a>
  </div>
@empty
  <div class="col-span-full t-card p-10 text-center text-slate-500">Sem produtos. <a class="text-blue-700 font-semibold underline" href="{{ Route::has('products.create') ? route('products.create') : '#' }}">Criar o primeiro</a>.</div>
@endforelse
</div>
@if(isset($products) && method_exists($products, 'links'))<div class="mt-5">{{ $products->links() }}</div>@endif
</div>
</x-app-layout>
