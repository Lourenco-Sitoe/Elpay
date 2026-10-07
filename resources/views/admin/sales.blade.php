@extends('layouts.admin')
@section('content')
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<div class="flex flex-wrap items-center gap-3 mb-5">
  <div>
    <h1 class="t-page-title">Vendas</h1>
    <p class="t-page-sub">Transações dos seus produtos e o respetivo estado de pagamento.</p>
  </div>
  <div class="t-pills ml-auto"><span class="on">MZN</span></div>
</div>

<div class="grid md:grid-cols-3 gap-5 mb-5">
  <div class="t-card p-6"><div class="t-stat-num">{{ $totalCount ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-receipt text-blue-700"></i> Total de transações</div></div>
  <div class="t-card p-6"><div class="t-stat-num green">{{ $paidCount ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-check-circle text-blue-700"></i> Com sucesso</div></div>
  <div class="t-card p-6"><div class="t-stat-num red">{{ $failCount ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-x-circle text-red-500"></i> Com falha</div></div>
</div>
<div class="grid md:grid-cols-3 gap-5 mb-5">
  <div class="t-card p-6"><div class="t-stat-num">MZN {{ number_format($moved ?? 0, 2) }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-wallet2 text-blue-700"></i> Valor movimentado</div></div>
  <div class="t-card p-6"><div class="t-stat-num green">MZN {{ number_format($received ?? 0, 2) }}</div><div class="t-stat-div"></div><div class="t-stat-label"><span class="text-blue-700 font-bold">$</span> Total recebido</div></div>
  <div class="t-card p-6"><div class="t-stat-num red">MZN {{ number_format($lost ?? 0, 2) }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-graph-down-arrow text-blue-700"></i> Total perdido</div></div>
</div>

<div class="t-card">
  <div class="p-6">
    <form method="GET" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div><label class="t-label">Pesquisar</label><div class="relative"><i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i><input name="q" value="{{ request('q') }}" placeholder="Ref, nome, email..." class="t-input" style="padding-left:2.5rem"></div></div>
      <div><label class="t-label">Estado</label><select name="status" class="t-input"><option value="">Todos</option>@foreach(['pending' => 'Pendente', 'paid' => 'Pago', 'cancelled' => 'Falha', 'refunded' => 'Reembolsado'] as $v => $l)<option value="{{ $v }}" @selected(request('status') == $v)>{{ $l }}</option>@endforeach</select></div>
      <div><label class="t-label">De</label><input type="date" name="from" value="{{ request('from') }}" class="t-input"></div>
      <div><label class="t-label">Até</label><input type="date" name="to" value="{{ request('to') }}" class="t-input"></div>
      <div><label class="t-label">Moeda</label><select class="t-input"><option>MZN — Metical</option></select></div>
      <div class="sm:col-span-2 lg:col-span-5"><button class="t-btn t-btn-sm"><i class="bi bi-search"></i> Pesquisar</button></div>
    </form>
  </div>
  <div class="overflow-x-auto px-6 pb-6">
    <div class="rounded-2xl border border-slate-100 dark:border-slate-700 overflow-hidden">
    <table class="t-table">
      <thead><tr><th>Data</th><th>Carteira</th><th>Produto</th><th>Cliente</th><th>Estado</th><th class="text-right">Valor</th><th></th></tr></thead>
      <tbody>
      @forelse(($sales ?? []) as $s)
        <tr>
          <td class="whitespace-nowrap">{{ optional($s->created_at)->format('d/m/Y H:i') }}</td>
          <td class="whitespace-nowrap">
            @if(($s->payment_method ?? '') === 'emola')
              <span class="w-8 h-8 rounded bg-[#f39200] text-white inline-flex items-center justify-center text-[10px] font-extrabold mr-1">eM</span>
            @else
              <span class="w-8 h-8 rounded bg-[#eb1c24] text-white inline-flex items-center justify-center text-[10px] font-extrabold mr-1">M</span>
            @endif
            <span class="text-xs text-slate-500">#{{ $s->id }}</span>
          </td>
          <td>{{ $s->product->title ?? '-' }}</td>
          <td><div class="font-medium">{{ $s->buyer->name ?? $s->buyer_name ?? '-' }}</div><div class="text-xs text-slate-500">{{ $s->buyer->email ?? $s->buyer_email ?? '' }}</div></td>
          <td>
            @if(($s->status ?? '') === 'paid')<span class="t-badge green">Pago</span>
            @elseif(in_array($s->status ?? '', ['cancelled', 'refunded']))<span class="t-badge soft-red">Falha</span>
            @else<span class="t-badge soft-amber">Pendente</span>@endif
          </td>
          <td class="text-right whitespace-nowrap"><div class="font-bold">MZN {{ number_format($s->final_price ?? 0, 2) }}</div><div class="text-xs text-slate-500">{{ $s->buyer_phone ?? '' }}</div></td>
          <td class="text-right whitespace-nowrap">
            <a href="#" class="inline-flex w-9 h-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:text-blue-700 hover:border-blue-600" title="Ver"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="py-10 text-center text-slate-500">Nenhuma venda encontrada no período.</td></tr>
      @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>
@if(isset($sales) && method_exists($sales, 'links'))<div class="mt-4">{{ $sales->links() }}</div>@endif
</div>
@endsection
