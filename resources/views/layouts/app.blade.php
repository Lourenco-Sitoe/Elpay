@php
$type = auth()->user()->type ?? 'client';
$menu = [['items' => [['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard']]]];
if ($type === 'producer') {
    $menu[] = ['title' => 'Vender', 'items' => [
        ['label' => 'Meus produtos', 'icon' => 'bi-box', 'route' => 'products.index', 'match' => 'products'],
        ['label' => 'Ferramentas', 'icon' => 'bi-tools', 'route' => 'tools.index', 'match' => 'tools'],
        ['label' => 'Cupons', 'icon' => 'bi-ticket', 'route' => 'coupons.index', 'match' => 'coupons'],
    ]];
    $menu[] = ['title' => 'Ganhos', 'items' => [
        ['label' => 'Carteira', 'icon' => 'bi-wallet2', 'route' => 'wallet.index', 'match' => 'wallet'],
        ['label' => 'Contas de saque', 'icon' => 'bi-bank', 'route' => 'payouts.accounts', 'match' => 'payouts'],
        ['label' => 'Ganhos de parceria', 'icon' => 'bi-people', 'route' => 'partners.earnings', 'match' => 'partners'],
        ['label' => 'Marketplace', 'icon' => 'bi-shop', 'route' => 'affiliate.marketplace', 'match' => 'affiliate'],
        ['label' => 'Meus links', 'icon' => 'bi-link-45deg', 'route' => 'affiliate.links'],
    ]];
} elseif ($type === 'affiliate') {
    $menu[] = ['title' => 'Afiliado', 'items' => [
        ['label' => 'Marketplace', 'icon' => 'bi-shop', 'route' => 'affiliate.marketplace', 'match' => 'affiliate'],
        ['label' => 'Meus links', 'icon' => 'bi-link-45deg', 'route' => 'affiliate.links'],
        ['label' => 'Comissões', 'icon' => 'bi-percent', 'route' => 'affiliate.commissions'],
        ['label' => 'Carteira', 'icon' => 'bi-wallet2', 'route' => 'wallet.index', 'match' => 'wallet'],
    ]];
} else {
    $menu[] = ['title' => 'Cliente', 'items' => [
        ['label' => 'Minhas compras', 'icon' => 'bi-bag', 'route' => 'client.dashboard'],
        ['label' => 'Certificados', 'icon' => 'bi-award', 'route' => 'certificates.mine', 'match' => 'certificates'],
    ]];
}
$menu[] = ['title' => 'Conta', 'items' => [
    ['label' => 'Verificação KYC', 'icon' => 'bi-shield-check', 'route' => 'kyc.index', 'match' => 'kyc'],
    ['label' => 'Notificações', 'icon' => 'bi-bell', 'route' => 'notifications.index', 'match' => 'notifications'],
    ['label' => 'Suporte', 'icon' => 'bi-chat', 'route' => 'support.index', 'match' => 'support'],
    ['label' => 'Perfil', 'icon' => 'bi-person', 'route' => 'profile.edit', 'match' => 'profile'],
]];
$title = trim(strip_tags($header ?? 'Painel'));
@endphp
@include('layouts.shell', ['menu' => $menu, 'title' => $title, 'slot' => $slot ?? ''])
