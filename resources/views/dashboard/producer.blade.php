<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Dashboard</h2></x-slot>
@php
  $period = $period ?? '30';
  $pills = ['today' => 'Hoje', '7' => '7 dias', '30' => '30 dias', 'last_month' => 'Mês passado'];
  $q = fn($p) => request()->fullUrlWithQuery(['period' => $p]);
  $fmt = fn($v) => 'MZN ' . number_format($v ?? 0, 2);
  $plabel = $pills[$period] ?? '30 dias';
@endphp
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')

{{-- Papel --}}
<div class="t-pills mb-5">
  <span class="on" style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1.1rem;border-radius:.65rem;font-size:.9rem;font-weight:600"><i class="bi bi-shop"></i> Produtor</span>
</div>

{{-- Título + filtros --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
  <h1 class="t-page-title">Dashboard</h1>
  <div class="ml-auto flex flex-wrap items-center gap-3">
    <div class="t-pills">
      @foreach($pills as $k => $label)
        <a href="{{ $q($k) }}" class="{{ $period == $k ? 'on' : '' }}">{{ $label }}</a>
      @endforeach
    </div>
    <div class="t-pills">
      <span class="on">MZN</span>
      <span style="padding:.45rem 1.1rem;font-size:.9rem;color:#94a3b8" title="Brevemente">USD</span>
    </div>
  </div>
</div>

{{-- Estatísticas --}}
<div class="grid md:grid-cols-3 gap-5 mb-5">
  <div class="t-card p-6">
    <div class="t-stat-num">{{ $salesCount ?? 0 }}</div>
    <div class="t-stat-div"></div>
    <div class="t-stat-label">Total de Vendas | Últimos {{ $plabel === 'Hoje' ? 'hoje' : $plabel }}</div>
  </div>
  <div class="t-card p-6">
    <div class="t-stat-num green">{{ $fmt($revenue ?? 0) }}</div>
    <div class="t-stat-div"></div>
    <div class="t-stat-label">Total Recebido | Últimos {{ $plabel === 'Hoje' ? 'hoje' : $plabel }}</div>
  </div>
  <div class="t-card p-6">
    <div class="t-stat-num red">{{ $fmt($lost ?? 0) }}</div>
    <div class="t-stat-div"></div>
    <div class="t-stat-label">Total Perdido | Últimos {{ $plabel === 'Hoje' ? 'hoje' : $plabel }}</div>
  </div>
</div>

{{-- Gráfico + carteiras --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
  <div class="t-card lg:col-span-2">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Performance de Receita | Últimos {{ $plabel === 'Hoje' ? 'hoje' : $plabel }}</div>
    <div class="p-5"><div class="h-72"><canvas id="chTutora"></canvas></div></div>
  </div>
  <div class="t-card">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Total pago por carteira | Últimos {{ $plabel === 'Hoje' ? 'hoje' : $plabel }}</div>
    <div class="p-6 space-y-5">
      <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-lg bg-[#eb1c24] text-white flex flex-col items-center justify-center font-extrabold leading-none text-[13px]">M<span class="text-[8px] font-bold">PESA</span></span>
        <div><div class="font-medium">M-Pesa</div><div class="font-extrabold">{{ $fmt(($byWallet['mpesa'] ?? 0)) }}</div></div>
      </div>
      <div class="flex items-center gap-3">
        <span class="w-11 h-11 rounded-lg bg-[#f39200] text-white flex flex-col items-center justify-center font-extrabold leading-none text-[13px]">e<span class="text-[8px] font-bold">MOLA</span></span>
        <div><div class="font-medium">e-Mola</div><div class="font-extrabold">{{ $fmt(($byWallet['emola'] ?? 0)) }}</div></div>
      </div>
    </div>
  </div>
</div>

{{-- Vendas detalhadas --}}
<div class="t-card" id="vendas" style="scroll-margin-top:90px">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Vendas Detalhadas</div>
  <div class="p-6">
    <form method="GET" class="grid md:grid-cols-2 gap-4 max-w-xl">
      <input type="hidden" name="period" value="{{ $period }}">
      <div>
        <label class="t-label">Carteira</label>
        <select name="wallet" class="t-input">
          <option value="all" {{ ($wallet ?? 'all') === 'all' ? 'selected' : '' }}>Todas as carteiras</option>
          <option value="mpesa" {{ ($wallet ?? '') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
          <option value="emola" {{ ($wallet ?? '') === 'emola' ? 'selected' : '' }}>e-Mola</option>
        </select>
      </div>
      <div class="flex items-end"><button class="t-btn t-btn-sm"><i class="bi bi-search"></i> Filtrar</button></div>
    </form>
    <div class="overflow-x-auto mt-5 rounded-2xl border border-slate-100 dark:border-slate-700">
      <table class="t-table">
        <thead><tr><th>Data</th><th>Carteira</th><th>Produto</th><th>Cliente</th><th>Estado</th><th class="text-right">Valor</th></tr></thead>
        <tbody>
        @forelse(($recentSales ?? []) as $r)
          <tr>
            <td class="whitespace-nowrap">{{ optional($r->created_at)->format('d/m/Y H:i') }}</td>
            <td class="whitespace-nowrap">
              @if(($r->payment_method ?? '') === 'emola')
                <span class="w-8 h-8 rounded bg-[#f39200] text-white inline-flex items-center justify-center text-[10px] font-extrabold mr-1">eM</span> {{ $r->transaction_id ?? '' }}
              @else
                <span class="w-8 h-8 rounded bg-[#eb1c24] text-white inline-flex items-center justify-center text-[10px] font-extrabold mr-1">M</span> {{ $r->transaction_id ?? '' }}
              @endif
            </td>
            <td>{{ $r->product->title ?? '-' }}</td>
            <td><div class="font-medium">{{ $r->buyer_name ?? '-' }}</div><div class="text-xs text-slate-500">{{ $r->buyer_email ?? '' }}</div><div class="text-xs text-slate-500">{{ $r->buyer_phone ?? '' }}</div></td>
            <td>
              @if(($r->status ?? '') === 'paid')<span class="t-badge green">Pago</span>
              @elseif(in_array($r->status ?? '', ['cancelled', 'refunded']))<span class="t-badge soft-red">Falha</span>
              @else<span class="t-badge soft-amber">Pendente</span>@endif
            </td>
            <td class="text-right whitespace-nowrap"><div class="font-bold">MZN {{ number_format($r->final_price ?? 0, 2) }}</div><div class="text-xs text-slate-500">{{ $r->buyer_phone ?? '' }}</div></td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-slate-500">Nenhuma venda encontrada no período.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chTutora'), {
  type: 'bar',
  data: {
    labels: @json(($daily ?? collect())->pluck('day')),
    datasets: [{ label: 'Vendas (MZN)', data: @json(($daily ?? collect())->pluck('total')), backgroundColor: '#3b5bdb', borderRadius: 3 }]
  },
  options: { plugins: { legend: { display: true, position: 'top', align: 'center', labels: { boxWidth: 30, color: '#64748b' } } }, scales: { y: { ticks: { callback: v => v + ' MZN' } }, x: { ticks: { maxRotation: 60, minRotation: 60 } } } }
});
</script>
</x-app-layout>
