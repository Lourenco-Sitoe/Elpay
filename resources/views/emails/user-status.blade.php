<h2>Estado da conta: {{ $status ?? '' }}</h2>
<p>Olá {{ $user->name ?? '' }}, o estado da tua conta é agora <b>{{ $status ?? '' }}</b>.</p>
@if(!empty($reason))<p>Motivo: {{ $reason }}</p>@endif
<p>Equipa Elpay</p>
