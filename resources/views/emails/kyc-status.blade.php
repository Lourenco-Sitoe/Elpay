<h2>Estado da verificação KYC: {{ $status ?? 'atualizado' }}</h2>
<p>Olá {{ $user->name ?? '' }},</p>
<p>A tua verificação KYC foi <b>{{ $status ?? '' }}</b>.</p>
@if(!empty($reason))<p>Motivo: {{ $reason }}</p>@endif
<p>Equipa Elpay</p>
