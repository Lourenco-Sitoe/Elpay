<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Links de Produtos</h2></x-slot>
<div class="max-w-[1400px] mx-auto">
@include('layouts.partials.flash')
<h1 class="t-page-title">Links de Produtos</h1>
<p class="t-page-sub mb-5">Copie os links para compartilhar os seus produtos.</p>

<div class="t-card overflow-hidden">
  <div class="overflow-x-auto">
    <table class="t-table">
      <thead><tr><th>Produto</th><th>Link</th><th style="width:130px"></th></tr></thead>
      <tbody>
      @forelse(($links ?? $products ?? []) as $l)
        @php $url = url('/checkout/' . ($l->product->slug ?? $l->product->id ?? $l->id) . '?ref=' . ($l->affiliate_code ?? '')); @endphp
        <tr>
          <td class="font-medium">{{ $l->product->title ?? $l->title ?? '-' }}</td>
          <td style="min-width:280px"><div class="t-linkbox">{{ $url }}</div></td>
          <td><button onclick="navigator.clipboard.writeText('{{ $url }}');this.innerHTML='<i class=&quot;bi bi-check&quot;></i> Copiado!'" class="t-btn t-btn-sm"><i class="bi bi-clipboard"></i> Copiar</button></td>
        </tr>
      @empty
        <tr><td colspan="3" class="py-10 text-center text-slate-500">Sem links. Vai ao <a class="underline text-blue-700 font-semibold" href="{{ Route::has('affiliate.marketplace') ? route('affiliate.marketplace') : '#' }}">marketplace</a>.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>
</x-app-layout>
