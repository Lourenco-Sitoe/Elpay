<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Marketplace</h2></x-slot>
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<h1 class="t-page-title mb-5">Marketplace</h1>

<div class="t-card p-6 mb-6 text-center">
  <h2 class="font-bold text-[22px]">Qual produto você está procurando?</h2>
  <p class="t-page-sub">Encontre os melhores produtos para se afiliar!</p>
  <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-5 text-left">
    <div><label class="t-label">Buscar produto</label><input name="q" value="{{ request('q') }}" placeholder="Buscar produto..." class="t-input"></div>
    <div><label class="t-label">Comissão</label><select class="t-input"><option>Todas as taxas</option></select></div>
    <div><label class="t-label">Preço</label><select class="t-input"><option>Todos os preços</option></select></div>
    <div><label class="t-label">Categoria</label><select class="t-input"><option>Todas as categorias</option></select></div>
    <div><label class="t-label">Moeda</label><select class="t-input"><option>Todas as moedas</option></select></div>
    <div class="sm:col-span-2 lg:col-span-5"><button class="t-btn t-btn-sm"><i class="bi bi-search"></i> Buscar</button></div>
  </form>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
@forelse(($products ?? []) as $p)
  <div class="t-prod">
    <div class="relative">
      @if(!empty($p->cover))
        <img src="{{ asset('storage/' . $p->cover) }}" class="t-cover" alt="">
      @else
        <div class="t-cover bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-slate-700 dark:to-slate-600 flex items-center justify-center" style="height:11rem"><i class="bi bi-box text-5xl text-blue-600/50"></i></div>
      @endif
    </div>
    <div class="p-4 flex-1 flex flex-col">
      <h3 class="font-bold leading-snug">{{ $p->title }}</h3>
      <div class="mt-auto pt-3 space-y-1" style="margin-top:.9rem">
        <p class="text-sm text-slate-600 dark:text-slate-300">Preço do Produto: <b class="text-blue-700">{{ number_format($p->price ?? 0, 2) }} MZN</b></p>
        <p class="text-sm text-slate-600 dark:text-slate-300">Receba até <b class="text-blue-700">{{ $p->commission_rate ?? 0 }}% por venda</b></p>
        <form method="POST" action="{{ Route::has('affiliate.join') ? route('affiliate.join', $p->id) : '#' }}">@csrf<button class="t-btn t-btn-sm w-full justify-center mt-2">Promover</button></form>
      </div>
    </div>
  </div>
@empty
  <div class="col-span-full t-card p-10 text-center text-slate-500">Sem produtos.</div>
@endforelse
</div>
@if(isset($products) && method_exists($products, 'links'))<div class="mt-5">{{ $products->links() }}</div>@endif
</div>
</x-app-layout>
