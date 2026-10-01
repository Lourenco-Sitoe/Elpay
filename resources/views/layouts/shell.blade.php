<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Painel') . ' · Elpay' }}</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2563eb">
    <link rel="apple-touch-icon" href="/pwa-icons/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Elpay">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>html{background-color:#f1f5f9}html.dark{background-color:#0f172a}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="font-sans antialiased bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100" x-data="{ sidebar:false }">
<div class="min-h-screen lg:flex">

    {{-- Sidebar --}}
    <div x-show="sidebar" x-transition.opacity class="fixed inset-0 bg-black/40 z-30 lg:hidden" @click="sidebar=false"></div>
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full'"
        class="fixed lg:static z-40 w-64 shrink-0 min-h-screen bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col transition-transform lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 h-16 font-extrabold text-xl text-blue-600">
            <x-logo class="h-9 w-9" /> Elpay
        </a>
        <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-1 text-sm">
            @foreach(($menu ?? []) as $section)
                @if(!empty($section['title']))
                    <p class="px-3 pt-4 pb-1 text-[11px] uppercase tracking-wider text-slate-400">{{ $section['title'] }}</p>
                @endif
                @foreach(($section['items'] ?? []) as $item)
                    @php $active = isset($item['route']) && Route::has($item['route']) && request()->routeIs(($item['match'] ?? $item['route']) . '*'); @endphp
                    <a href="{{ isset($item['route']) && Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#' }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium {{ $active ? 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                        <i class="bi {{ $item['icon'] ?? 'bi-circle' }} text-base"></i> {{ $item['label'] }}
                        @if(!empty($item['badge']))<span class="ml-auto text-[11px] px-2 py-0.5 rounded-full bg-blue-600 text-white">{{ $item['badge'] }}</span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="p-3 border-t border-slate-200 dark:border-slate-700 text-xs text-slate-400">Elpay · MZN · v1.1</div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-20 h-16 bg-white/90 dark:bg-slate-800/90 backdrop-blur border-b border-slate-200 dark:border-slate-700 flex items-center gap-2 px-4">
            <button class="lg:hidden p-2 -ml-2 text-slate-500" @click="sidebar=true"><i class="bi bi-list text-xl"></i></button>
            <h1 class="font-bold text-lg truncate">{{ $title ?? 'Painel' }}</h1>
            <div class="ml-auto flex items-center gap-1">
                <button onclick="window.ElpayTheme.toggle()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700" title="Tema claro/escuro">
                    <span class="dark:hidden"><i class="bi bi-moon text-lg"></i></span>
                    <span class="hidden dark:inline"><i class="bi bi-sun text-lg"></i></span>
                </button>
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 relative">
                        <i class="bi bi-bell text-lg"></i>
                        @if(($unreadCount ?? 0) > 0)<span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 text-[10px] leading-[18px] text-center rounded-full bg-red-500 text-white">{{ $unreadCount }}</span>@endif
                    </button>
                    <div x-show="open" @click.outside="open=false" class="dropdown-panel absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-sm">Notificações ({{ $unreadCount ?? 0 }})
                            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="text-blue-600 float-right font-normal">Ver todas</a></div>
                        @forelse(($unreadNotifications ?? collect())->take(5) as $n)
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 text-sm"><p class="font-medium">{{ $n->title }}</p><p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($n->message, 80) }}</p></div>
                        @empty<div class="p-4 text-sm text-slate-400 text-center">Sem notificações.</div>@endforelse
                    </div>
                </div>
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">{{ mb_substr(auth()->user()->name ?? 'E', 0, 1) }}</span>
                        <i class="bi bi-chevron-down text-xs text-slate-400"></i>
                    </button>
                    <div x-show="open" @click.outside="open=false" class="dropdown-panel absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden text-sm">
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700"><p class="font-semibold truncate">{{ auth()->user()->name }}</p><p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p></div>
                        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700">Sair</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6 w-full max-w-7xl mx-auto">
            @include('layouts.partials.flash')
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
