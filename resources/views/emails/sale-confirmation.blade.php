<h2>Compra confirmada #{{ $sale->id ?? '' }}</h2>
<p>Olá {{ $user->name ?? $sale->email ?? '' }},</p>
<p>A tua compra de <b>{{ $sale->product->title ?? '' }}</b> ({{ number_format($sale->final_price ?? 0,2) }} MT) foi confirmada.</p>
<p>Acede aos teus produtos no painel Elpay.</p>
<p>Equipa Elpay</p>
