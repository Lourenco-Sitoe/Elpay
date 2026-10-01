<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Painel do Afiliado</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
@include('layouts.partials.flash')
@php $s=$stats??[]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
@foreach([['Cliques',$s['clicks']??0,'bi-cursor'],['Conversões',$s['conversions']??0,'bi-check-circle'],['Comissões (MT)',$s['commissions']??0,'bi-percent'],['Saldo (MT)',$s['balance']??0,'bi-wallet2']] as $c)
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500"><i class="bi {{ $c[2] }}"></i> {{ $c[0] }}</div><div class="font-bold text-xl">{{ $c[1] }}</div></div>
@endforeach
</div>
<div class="bg-white rounded-xl shadow p-4 mb-6"><h3 class="font-semibold mb-2">Comissões / mês</h3><canvas id="chA"></canvas></div>
<div class="bg-white rounded-xl shadow p-4">
<h3 class="font-semibold mb-3">Comissões recentes</h3>
<div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Produto</th><th>Valor</th><th>Estado</th></tr></thead>
<tbody>@forelse(($recentCommissions??$recentSales??[]) as $r)<tr class="border-b"><td>{{ $r->product->title??$r->sale->product->title??'-' }}</td><td>{{ number_format($r->amount??0,2) }}</td><td>{!! statusBadge($r->status??'-') !!}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-gray-400">Sem comissões ainda. <a class="text-blue-600 underline" href="{{ Route::has('affiliate.marketplace')?route('affiliate.marketplace'):'#' }}">Ver marketplace</a></td></tr>@endforelse</tbody></table></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>new Chart(document.getElementById('chA'),{type:'line',data:{labels:@json(($monthlySales??collect())->pluck('month')??[]),datasets:[{data:@json(($monthlySales??collect())->pluck('total')??[]),fill:true}]},options:{plugins:{legend:{display:false}}}});</script>
</x-app-layout>
