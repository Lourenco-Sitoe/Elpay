@php
$menu = [
    ['items' => [['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard']]],
    ['title' => 'Gestão', 'items' => [
        ['label' => 'Utilizadores', 'icon' => 'bi-people', 'route' => 'admin.users', 'match' => 'admin.users'],
        ['label' => 'Produtos', 'icon' => 'bi-box', 'route' => 'admin.products', 'match' => 'admin.products'],
        ['label' => 'Cursos', 'icon' => 'bi-mortarboard', 'route' => 'admin.courses', 'match' => 'admin.courses'],
        ['label' => 'Vendas', 'icon' => 'bi-cart', 'route' => 'admin.sales', 'match' => 'admin.sales'],
        ['label' => 'Pagamentos', 'icon' => 'bi-credit-card', 'route' => 'admin.payments', 'match' => 'admin.payments'],
        ['label' => 'Comissões', 'icon' => 'bi-percent', 'route' => 'admin.commissions', 'match' => 'admin.commissions'],
        ['label' => 'Afiliados', 'icon' => 'bi-diagram-3', 'route' => 'admin.affiliates', 'match' => 'admin.affiliates'],
        ['label' => 'Saques', 'icon' => 'bi-wallet2', 'route' => 'admin.withdrawals', 'match' => 'admin.withdrawals'],
        ['label' => 'Verificação KYC', 'icon' => 'bi-shield-check', 'route' => 'admin.kyc', 'match' => 'admin.kyc'],
    ]],
    ['title' => 'Sistema', 'items' => [
        ['label' => 'Banners', 'icon' => 'bi-image', 'route' => 'admin.banners', 'match' => 'admin.banners'],
        ['label' => 'Relatórios', 'icon' => 'bi-bar-chart', 'route' => 'admin.reports', 'match' => 'admin.reports'],
        ['label' => 'Comunicação', 'icon' => 'bi-megaphone', 'route' => 'admin.communication', 'match' => 'admin.communication'],
        ['label' => 'Mensagens', 'icon' => 'bi-chat', 'route' => 'admin.messages', 'match' => 'admin.messages'],
        ['label' => 'Logs', 'icon' => 'bi-journal-text', 'route' => 'admin.logs', 'match' => 'admin.logs'],
        ['label' => 'Configurações', 'icon' => 'bi-gear', 'route' => 'admin.settings', 'match' => 'admin.settings'],
        ['label' => 'Ver site', 'icon' => 'bi-globe', 'route' => 'home'],
    ]],
];
$title = 'Administração';
@endphp
@include('layouts.shell', ['menu' => $menu, 'title' => $title])
