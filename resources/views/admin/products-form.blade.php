@extends('layouts.admin')
@section('content')
<div class="bg-white rounded-xl shadow p-6 max-w-3xl">
<h2 class="font-bold mb-1">Editar produto #{{ $product->id }}</h2>
<p class="text-sm text-gray-500 mb-4">Dono: {{ $product->producer->name ?? '-' }} ({{ $product->producer->email ?? '-' }}) — o Admin pode editar qualquer produto.</p>
@include('layouts.partials.flash')
<form method="POST" action="{{ route('admin.products.update', $product) }}">
@csrf @method('PUT')
<div class="grid gap-3">
<div><label class="text-sm font-medium">Título</label><input name="title" value="{{ old('title', $product->title) }}" required class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Descrição</label><textarea name="description" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('description', $product->description) }}</textarea></div>
<div class="grid sm:grid-cols-4 gap-3">
<div><label class="text-sm font-medium">Preço</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Moeda</label><input name="currency" value="{{ old('currency', $product->currency ?? 'MZN') }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Tipo</label><input name="type" value="{{ old('type', $product->type) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Categoria</label><input name="category" value="{{ old('category', $product->category) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
<div class="grid sm:grid-cols-3 gap-3">
<div><label class="text-sm font-medium">Comissão afiliado (%)</label><input type="number" step="0.1" min="0" max="90" name="commission_rate" value="{{ old('commission_rate', $product->commission_rate) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Afiliação</label>
<select name="affiliation_mode" class="mt-1 w-full border rounded-lg px-3 py-2">
<option value="automatic" @selected(old('affiliation_mode', $product->affiliation_mode ?? 'automatic')=='automatic')>Automática</option>
<option value="manual" @selected(old('affiliation_mode', $product->affiliation_mode ?? 'automatic')=='manual')>Com aprovação</option>
</select></div>
<div><label class="text-sm font-medium">Estado</label>
<select name="status" class="mt-1 w-full border rounded-lg px-3 py-2">
@foreach((\App\Http\Controllers\ProductWizardController::STATUS_LABELS ?? ['pending'=>'pending','approved'=>'approved','rejected'=>'rejected']) as $v=>$l)<option value="{{ $v }}" @selected(old('status', $product->status)==$v)>{{ $l }}</option>@endforeach
</select></div>
</div>
<div><label class="text-sm font-medium">Regras de afiliação</label><textarea name="affiliation_rules" rows="2" class="mt-1 w-full border rounded-lg px-3 py-2">{{ old('affiliation_rules', $product->affiliation_rules) }}</textarea></div>
<div class="grid sm:grid-cols-2 gap-3">
<div><label class="text-sm font-medium">URL externa / entrega</label><input name="external_url" value="{{ old('external_url', $product->external_url) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
<div><label class="text-sm font-medium">Página de vendas</label><input name="sales_page_url" value="{{ old('sales_page_url', $product->sales_page_url) }}" class="mt-1 w-full border rounded-lg px-3 py-2"></div>
</div>
</div>
<div class="mt-4 flex gap-2">
<button class="px-6 py-2 bg-blue-600 text-white rounded-lg">Guardar</button>
<a href="{{ route('admin.products') }}" class="px-6 py-2 border rounded-lg">Voltar</a>
</div>
</form>
</div>
@endsection
