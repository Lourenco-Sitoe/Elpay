<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Dashboard</h2></x-slot>
@php $s = $stats ?? []; @endphp
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<div class="t-pills mb-5">
  <span class="on" style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem 1.1rem;border-radius:.65rem;font-size:.9rem;font-weight:600"><i class="bi bi-megaphone"></i> Afiliado</span>
</div>
<h1 class="t-page-title mb-5">Dashboard</h1>

<div class="grid md:grid-cols-3 gap-5 mb-5">
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['clicks'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-cursor text-green-700"></i> Cliques</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['conversions'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-check-circle text-green-700"></i> Conversões</div></div>
  <div class="t-card p-6"><div class="t-stat-num green">{{ number_format($s['commissions'] ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-percent text-green-700"></i> Comissões</div></div>
</div>

<div class="t-card mb-5">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Comissões / mês</div>
  <div class="p-5"><div class="h-64"><canvas id="chA"></canvas></div></div>
</div>

<div class="t-card">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Comissões recentes</div>
  <div class="overflow-x-auto">
    <table class="t-table">
      <thead><tr><th>Produto</th><th>Valor</th><th>Estado</th></tr></thead>
      <tbody>
      @forelse(($recentCommissions ?? $recentSales ?? []) as $r)
        <tr><td>{{ $r->product->title ?? $r->sale->product->title ?? '-' }}</td><td class="font-bold whitespace-nowrap">{{ number_format($r->amount ?? 0, 2) }} MZN</td><td>{!! statusBadge($r->status ?? '-') !!}</td></tr>
      @empty
        <tr><td colspan="3" class="py-10 text-center text-slate-500">Sem comissões ainda. <a class="text-green-700 font-semibold underline" href="{{ Route::has('affiliate.marketplace') ? route('affiliate.marketplace') : '#' }}">Ver marketplace</a></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>new Chart(document.getElementById('chA'), { type: 'line', data: { labels: @json(($monthlySales ?? collect())->pluck('month') ?? []), datasets: [{ data: @json(($monthlySales ?? collect())->pluck('total') ?? []), fill: true, backgroundColor: 'rgba(22,138,69,.12)', borderColor: '#168a45', tension: .4 }] }, options: { plugins: { legend: { display: false } } } });</script>
</x-app-layout>
