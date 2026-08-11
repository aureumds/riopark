@props([
    'title' => 'Rio Park',
    'subtitle' => null,
])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — Rio Park</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen lg:flex">
        {{-- Overlay mobile --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 flex flex-col transform transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-800 flex items-center justify-center text-white font-bold text-sm">RP</div>
                    <div>
                        <p class="font-semibold text-slate-900 leading-tight">Rio Park</p>
                        <p class="text-xs text-slate-500">Painel da plataforma</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu</p>

                <a href="{{ route('super.dashboard') }}"
                   class="super-nav-link {{ request()->routeIs('super.dashboard') ? 'super-nav-link-active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <a href="{{ route('super.companies') }}"
                   class="super-nav-link {{ request()->routeIs('super.companies') ? 'super-nav-link-active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Empresas
                </a>

                <a href="{{ route('super.plans') }}"
                   class="super-nav-link {{ request()->routeIs('super.plans') ? 'super-nav-link-active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Planos
                </a>
            </nav>

            <div class="p-4 border-t border-slate-100">
                <div class="flex items-center gap-3 px-2 py-2 mb-2">
                    <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center text-sm font-semibold text-slate-600">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">Super Admin</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full super-btn-ghost justify-center text-sm">
                        Sair da conta
                    </button>
                </form>
            </div>
        </aside>

        {{-- Conteúdo --}}
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200 px-4 sm:px-8 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-slate-100" @click="sidebarOpen = true">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-xl sm:text-2xl font-semibold text-slate-900 truncate">{{ $title }}</h1>
                            @if($subtitle)
                                <p class="text-sm text-slate-500 mt-0.5 truncate">{{ $subtitle }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-8">
                @if(session('success'))
                    <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 flex items-start gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm" role="alert">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
