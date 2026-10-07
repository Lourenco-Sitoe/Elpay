<x-app-layout>
@php
$icons = [
  'overview' => 'bi-eye', 'basic' => 'bi-file-earmark-text', 'pricing' => 'bi-tag',
  'partners' => 'bi-people', 'affiliates' => 'bi-person-plus', 'links' => 'bi-link-45deg',
  'support' => 'bi-chat-text', 'materials' => 'bi-paperclip',
];
$descs = [
  'overview' => 'Estado e progresso do produto',
  'basic' => 'Capa, nome, descrição e categoria',
  'pricing' => 'Preço, moeda e formas de pagamento',
  'partners' => 'Quem divide os ganhos consigo',
  'affiliates' => 'Comissões de quem divulga',
  'links' => 'Endereços para partilhar',
  'support' => 'Contacto de apoio ao cliente',
  'materials' => 'Ficheiros que o cliente recebe',
];
$payLabels = ['mpesa' => 'M-Pesa', 'emola' => 'e-Mola', 'visa' => 'Debit Card'];
$statusLabels = \App\Http\Controllers\ProductWizardController::STATUS_LABELS;
$statusLabel = $statusLabels[$product->status ?? 'draft'] ?? ($product->status ?? 'Rascunho');
$priceMin = \App\Http\Controllers\ProductWizardController::PRICE_MIN[$product->currency ?? 'MZN'] ?? 1;
@endphp
<div class="max-w-[1400px] mx-auto px-1 sm:px-0">
<div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-start gap-3 mb-5">
  <div class="min-w-0">
    <h1 class="t-page-title break-words">{{ $product->title }} <span class="ml-1 sm:ml-2 align-middle text-xs font-medium px-2.5 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $statusLabel }}</span></h1>
    <p class="t-page-sub">Gestão de parâmetros e integração de vendas</p>
  </div>
  <a href="{{ route('products.index') }}" class="sm:ml-auto self-start px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-full text-sm hover:bg-slate-50 dark:hover:bg-slate-800 whitespace-nowrap"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

@if(session('success'))
<div class="fixed top-20 left-4 right-4 sm:left-auto sm:right-5 sm:w-80 z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700" x-data="{ show:true }" x-show="show">
  <div class="flex items-center gap-2 px-4 py-3 bg-blue-600 text-white font-bold"><i class="bi bi-check-circle-fill"></i> Sucesso
  <button @click="show=false" class="ml-auto w-7 h-7 shrink-0 rounded-full bg-white text-slate-600 text-sm">✕</button></div>
  <p class="px-4 py-3 text-sm">{{ session('success') }}</p>
</div>
@endif

