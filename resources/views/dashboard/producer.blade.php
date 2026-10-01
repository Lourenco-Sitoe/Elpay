<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Painel do Produtor</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
@include('layouts.partials.flash')
@php $s=$stats??[]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
@foreach([['Meus produtos',$s['products']??0,'bi-box','from-blue-500 to-blue-600'],['Vendas',$s['sales']??0,'bi-cart','from-amber-500 to-orange-600'],['Receita (MT)',$s['revenue']??0,'bi-cash-coin','from-blue-500 to-indigo-600'],['Saldo (MT)',$s['balance']??0,'bi-wallet2','from-purple-500 to-indigo-600']] as $c)
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex items-center gap-3"><span class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $c[3] }} text-white flex items-center justify-center text-xl shadow"><i class="bi {{ $c[2] }}"></i></span><div><div class="text-xs text-slate-500">{{ $c[0] }}</div><div class="font-extrabold text-xl">{{ $c[1] }}</div></div></div>
@endforeach
</div>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6"><h3 class="font-bold mb-2">Minhas vendas / mês</h3><div class="h-64"><canvas id="chP"></canvas></div></div>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
<div class="flex justify-between items-center mb-3"><h3 class="font-bold">Vendas recentes</h3><a href="{{ Route::has('products.create')?route('products.create'):'#' }}" class="text-sm font-semibold px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">+ Novo produto</a></div>
<div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Produto</th><th>Valor</th><th>Data</th></tr></thead>
<tbody>@forelse(($recentSales??[]) as $r)<tr class="border-b"><td>{{ $r->product->title??'-' }}</td><td>{{ number_format($r->final_price??0,2) }}</td><td>{{ $r->created_at }}</td></tr>@empty<tr><td colspan="3" class="py-4 text-center text-gray-400">Sem vendas ainda.</td></tr>@endforelse</tbody>
</table></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>new Chart(document.getElementById('chP'),{type:'bar',data:{labels:@json(($monthlySales??collect())->pluck('month')??[]),datasets:[{data:@json(($monthlySales??collect())->pluck('total')??[])}]},options:{plugins:{legend:{display:false}}}});</script>
</x-app-layout>
