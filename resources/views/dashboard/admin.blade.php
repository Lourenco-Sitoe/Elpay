@extends('layouts.admin')
@section('content')
@php $s = $stats ?? []; @endphp
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<h1 class="t-page-title mb-5">Administração</h1>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-5">
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['users'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-people text-blue-700"></i> Utilizadores</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['products'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-box text-blue-700"></i> Produtos</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ $s['sales'] ?? 0 }}</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-cart text-blue-700"></i> Vendas</div></div>
  <div class="t-card p-6"><div class="t-stat-num green">{{ number_format($s['revenue'] ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label"><i class="bi bi-cash-coin text-blue-700"></i> Receita</div></div>
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">
  <div class="t-card">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Vendas mensais</div>
    <div class="p-5"><div class="h-64"><canvas id="chSales"></canvas></div></div>
  </div>
  <div class="t-card">
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Receita mensal (MZN)</div>
    <div class="p-5"><div class="h-64"><canvas id="chRev"></canvas></div></div>
  </div>
</div>

<div class="t-card">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Vendas recentes</div>
  <div class="overflow-x-auto">
    <table class="t-table">
      <thead><tr><th>ID</th><th>Produto</th><th>Valor</th><th>Estado</th><th>Data</th></tr></thead>
      <tbody>
      @forelse(($recentSales ?? []) as $sale)
        <tr><td>#{{ $sale->id }}</td><td>{{ $sale->product->title ?? '-' }}</td><td class="font-bold whitespace-nowrap">{{ number_format($sale->final_price ?? 0, 2) }} MZN</td><td>{!! statusBadge($sale->status ?? '-') !!}</td><td class="whitespace-nowrap">{{ $sale->created_at }}</td></tr>
      @empty
        <tr><td colspan="5" class="py-10 text-center text-slate-500">Sem vendas.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = @json(($monthlySales ?? collect())->pluck('month') ?? []);
const totals = @json(($monthlySales ?? collect())->pluck('total') ?? []);
new Chart(document.getElementById('chSales'), { type: 'bar', data: { labels, datasets: [{ data: totals, backgroundColor: '#3b5bdb', borderRadius: 3 }] }, options: { plugins: { legend: { display: false } } } });
new Chart(document.getElementById('chRev'), { type: 'line', data: { labels, datasets: [{ data: totals, fill: true, backgroundColor: 'rgba(37,99,235,.12)', borderColor: '#2563eb', tension: .4 }] }, options: { plugins: { legend: { display: false } } } });
</script>
@endsection
