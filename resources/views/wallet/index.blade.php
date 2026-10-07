<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Carteira</h2></x-slot>
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<div class="flex flex-wrap items-center gap-3 mb-5">
  <h1 class="t-page-title">Carteira</h1>
  <a href="{{ Route::has('wallet.request') ? route('wallet.request') : '#' }}" class="t-btn t-btn-sm ml-auto"><i class="bi bi-cash-stack"></i> Solicitar saque</a>
</div>

<div class="grid sm:grid-cols-3 gap-5 mb-5">
  <div class="t-card p-6"><div class="t-stat-num green">{{ number_format($balance ?? $available ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label">Saldo disponível</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ number_format($pending ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label">Pendente</div></div>
  <div class="t-card p-6"><div class="t-stat-num">{{ number_format($withdrawn ?? 0, 2) }} MZN</div><div class="t-stat-div"></div><div class="t-stat-label">Total levantado</div></div>
</div>

<div class="t-card">
  <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 font-semibold text-[17px]">Extrato de saques</div>
  <div class="overflow-x-auto">
    <table class="t-table">
      <thead><tr><th>Valor</th><th>Método</th><th>Estado</th><th>Data</th></tr></thead>
      <tbody>
      @forelse(($withdrawals ?? []) as $w)
        <tr>
          <td class="font-bold whitespace-nowrap">{{ number_format($w->amount ?? 0, 2) }} MZN</td>
          <td>{{ $w->method ?? $w->phone ?? '-' }}</td>
          <td>{!! statusBadge($w->status ?? '-') !!}</td>
          <td class="whitespace-nowrap">{{ $w->created_at }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-slate-500">Sem saques.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@if(isset($withdrawals) && method_exists($withdrawals, 'links'))<div class="mt-4">{{ $withdrawals->links() }}</div>@endif
</div>
</x-app-layout>
