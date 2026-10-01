@extends('layouts.admin')
@section('content')
@php $s=$stats??[]; @endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
@foreach([['Receita total',$s['revenue']??0],['Vendas',$s['sales']??0],['Utilizadores',$s['users']??0],['Ticket médio',$s['avg']??0]] as $c)
<div class="bg-white rounded-xl shadow p-4"><div class="text-xs text-gray-500">{{ $c[0] }}</div><div class="font-bold text-lg">{{ $c[1] }}</div></div>
@endforeach
</div>
<div class="grid lg:grid-cols-2 gap-4">
<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold mb-2">Vendas por mês</h3><canvas id="rSales"></canvas></div>
<div class="bg-white rounded-xl shadow p-4"><h3 class="font-semibold mb-2">Por método</h3><canvas id="rMethods"></canvas></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('rSales'),{type:'bar',data:{labels:@json(($monthly??collect())->pluck('month')??['Jan','Fev','Mar']),datasets:[{data:@json(($monthly??collect())->pluck('total')??[0,0,0])}]},options:{plugins:{legend:{display:false}}}});
new Chart(document.getElementById('rMethods'),{type:'doughnut',data:{labels:['M-Pesa','e-Mola'],datasets:[{data:[{{ $mpesa??60 }},{{ $emola??40 }}]}]}});
</script>
@endsection
