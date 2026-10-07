@php
$type = auth()->user()->type ?? 'client';
$menu = [['items' => [['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'dashboard']]]];
if ($type === 'producer') {
    $menu[] = ['items' => [
        ['label' => 'Produtos', 'icon' => 'bi-box', 'children' => [
            ['label' => 'Meus produtos', 'route' => 'products.index', 'match' => 'products'],
            ['label' => 'Cupons', 'route' => 'coupons.index', 'match' => 'coupons'],
            ['label' => 'Ferramentas', 'route' => 'tools.index', 'match' => 'tools'],
        ]],
        ['label' => 'Vendas', 'icon' => 'bi-cart', 'children' => [
            ['label' => 'Minhas vendas', 'route' => 'producer.sales', 'match' => 'producer.sales'],
            ['label' => 'Clientes', 'route' => 'producer.customers', 'match' => 'producer.customers'],
            ['label' => 'Ganhos de parceria', 'route' => 'partners.earnings', 'match' => 'partners'],
        ]],
        ['label' => 'Afiliados', 'icon' => 'bi-diagram-3', 'children' => [
            ['label' => 'Gerir afiliados', 'route' => 'producer.affiliates', 'match' => 'producer.affiliates'],
            ['label' => 'Marketplace', 'route' => 'affiliate.marketplace', 'match' => 'affiliate.marketplace'],
            ['label' => 'Meus links', 'route' => 'affiliate.links', 'match' => 'affiliate.links'],
        ]],
        ['label' => 'Carteira', 'icon' => 'bi-wallet2', 'children' => [
            ['label' => 'A minha carteira', 'route' => 'wallet.index', 'match' => 'wallet'],
            ['label' => 'Contas de saque', 'route' => 'payouts.accounts', 'match' => 'payouts'],
        ]],
    ]];
} elseif ($type === 'affiliate') {
    $menu[] = ['items' => [
        ['label' => 'Afiliação', 'icon' => 'bi-shop', 'children' => [
            ['label' => 'Marketplace', 'route' => 'affiliate.marketplace', 'match' => 'affiliate.marketplace'],
            ['label' => 'Meus links', 'route' => 'affiliate.links', 'match' => 'affiliate.links'],
            ['label' => 'Materiais', 'route' => 'affiliate.materials', 'match' => 'affiliate.materials'],
            ['label' => 'Comissões', 'route' => 'affiliate.commissions', 'match' => 'affiliate.commissions'],
        ]],
        ['label' => 'Carteira', 'icon' => 'bi-wallet2', 'children' => [
            ['label' => 'A minha carteira', 'route' => 'wallet.index', 'match' => 'wallet'],
            ['label' => 'Contas de saque', 'route' => 'payouts.accounts', 'match' => 'payouts'],
        ]],
    ]];
} else {
    $menu[] = ['items' => [
        ['label' => 'Certificados', 'icon' => 'bi-award', 'route' => 'certificates.mine', 'match' => 'certificates'],
    ]];
}
$menu[] = ['items' => [
    ['label' => 'Verificação KYC', 'icon' => 'bi-shield-check', 'route' => 'kyc.index', 'match' => 'kyc'],
    ['label' => 'Notificações', 'icon' => 'bi-bell', 'route' => 'notifications.index', 'match' => 'notifications'],
    ['label' => 'Suporte', 'icon' => 'bi-chat', 'route' => 'support.index', 'match' => 'support'],
    ['label' => 'Perfil', 'icon' => 'bi-person', 'route' => 'profile.edit', 'match' => 'profile'],
]];
$title = trim(strip_tags($header ?? 'Painel'));
@endphp
@include('layouts.shell', ['menu' => $menu, 'title' => $title, 'slot' => $slot ?? ''])
