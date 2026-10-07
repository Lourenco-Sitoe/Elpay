<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Painel') . ' · Elpay' }}</title>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#168a45">
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
        class="fixed lg:static z-40 w-72 shrink-0 min-h-screen bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col transition-transform lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-6 h-[72px] font-extrabold text-[22px] text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700">
            <x-logo class="h-9 w-9" /> Elpay
        </a>
        <nav class="flex-1 overflow-y-auto py-2 text-[15px]">
            @foreach(($menu ?? []) as $section)
                @if(!empty($section['title']))
                    <p class="px-6 pt-5 pb-2 text-[13px] font-medium text-slate-500 dark:text-slate-400">{{ $section['title'] }}</p>
                @endif
                @foreach(($section['items'] ?? []) as $item)
                    @if(!empty($item['children']))
                        @php
                            $childActive = false;
                            foreach (($item['children'] ?? []) as $ch) {
                                $cm = $ch['match'] ?? $ch['route'] ?? '__none__';
                                if (isset($ch['route']) && Route::has($ch['route']) && request()->routeIs($cm . '*')) { $childActive = true; break; }
                            }
                        @endphp
                        <div x-data="{ open: {{ $childActive ? 'true' : 'false' }} }">
                            <button @click="open = !open" class="w-full flex items-center gap-3 px-6 py-2.5 font-medium text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60">
                                <i class="bi {{ $item['icon'] ?? 'bi-circle' }} text-[17px]"></i> {{ $item['label'] }}
                                <i class="bi bi-chevron-down ml-auto text-xs text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="open" x-transition>
                                @foreach(($item['children'] ?? []) as $child)
                                    @php
                                        $cmatch = $child['match'] ?? $child['route'] ?? '__none__';
                                        $cactive = isset($child['route']) && Route::has($child['route']) && request()->routeIs($cmatch . '*');
                                        $chref = $child['url'] ?? (isset($child['route']) && Route::has($child['route']) ? route($child['route'], $child['params'] ?? []) : '#');
                                    @endphp
                                    <a href="{{ $chref }}"
                                       class="flex items-center gap-3 py-2.5 pr-6 font-medium {{ $cactive ? 'bg-green-600 text-white shadow-[0_2px_8px_rgba(22,138,69,.35)]' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}"
                                       style="padding-left:3.4rem">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                    @php
                        $match = $item['match'] ?? $item['route'] ?? '';
                        $active = isset($item['route']) && Route::has($item['route']) && (request()->routeIs($match . '*') || ($item['route'] === 'dashboard' && request()->routeIs('*.dashboard')));
                        $href = $item['url'] ?? (isset($item['route']) && Route::has($item['route']) ? route($item['route'], $item['params'] ?? []) : '#');
                    @endphp
                    <a href="{{ $href }}"
                       class="flex items-center gap-3 px-6 py-2.5 font-medium {{ $active ? 'bg-green-600 text-white shadow-[0_2px_8px_rgba(22,138,69,.35)]' : 'text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700/60' }}">
                         <i class="bi {{ $item['icon'] ?? 'bi-circle' }} text-[17px]"></i> {{ $item['label'] }}
                         @if(!empty($item['badge']))<span class="ml-auto text-[11px] px-2 py-0.5 rounded-full {{ $active ? 'bg-white/25 text-white' : 'bg-green-600 text-white' }}">{{ $item['badge'] }}</span>@endif
                     </a>
                    @endif
                @endforeach
            @endforeach
            <div class="px-0 pt-2">
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="w-full flex items-center gap-3 px-6 py-2.5 font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <i class="bi bi-power text-[17px]"></i> Sair
                    </button>
                </form>
            </div>
        </nav>
    </aside>

    {{-- Main --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-20 h-[72px] bg-white/95 dark:bg-slate-800/95 backdrop-blur border-b border-slate-200 dark:border-slate-700 flex items-center gap-2 px-4 lg:px-6">
            <button class="hidden lg:block p-2 text-slate-700 dark:text-slate-300" @click="$dispatch('toggle-sidebar')" title="Recolher menu"><i class="bi bi-layout-sidebar text-xl"></i></button>
            <button class="lg:hidden p-2 -ml-2 text-slate-500" @click="sidebar=true"><i class="bi bi-list text-2xl"></i></button>
            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button onclick="window.ElpayTheme.toggle()" class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700" title="Tema claro/escuro">
                    <span class="dark:hidden"><i class="bi bi-moon text-xl"></i></span>
                    <span class="hidden dark:inline"><i class="bi bi-sun text-xl"></i></span>
                </button>
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 relative">
                        <i class="bi bi-bell text-xl"></i>
                        @if(($unreadCount ?? 0) > 0)<span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 text-[10px] leading-[18px] text-center rounded-full bg-red-500 text-white">{{ $unreadCount }}</span>@endif
                    </button>
                    <div x-show="open" @click.outside="open=false" class="dropdown-panel absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-3 border-b border-slate-100 dark:border-slate-700 font-semibold text-sm">Notificações ({{ $unreadCount ?? 0 }})
                            <a href="{{ Route::has('notifications.index') ? route('notifications.index') : '#' }}" class="text-green-700 float-right font-normal">Ver todas</a></div>
                        @forelse(($unreadNotifications ?? collect())->take(5) as $n)
                            <div class="p-3 border-b border-slate-100 dark:border-slate-700 text-sm"><p class="font-medium">{{ $n->title }}</p><p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($n->message, 80) }}</p></div>
                        @empty<div class="p-4 text-sm text-slate-400 text-center">Sem notificações.</div>@endforelse
                    </div>
                </div>
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open" class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700">
                        <span class="hidden sm:block text-right leading-tight">
                            <span class="block text-[13px] font-bold text-slate-900 dark:text-white max-w-[190px] truncate"><i class="bi bi-patch-check-fill text-green-600 text-xs"></i> {{ auth()->user()->name }}</span>
                            <span class="block text-[12px] text-slate-500 dark:text-slate-400 max-w-[190px] truncate">{{ auth()->user()->email }}</span>
                        </span>
                        <span class="w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-lg overflow-hidden">
                            {{ mb_substr(auth()->user()->name ?? 'E', 0, 1) }}
                        </span>
                    </button>
                    <div x-show="open" @click.outside="open=false" class="dropdown-panel absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden text-sm">
                        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="block px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700">Perfil</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full text-left px-4 py-2 hover:bg-slate-50 dark:hover:bg-slate-700">Sair</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8 w-full max-w-[1400px] mx-auto">
            @include('layouts.partials.flash')
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
