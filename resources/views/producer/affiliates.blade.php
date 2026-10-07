<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Afiliados dos meus produtos</h2></x-slot>
<div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-4 mb-4">
<form method="GET" class="flex flex-wrap gap-2 text-sm">
<select name="product_id" class="border rounded-lg px-2 py-2"><option value="">Todos produtos</option>
@foreach($products as $p)<option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->title }} ({{ $p->affiliation_mode ?? 'automatic' }})</option>@endforeach</select>
<select name="status" class="border rounded-lg px-2 py-2"><option value="">Todos estados</option>
@foreach(['pending','approved','blocked'] as $s)<option @selected(request('status')==$s)>{{ $s }}</option>@endforeach</select>
<button class="px-3 py-2 bg-gray-900 text-white rounded-lg">Filtrar</button>
</form>
</div>
@foreach($products as $p)
<div class="bg-white rounded-xl shadow p-4 mb-3">
<form method="POST" action="{{ route('producer.affiliation.mode', $p) }}" class="flex flex-wrap items-center gap-2 text-sm">
@csrf @method('PUT')
<span class="font-bold">{{ $p->title }}</span>
<select name="affiliation_mode" class="border rounded-lg px-2 py-1.5">
<option value="automatic" @selected(($p->affiliation_mode ?? 'automatic')=='automatic')>Automática</option>
<option value="manual" @selected(($p->affiliation_mode ?? 'automatic')=='manual')>Com aprovação</option>
</select>
<input type="number" step="0.1" min="0" max="90" name="commission_rate" value="{{ $p->commission_rate }}" class="border rounded-lg px-2 py-1.5 w-24" title="Comissão %">
<input name="affiliation_rules" value="{{ $p->affiliation_rules }}" placeholder="Regras…" class="border rounded-lg px-2 py-1.5 flex-1 min-w-[200px]">
<a href="{{ route('materials.index', $p) }}" class="text-blue-600 text-xs">Materiais →</a>
<button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs">Guardar</button>
</form>
</div>
@endforeach
<div class="bg-white rounded-xl shadow overflow-x-auto"><table class="w-full text-sm">
<thead><tr class="text-left text-gray-500 border-b"><th class="py-2 px-3">Afiliado</th><th>Produto</th><th>Código</th><th>Cliques</th><th>Vendas</th><th>Conversão</th><th>Ganho</th><th>Estado</th><th></th></tr></thead>
<tbody>
@forelse($affiliates as $a)
@php $st = $stats[$a->id] ?? ['sales'=>0,'revenue'=>0,'clicks'=>0,'conversion'=>0,'earned'=>0]; @endphp
<tr class="border-b">
<td class="px-3"><div class="font-medium">{{ $a->user->name ?? '-' }}</div><div class="text-xs text-gray-500">{{ $a->user->email ?? '' }}</div></td>
<td>{{ $a->product->title ?? '-' }}</td>
<td class="font-mono text-xs">{{ $a->affiliate_code }}</td>
<td>{{ $st['clicks'] }}</td><td>{{ $st['sales'] }}</td><td>{{ $st['conversion'] }}%</td>
<td>MZN {{ number_format($st['earned'], 2) }}</td>
<td>{!! statusBadge($a->status) !!}</td>
<td class="text-right whitespace-nowrap px-3">
<form method="POST" action="{{ route('producer.affiliates.rate', $a) }}" class="inline">@csrf @method('PUT')
<input type="number" step="0.1" min="0" max="90" name="custom_rate" value="{{ $a->custom_rate }}" placeholder="%" class="border rounded w-16 px-1 py-0.5 text-xs" title="Comissão personalizada">
<button class="text-blue-600 text-xs">%</button></form>
@if($a->status=='pending')
<form method="POST" action="{{ route('producer.affiliates.approve', $a) }}" class="inline">@csrf<button class="text-blue-600 text-xs ml-1">Aprovar</button></form>
@endif
@if($a->status!='blocked')
<form method="POST" action="{{ route('producer.affiliates.reject', $a) }}" class="inline">@csrf<button class="text-red-600 text-xs ml-1">Bloquear</button></form>
@else
<form method="POST" action="{{ route('producer.affiliates.unblock', $a) }}" class="inline">@csrf<button class="text-blue-600 text-xs ml-1">Desbloquear</button></form>
@endif
</td></tr>
@empty<tr><td colspan="9" class="py-6 text-center text-gray-400">Sem afiliados.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-3">{{ $affiliates->withQueryString()->links() }}</div>
</div></x-app-layout>
