@extends('layouts.admin')
@section('content')
<h2 class="font-bold text-xl mb-4">Admin Dashboard</h2>
<div class="max-w-7xl mx-auto">
@include('layouts.partials.flash')
@php $s=$stats??[]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
@foreach([['Utilizadores',$s['users']??0,'bi-people','bg-blue-600'],['Produtos',$s['products']??0,'bi-box','bg-blue-600'],['Vendas',$s['sales']??0,'bi-cart','bg-amber-600'],['Receita (MT)',$s['revenue']??0,'bi-cash-coin','bg-purple-600']] as $c)
<div class="bg-white rounded-xl shadow p-4 flex items-center gap-3"><span class="w-10 h-10 rounded-lg text-white flex items-center justify-center {{ $c[3] }}"><i class="bi {{ $c[2] }}"></i></span><div><div class="text-xs text-gray-500">{{ $c[0] }}</div><div class="font-bold text-lg">{{ $c[1] }}</div></div></div>
@endforeach
</div>
<div class="grid lg:grid-cols-2 gap-4 mb-6">
<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold mb-2">Vendas mensais</h3><canvas id="chSales"></canvas></div>
<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold mb-2">Receita mensal (MT)</h3><canvas id="chRev"></canvas></div>
</div>
<div class="bg-white rounded-xl shadow p-4">
<h3 class="font-semibold mb-3">Vendas recentes</h3>
<div class="overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2">ID</th><th>Produto</th><th>Valor</th><th>Estado</th><th>Data</th></tr></thead>
<tbody>@forelse(($recentSales??[]) as $s)<tr class="border-b"><td>{{ $s->id }}</td><td>{{ $s->product->title ?? '-' }}</td><td>{{ number_format($s->final_price??0,2) }}</td><td>{!! statusBadge($s->status??'-') !!}</td><td>{{ $s->created_at }}</td></tr>@empty<tr><td colspan="5" class="py-4 text-center text-gray-400">Sem vendas.</td></tr>@endforelse</tbody>
</table></div>
</div>
</div>
@push('scripts' ?? 'x')@endpush
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chSales'),{type:'bar',data:{labels:@json(($monthlySales??collect())->pluck('month')??[]),datasets:[{data:@json(($monthlySales??collect())->pluck('total')??[])}]},options:{plugins:{legend:{display:false}}}});
new Chart(document.getElementById('chRev'),{type:'line',data:{labels:@json(($monthlySales??collect())->pluck('month')??[]),datasets:[{data:@json(($monthlySales??collect())->pluck('total')??[]),fill:true}]},options:{plugins:{legend:{display:false}}}});
</script>
@endsection