<div class="grid lg:grid-cols-[320px_1fr] gap-4 lg:gap-5 items-start">
  {{-- Coluna progresso + navegação --}}
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 sm:p-5">
    <p class="text-xs tracking-wide text-slate-500 font-medium">PROGRESSO</p>
    <div class="flex items-end justify-between mt-1">
      <span class="text-2xl font-extrabold">{{ $progress }}%</span>
      <span class="text-xs text-slate-500">{{ $doneCount }} de {{ $totalCount }} secções</span>
    </div>
    <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-700 mt-2 overflow-hidden">
      <div class="h-full bg-blue-600 rounded-full" style="width:{{ $progress }}%"></div>
    </div>
    <nav class="mt-4 flex lg:block gap-2 lg:gap-0 lg:space-y-1 overflow-x-auto lg:overflow-visible pb-1 lg:pb-0 -mx-1 px-1 lg:mx-0 lg:px-0">
      @foreach($sections as $key => $label)
      <a href="{{ route('products.wizard', [$product, $key]) }}"
         class="flex items-center gap-2.5 sm:gap-3 px-3 py-2 sm:py-2.5 rounded-xl text-sm sm:text-[15px] font-medium shrink-0 lg:shrink {{ $section === $key ? 'bg-blue-600 text-white shadow-[0_2px_8px_rgba(37,99,235,.35)]' : 'bg-slate-50 lg:bg-transparent dark:bg-slate-700/50 lg:dark:bg-transparent text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
        <span class="w-8 h-8 sm:w-9 sm:h-9 shrink-0 rounded-full flex items-center justify-center {{ $section === $key ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">
          <i class="bi {{ $icons[$key] }}"></i>
        </span>
        <span class="whitespace-nowrap">{{ $label }}</span>
        <span class="ml-1 sm:ml-auto">
          @if(!empty($done[$key]))<i class="bi bi-check-circle-fill {{ $section === $key ? 'text-white' : 'text-blue-600' }}"></i>
          @else<i class="bi bi-circle {{ $section === $key ? 'text-white/70' : 'text-slate-300' }}"></i>@endif
        </span>
      </a>
      @endforeach
    </nav>
  </div>

  {{-- Conteúdo da secção --}}
  <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-4 sm:p-6 md:p-8 lg:min-h-[480px] min-w-0">

  @if($section === 'overview')
    <h2 class="font-extrabold text-lg sm:text-xl">Secções do produto</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Complete as secções obrigatórias para poder enviar o produto a avaliação.</p>
    @if(in_array($product->status, ['draft', 'rejected']))
    <form method="POST" action="{{ route('products.submit', $product) }}" class="mt-4">
      @csrf
      @if($product->status === 'rejected' && $product->rejection_reason)
      <p class="text-sm text-red-600 mb-2">Recusado: {{ $product->rejection_reason }}</p>
      @endif
      <button class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm"><i class="bi bi-send"></i> Enviar para avaliação</button>
    </form>
    @elseif($product->status === 'pending')
    <p class="mt-4 text-sm px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900 text-blue-700 dark:text-blue-300">Em análise pelo admin. Serás notificado da decisão.</p>
    @endif
    <div class="grid gap-2.5 sm:gap-3 mt-4 sm:mt-5">
      @foreach($sections as $key => $label)
      @php $optional = in_array($key, ['partners', 'affiliates']) ? ' <span class="font-normal text-slate-400">(opcional)</span>' : ''; @endphp
      <a href="{{ route('products.wizard', [$product, $key]) }}"
         class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-xl border {{ !empty($done[$key]) ? 'bg-blue-50/60 dark:bg-blue-900/20 border-blue-100 dark:border-blue-900' : 'bg-slate-50/60 dark:bg-slate-700/30 border-slate-200 dark:border-slate-700 hover:border-blue-300' }}">
        <span class="w-6 h-6 shrink-0 rounded-full flex items-center justify-center {{ !empty($done[$key]) ? 'bg-blue-600 text-white' : 'border-2 border-slate-300 dark:border-slate-600 text-transparent' }}">
          @if(!empty($done[$key]))<i class="bi bi-check text-sm"></i>@endif
        </span>
        <span class="w-9 h-9 sm:w-10 sm:h-10 shrink-0 rounded-lg flex items-center justify-center {{ !empty($done[$key]) ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">
          <i class="bi {{ $icons[$key] }}"></i>
        </span>
        <span class="min-w-0"><span class="block font-bold text-sm sm:text-base truncate">{!! $label . $optional !!}</span><span class="block text-xs sm:text-sm text-slate-500 truncate">{{ $descs[$key] }}</span></span>
        <i class="bi bi-arrow-right ml-auto shrink-0 text-slate-400"></i>
      </a>
      @endforeach
    </div>

  @elseif($section === 'basic')
    <h2 class="font-extrabold text-lg sm:text-xl">Identidade do produto</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">A capa, o nome e a forma como o produto é entregue.</p>
    <form method="POST" action="{{ route('products.wizard.update', [$product, 'basic']) }}" enctype="multipart/form-data" class="mt-4 sm:mt-5">
      @csrf @method('PUT')
      <div class="grid sm:grid-cols-[200px_1fr] lg:grid-cols-[220px_1fr] gap-5 sm:gap-6">
        <div class="max-w-[280px] w-full mx-auto sm:mx-0">
          <p class="font-medium mb-2">Capa</p>
          @if(!empty($product->cover))
          <img src="{{ asset('storage/'.$product->cover) }}" class="w-full aspect-[4/3] object-cover rounded-2xl border" alt="">
          @else
          <div class="w-full aspect-[4/3] rounded-2xl border bg-slate-50 dark:bg-slate-700/50 flex flex-col items-center justify-center text-slate-300"><i class="bi bi-box text-5xl sm:text-6xl text-rose-500"></i><span class="text-xs mt-2 text-slate-400">Foto do Produto</span></div>
          @endif
          <label class="mt-3 flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-700 text-sm font-medium cursor-pointer hover:bg-slate-200">
            <i class="bi bi-camera"></i> Alterar capa
            <input type="file" name="cover" accept="image/*" class="hidden">
          </label>
        </div>
        <div class="grid gap-4 content-start">
          <div><label class="font-medium">Nome do produto <span class="text-red-500">*</span></label>
          <input name="title" required value="{{ old('title', $product->title) }}" class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
          <div><label class="font-medium">Descrição <span class="text-red-500">*</span></label>
          <textarea name="description" rows="5" class="mt-1 w-full border rounded-lg px-3 py-2.5">{{ old('description', $product->description) }}</textarea></div>
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4 mt-4">
        <div><label class="font-medium">Categoria <span class="text-red-500">*</span></label>
        <select name="category" required class="mt-1 w-full border rounded-lg px-3 py-2.5">
          @foreach($categories as $c)<option value="{{ $c }}" @selected(old('category', $product->category) == $c)>{{ $c }}</option>@endforeach
        </select></div>
        <div><label class="font-medium">Formato <span class="text-red-500">*</span></label>
        <select name="type" required class="mt-1 w-full border rounded-lg px-3 py-2.5">
          @foreach($formats as $v => $l)<option value="{{ $v }}" @selected(old('type', $product->type) == $v)>{{ $l }}</option>@endforeach
        </select></div>
      </div>
      <p class="font-medium mt-6">Tipo de entrega</p>
      <div class="grid sm:grid-cols-2 gap-3 mt-2" x-data="{ delivery:'{{ old('delivery', $product->delivery ?? 'internal') }}' }">
        <label class="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl border-2 cursor-pointer min-w-0" :class="delivery === 'internal' ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'">
          <input type="radio" name="delivery" value="internal" x-model="delivery" class="hidden">
          <span class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg"><i class="bi bi-mortarboard"></i></span>
          <span class="min-w-0"><span class="block font-bold text-sm sm:text-base">Entrega interna</span><span class="block text-xs sm:text-sm text-slate-500">Conteúdo na área de membros</span></span>
          <i class="bi bi-check-circle-fill ml-auto shrink-0 text-blue-600 text-xl" x-show="delivery === 'internal'"></i>
        </label>
        <label class="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl border-2 cursor-pointer min-w-0" :class="delivery === 'external' ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700'">
          <input type="radio" name="delivery" value="external" x-model="delivery" class="hidden">
          <span class="w-10 h-10 sm:w-11 sm:h-11 shrink-0 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 flex items-center justify-center text-lg"><i class="bi bi-box-arrow-up-right"></i></span>
          <span class="min-w-0"><span class="block font-bold text-sm sm:text-base">Entrega externa</span><span class="block text-xs sm:text-sm text-slate-500">Link externo depois do pagamento</span></span>
          <i class="bi bi-circle ml-auto shrink-0 text-slate-300 text-xl" x-show="delivery !== 'external'"></i>
          <i class="bi bi-check-circle-fill ml-auto shrink-0 text-blue-600 text-xl" x-show="delivery === 'external'"></i>
        </label>
        <div x-show="delivery === 'external'" class="sm:col-span-2">
          <input name="external_url" value="{{ old('external_url', $product->external_url) }}" placeholder="https://…" class="w-full border rounded-lg px-3 py-2.5">
        </div>
      </div>
      <div class="flex mt-6"><button class="w-full sm:w-auto sm:ml-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm text-center"><i class="bi bi-check-lg"></i> Guardar informações</button></div>
    </form>

  @elseif($section === 'pricing')
    <p class="text-slate-500 text-sm sm:text-[15px]">A moeda, o preço e as formas de pagamento deste produto.</p>
    <form method="POST" action="{{ route('products.wizard.update', [$product, 'pricing']) }}" class="mt-4">
      @csrf @method('PUT')
      <h3 class="font-extrabold text-base sm:text-lg">Moeda e preço</h3>
      <p class="text-xs sm:text-sm text-slate-500">Cada produto é vendido numa única moeda. Para vender noutra, crie uma cópia do produto nessa moeda.</p>
      <div class="grid sm:grid-cols-2 gap-4 mt-3">
        <div><label class="font-medium">Moeda <span class="text-red-500">*</span></label>
        <select name="currency" class="mt-1 w-full border rounded-lg px-3 py-2.5"><option value="MZN" @selected(old('currency', $product->currency ?? 'MZN') == 'MZN')>MZN — Metical</option><option value="USD" @selected(old('currency', $product->currency) == 'USD')>USD — Dólar</option></select></div>
        <div class="min-w-0"><label class="font-medium">Preço de venda <span class="text-red-500">*</span></label>
        <div class="mt-1 flex min-w-0"><span class="px-3 py-2.5 border border-r-0 rounded-l-lg bg-slate-100 dark:bg-slate-700 text-sm font-bold text-slate-500 shrink-0">{{ $product->currency ?? 'MZN' }}</span>
        <input type="number" step="0.01" min="1" name="price" required value="{{ old('price', $product->price) }}" class="w-full min-w-0 border rounded-r-lg px-3 py-2.5"></div>
        <p class="text-xs text-slate-500 mt-1">O mínimo nesta moeda é {{ ($product->currency ?? 'MZN') }} {{ number_format($priceMin, 2) }}.</p></div>
      </div>
      <h3 class="font-extrabold text-base sm:text-lg mt-6 sm:mt-8">Garantia</h3>
      <p class="text-xs sm:text-sm text-slate-500">Durante quantos dias o cliente pode pedir o reembolso depois de comprar.</p>
      <div class="mt-2 sm:max-w-sm"><label class="font-medium">Tempo de garantia <span class="text-red-500">*</span></label>
      <select name="warranty_days" class="mt-1 w-full border rounded-lg px-3 py-2.5">
        @foreach([0 => 'Sem garantia', 7 => '7 dias', 15 => '15 dias', 30 => '30 dias'] as $v => $l)<option value="{{ $v }}" @selected(old('warranty_days', $product->warranty_days ?? 7) == $v)>{{ $l }}</option>@endforeach
      </select></div>
      <h3 class="font-extrabold text-base sm:text-lg mt-6 sm:mt-8">Formas de pagamento</h3>
      <p class="text-xs sm:text-sm text-slate-500">As que estão disponíveis para este produto em {{ $product->currency ?? 'MZN' }}.</p>
      @php $pm = old('payment_methods', $product->payment_methods ?? ['mpesa', 'emola']); @endphp
      <div class="grid sm:grid-cols-2 gap-3 mt-3">
        <label class="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-blue-400 min-w-0">
          <span class="w-10 h-10 shrink-0 rounded-lg bg-[#eb1c24] text-white flex items-center justify-center font-extrabold text-xs">M</span>
          <span class="min-w-0"><span class="block font-bold text-sm sm:text-base">M-Pesa</span><span class="block text-xs sm:text-sm text-slate-500">Pagamento via M-Pesa</span></span>
          <input type="checkbox" name="payment_methods[]" value="mpesa" @checked(in_array('mpesa', (array) $pm)) class="ml-auto shrink-0 w-5 h-5 accent-blue-600">
        </label>
        <label class="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-blue-400 min-w-0">
          <span class="w-10 h-10 shrink-0 rounded-lg bg-[#f39200] text-white flex items-center justify-center font-extrabold text-xs">eM</span>
          <span class="min-w-0"><span class="block font-bold text-sm sm:text-base">e-Mola</span><span class="block text-xs sm:text-sm text-slate-500">Pagamento via e-Mola</span></span>
          <input type="checkbox" name="payment_methods[]" value="emola" @checked(in_array('emola', (array) $pm)) class="ml-auto shrink-0 w-5 h-5 accent-blue-600">
        </label>
      </div>
      <div class="flex mt-6"><button class="w-full sm:w-auto sm:ml-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm text-center"><i class="bi bi-check-lg"></i> Guardar preço e moeda</button></div>
    </form>

  @elseif($section === 'partners')
    <h2 class="font-extrabold text-lg sm:text-xl">Parceiros (coprodução) <span class="text-xs sm:text-sm font-normal text-slate-400">(opcional)</span></h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Quem divide os ganhos consigo. O total das participações não pode passar 90%.</p>
    <div class="mt-4">
      <a href="{{ route('partners.index', $product) }}" class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm"><i class="bi bi-people"></i> Gerir coprodução</a>
    </div>
    <div class="mt-4 space-y-2">
      @forelse(($product->partners ?? []) as $pp)
      <div class="flex flex-wrap items-center gap-2 sm:gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-700 text-sm">
        <span class="font-bold break-words">{{ $pp->user->name ?? $pp->email ?? '-' }}</span>
        <span class="text-slate-500">{{ $pp->share_percent }}%</span>
        <span class="ml-auto">{!! statusBadge($pp->status) !!}</span>
      </div>
      @empty<p class="text-sm text-slate-400">Sem parceiros. Pode avançar — esta secção é opcional.</p>
      @endforelse
    </div>

  @elseif($section === 'affiliates')
    <h2 class="font-extrabold text-lg sm:text-xl">Programa de afiliados</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Deixe outras pessoas divulgarem o seu produto e defina quanto recebem por cada venda.</p>
    <form method="POST" action="{{ route('products.wizard.update', [$product, 'affiliates']) }}" class="mt-4" x-data="{ enabled: {{ (float) ($product->commission_rate ?? 10) > 0 ? 'true' : 'false' }} }">
      @csrf @method('PUT')
      <div class="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700">
        <div class="min-w-0"><p class="font-bold text-sm sm:text-base">{{ (float) ($product->commission_rate ?? 10) > 0 ? 'Afiliados permitidos' : 'Afiliados não permitidos' }}</p>
        <p class="text-xs sm:text-sm text-slate-500">Ligue para deixar outras pessoas divulgarem o seu produto e receberem comissão.</p></div>
        <button type="button" @click="enabled = !enabled" class="ml-auto shrink-0 w-14 h-8 rounded-full transition" :class="enabled ? 'bg-blue-600' : 'bg-slate-300'"><span class="block w-6 h-6 bg-white rounded-full shadow transition-all" :class="enabled ? 'ml-7' : 'ml-1'"></span></button>
        <input type="hidden" name="affiliates_enabled" :value="enabled ? 1 : 0">
      </div>
      <div x-show="enabled" class="grid gap-4 mt-4">
        <div class="grid sm:grid-cols-2 gap-4">
          <div><label class="font-medium">Comissão do afiliado (%)</label>
          <input type="number" step="0.1" min="0" max="90" name="commission_rate" value="{{ old('commission_rate', $product->commission_rate ?? 10) }}" class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
          <div><label class="font-medium">Aprovação</label>
          <select name="affiliation_mode" class="mt-1 w-full border rounded-lg px-3 py-2.5">
            <option value="automatic" @selected(old('affiliation_mode', $product->affiliation_mode ?? 'automatic') == 'automatic')>Automática</option>
            <option value="manual" @selected(old('affiliation_mode', $product->affiliation_mode) == 'manual')>Com aprovação</option>
          </select></div>
        </div>
        <div><label class="font-medium">Regras de afiliação</label>
        <textarea name="affiliation_rules" rows="3" class="mt-1 w-full border rounded-lg px-3 py-2.5">{{ old('affiliation_rules', $product->affiliation_rules) }}</textarea></div>
      </div>
      <div class="flex mt-6"><button class="w-full sm:w-auto sm:ml-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm text-center"><i class="bi bi-check-lg"></i> Guardar programa de afiliados</button></div>
    </form>

  @elseif($section === 'links')
    <h2 class="font-extrabold text-lg sm:text-xl">Links de divulgação</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Endereços para partilhar o seu produto.</p>
    @php
      $checkout = route('checkout.show', $product->slug);
      $pub = route('product.public', $product->slug);
    @endphp
    <div class="grid gap-3 mt-4">
      <div class="p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 min-w-0">
        <p class="font-bold text-sm">Checkout (página de compra)</p>
        <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2"><code class="flex-1 min-w-0 text-xs bg-slate-50 dark:bg-slate-700/50 rounded-lg px-3 py-2 break-all">{{ $checkout }}</code>
        <button onclick="navigator.clipboard.writeText('{{ $checkout }}')" class="shrink-0 px-3 py-2 bg-blue-600 text-white text-xs rounded-lg">Copiar</button></div>
      </div>
      <div class="p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 min-w-0">
        <p class="font-bold text-sm">Página do produto</p>
        <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-2"><code class="flex-1 min-w-0 text-xs bg-slate-50 dark:bg-slate-700/50 rounded-lg px-3 py-2 break-all">{{ $pub }}</code>
        <button onclick="navigator.clipboard.writeText('{{ $pub }}')" class="shrink-0 px-3 py-2 bg-blue-600 text-white text-xs rounded-lg">Copiar</button></div>
      </div>
      <div class="p-3.5 sm:p-4 rounded-xl border border-slate-200 dark:border-slate-700 min-w-0">
        <p class="font-bold text-sm">Exemplo de link de afiliado</p>
        <code class="block mt-2 text-xs bg-slate-50 dark:bg-slate-700/50 rounded-lg px-3 py-2 break-all">{{ $checkout }}?ref=CODIGO-DO-AFILIADO</code>
        <p class="text-xs text-slate-500 mt-1">Os seus afiliados encontram o link real em Meus Links. Gerencie-os em <a href="{{ route('producer.affiliates') }}" class="text-blue-600 underline">Afiliados</a>.</p>
      </div>
    </div>

  @elseif($section === 'support')
    <h2 class="font-extrabold text-lg sm:text-xl">Suporte e tracking</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Nome e email de contacto ao cliente. O tracking de anúncios configura-se em Ferramentas → Pixels.</p>
    <form method="POST" action="{{ route('products.wizard.update', [$product, 'support']) }}" class="grid sm:grid-cols-2 gap-4 mt-4">
      @csrf @method('PUT')
      <div><label class="font-medium">Email de suporte</label>
      <input type="email" name="support_email" value="{{ old('support_email', $product->support_email) }}" placeholder="apoio@exemplo.co.mz" class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
      <div><label class="font-medium">Telefone/WhatsApp</label>
      <input name="support_phone" value="{{ old('support_phone', $product->support_phone) }}" placeholder="84… / 85…" class="mt-1 w-full border rounded-lg px-3 py-2.5"></div>
      <div class="sm:col-span-2 flex"><button class="w-full sm:w-auto sm:ml-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm text-center"><i class="bi bi-check-lg"></i> Guardar suporte</button></div>
    </form>
    <p class="text-sm text-slate-500 mt-4">Pixels e webhooks: <a href="{{ route('tools.index') }}" class="text-blue-600 underline">abrir Ferramentas</a>.</p>

  @elseif($section === 'materials')
    <h2 class="font-extrabold text-lg sm:text-xl">Materiais para entrega</h2>
    <p class="text-slate-500 text-sm sm:text-[15px] mt-1">Os ficheiros que o cliente recebe depois de pagar.</p>
    <form method="POST" action="{{ route('products.wizard.update', [$product, 'materials']) }}" enctype="multipart/form-data" class="mt-4 p-4 sm:p-6 rounded-2xl bg-slate-50 dark:bg-slate-700/40 border border-slate-200 dark:border-slate-700">
      @csrf @method('PUT')
      <p class="font-bold flex items-center gap-2 text-sm sm:text-base"><span class="w-9 h-9 shrink-0 rounded-full bg-blue-600 text-white flex items-center justify-center"><i class="bi bi-cloud-arrow-up"></i></span> Carregar novo material</p>
      <div class="mt-3"><label class="font-medium text-sm">Nome do material <span class="text-red-500">*</span></label>
      <input name="material_name" placeholder="Ex.: Guia do utilizador" class="mt-1 w-full border rounded-lg px-3 py-2.5 bg-white dark:bg-slate-800"></div>
      <div class="mt-3"><label class="font-medium text-sm">Ficheiro <span class="text-red-500">*</span></label>
      <label class="mt-1 flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl py-6 sm:py-8 px-4 cursor-pointer text-slate-500 text-center">
        <i class="bi bi-cloud-arrow-up text-2xl sm:text-3xl"></i>
        <span class="text-xs sm:text-sm"><span class="text-blue-600 font-semibold">Escolher ficheiro</span> ou arraste para aqui</span>
        <span class="text-[11px] sm:text-xs">JPG, JPEG, PNG, PDF, DOC, DOCX, XLSX, XLS, CSV · até 500 MB</span>
        <input type="file" name="file" class="hidden">
      </label></div>
      <button class="mt-4 w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full text-sm opacity-90"><i class="bi bi-upload"></i> Carregar material</button>
    </form>
    @if(!empty($product->file_path))
    <p class="text-sm mt-3 break-words">Ficheiro atual: <a href="{{ asset('storage/'.$product->file_path) }}" target="_blank" class="text-blue-600 underline">{{ basename($product->file_path) }}</a></p>
    @else
    <div class="mt-4 p-4 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-900 text-sm text-yellow-800 dark:text-yellow-200"><i class="bi bi-exclamation-triangle-fill"></i> Ainda não carregou nenhum material. Sem ficheiros, o cliente paga e não recebe nada.</div>
    @endif
    <p class="text-sm text-slate-500 mt-4">Cursos: gira módulos e aulas em <a href="{{ route('content.index', $product) }}" class="text-blue-600 underline">Conteúdo do curso</a>.</p>
  @endif

  </div>
</div>
</div>
</x-app-layout>
