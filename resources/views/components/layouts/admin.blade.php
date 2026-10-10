@props(['title' => 'Panel', 'actions' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :title="$title.' · Admin'" />
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
    @php
        $nav = [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['Ediciones', 'admin.editions.index', 'admin.editions.*', 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['Emprendimientos', 'admin.ventures.index', 'admin.ventures.*', 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['Resultados', 'admin.results.index', 'admin.results.*', 'M9 19V6l-2 2m10 11V10m-4 9v-5M4 21h16'],
            ['Intenciones de apoyo', 'admin.pledges.index', 'admin.pledges.*', 'M12 8c-2 0-3 1-3 2s1 2 3 2 3 1 3 2-1 2-3 2m0-8V6m0 10v2m9-6a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Comentarios', 'admin.comments.index', 'admin.comments.*', 'M4 5h16v11H8l-4 4V5z'],
            ['Usuarios', 'admin.users.index', 'admin.users.*', 'M17 20h5v-2a3 3 0 00-5-2.2M9 20H2v-2a3 3 0 015-2.2m5-5.8a3 3 0 100-6 3 3 0 000 6zm6 2a2 2 0 100-4 2 2 0 000 4zM6 12a2 2 0 100-4 2 2 0 000 4z'],
            ['Categorías', 'admin.categories.index', 'admin.categories.*', 'M7 7h.01M7 3h5l8 8-9 9-8-8V7a4 4 0 014-4z'],
        ];
        $pendingPledges = \App\Models\SupportPledge::where('status', 'pendiente')->count();
        $pendingComments = \App\Models\Comment::where('is_approved', false)->count();
    @endphp

    <div x-data="{ open: false }" class="flex min-h-screen">
        {{-- Sidebar --}}
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-ink-950/60 lg:hidden" @click="open = false"></div>
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-ink-950 text-white transition-transform lg:static lg:translate-x-0">
            <div class="flex h-20 items-center px-6"><a href="{{ route('admin.dashboard') }}"><x-brand /></a></div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($nav as [$label, $route, $pattern, $icon])
                    <a href="{{ route($route) }}" @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-white/10 text-white' => request()->routeIs($pattern),
                        'text-white/60 hover:bg-white/5 hover:text-white' => ! request()->routeIs($pattern),
                    ])>
                        <svg class="h-5 w-5 shrink-0 {{ request()->routeIs($pattern) ? 'text-lion-400' : 'text-white/40 group-hover:text-white/70' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        <span class="flex-1">{{ $label }}</span>
                        @if ($route === 'admin.pledges.index' && $pendingPledges)<span class="rounded-full bg-lion-400 px-2 py-0.5 text-[11px] font-bold text-ink-950">{{ $pendingPledges }}</span>@endif
                        @if ($route === 'admin.comments.index' && $pendingComments)<span class="rounded-full bg-rose-500 px-2 py-0.5 text-[11px] font-bold">{{ $pendingComments }}</span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-white/10 p-4">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-white/60 hover:bg-white/5 hover:text-white">↗ Ver sitio público</a>
                <div class="mt-2 flex items-center gap-3 px-3 py-2">
                    <x-avatar :name="auth()->user()->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-xs text-white/50 hover:text-rose-300">Cerrar sesión</button></form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Contenido --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur">
                <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <button @click="open = true" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 lg:hidden" aria-label="Menú">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <h1 class="flex-1 truncate font-display text-xl font-bold text-ink-900">{{ $title }}</h1>
                    @if ($actions)<div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>@endif
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        <p class="font-semibold">Revisa los siguientes campos:</p>
                        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-toasts />
    @stack('scripts')
</body>
</html>
