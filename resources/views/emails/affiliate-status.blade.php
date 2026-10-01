<h2>Estado de afiliado: {{ $status ?? '' }}</h2>
<p>Olá {{ $user->name ?? '' }}, o teu pedido de afiliado foi <b>{{ $status ?? '' }}</b>.</p>
@if(!empty($reason))<p>Motivo: {{ $reason }}</p>@endif
<p>Equipa Elpay</p>
