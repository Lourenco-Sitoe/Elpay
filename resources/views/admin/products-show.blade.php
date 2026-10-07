@extends('layouts.admin')
@section('content')
@include('layouts.partials.flash')
<div class="bg-white rounded-xl shadow p-6 mb-4">
<div class="flex flex-wrap items-start gap-4">
@if(!empty($product->cover))
<img src="{{ asset('storage/'.$product->cover) }}" class="w-40 h-28 object-cover rounded-xl border" alt="">
@endif
<div class="flex-1 min-w-[220px]">
<div class="flex flex-wrap items-center gap-2">
<h2 class="font-bold text-lg">{{ $product->title }}</h2>
{!! statusBadge($product->status) !!}
<span class="text-xs text-gray-500">{{ $product->type }}{{ $product->category ? ' · '.$product->category : '' }}</span>
</div>
<p class="text-sm text-gray-500">Produtor: {{ $product->producer->name ?? '-' }} ({{ $product->producer->email ?? '-' }}) · {{ number_format($product->price, 2) }} {{ $product->currency ?? 'MZN' }} · Comissão {{ $product->commission_rate }}% · Afiliação {{ $product->affiliation_mode ?? 'automatic' }}</p>
<p class="text-sm text-gray-700 mt-2 whitespace-pre-line">{{ $product->description }}</p>
@if($product->external_url)<p class="text-xs mt-1">Entrega: <a href="{{ $product->external_url }}" target="_blank" class="text-blue-600">{{ $product->external_url }}</a></p>@endif
@if($product->sales_page_url)<p class="text-xs">Página de vendas: <a href="{{ $product->sales_page_url }}" target="_blank" class="text-blue-600">{{ $product->sales_page_url }}</a></p>@endif
@if($product->status === 'rejected' && $product->rejection_reason)<p class="text-xs text-red-600 mt-1">Motivo da rejeição: {{ $product->rejection_reason }}</p>@endif
@if($product->affiliation_rules)<p class="text-xs text-gray-500 mt-1">Regras de afiliação: {{ $product->affiliation_rules }}</p>@endif
</div>
</div>
<div class="flex flex-wrap gap-2 mt-4">
<form method="POST" action="{{ route('admin.products.approve', $product) }}">@csrf<button class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg">Aprovar</button></form>
<form method="POST" action="{{ route('admin.products.reject', $product) }}" class="flex gap-2">@csrf
<input name="reason" required placeholder="Motivo da rejeição…" class="border rounded-lg px-3 py-2 text-sm">
<button class="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg">Rejeitar</button></form>
<a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2 border text-sm rounded-lg">Editar</a>
<a href="{{ route('content.index', $product) }}" class="px-4 py-2 border text-sm rounded-lg">Ver conteúdo/curso</a>
<a href="{{ route('admin.products') }}" class="px-4 py-2 text-sm text-gray-500">← Voltar</a>
</div>
</div>

<div class="grid md:grid-cols-3 gap-4">
<div class="bg-white rounded-xl shadow p-4">
<h3 class="font-bold text-sm mb-2">Módulos ({{ $product->modules->count() }})</h3>
@forelse($product->modules as $m)
<div class="text-sm border-b py-1"><strong>{{ $m->title }}</strong> <span class="text-gray-400">({{ $m->lessons->count() }} aulas)</span></div>
@empty<p class="text-xs text-gray-400">Sem módulos.</p>
@endforelse
</div>
<div class="bg-white rounded-xl shadow p-4">
<h3 class="font-bold text-sm mb-2">Cupons ({{ $product->coupons->count() ?? 0 }})</h3>
@forelse(($product->coupons ?? []) as $c)
<div class="text-sm border-b py-1">{{ $c->code }} — {{ $c->percent }}%</div>
@empty<p class="text-xs text-gray-400">Sem cupons.</p>
@endforelse
<h3 class="font-bold text-sm mt-3 mb-2">Afiliados ({{ $product->affiliates->count() }})</h3>
@forelse($product->affiliates->take(5) as $a)
<div class="text-xs border-b py-1">{{ $a->user->name ?? '-' }} · {{ $a->affiliate_code }} · {!! statusBadge($a->status) !!}</div>
@empty<p class="text-xs text-gray-400">Sem afiliados.</p>
@endforelse
</div>
<div class="bg-white rounded-xl shadow p-4">
<h3 class="font-bold text-sm mb-2">Últimas vendas</h3>
@forelse($sales as $s)
<div class="text-xs border-b py-1">{{ $s->buyer_name }} — MZN {{ number_format($s->final_price, 2) }} — {!! statusBadge($s->status) !!}</div>
@empty<p class="text-xs text-gray-400">Sem vendas.</p>
@endforelse
</div>
</div>
@endsection
