@props(['title' => 'Panel', 'actions' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :title="$title.' · Admin'" />
    @stack('head')
</head>
<body class="theme-admin min-h-screen bg-[#f6f1fb] font-sans text-ink-800 antialiased">
    @php
        $nav = [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'gauge-high'],
            ['Ediciones', 'admin.editions.index', 'admin.editions.*', 'calendar-days'],
            ['Emprendimientos', 'admin.ventures.index', 'admin.ventures.*', 'lightbulb'],
            ['Resultados', 'admin.results.index', 'admin.results.*', 'ranking-star'],
            ['Jurados', 'admin.juries.index', 'admin.juries.*', 'gavel'],
            ['Intenciones de apoyo', 'admin.pledges.index', 'admin.pledges.*', 'hand-holding-dollar'],
            ['Comentarios', 'admin.comments.index', 'admin.comments.*', 'comments'],
            ['Usuarios', 'admin.users.index', 'admin.users.*', 'users'],
            ['Categorías', 'admin.categories.index', 'admin.categories.*', 'tags'],
            ['Importar Excel', 'admin.import.create', 'admin.import.*', 'file-import'],
            ['Configuración', 'admin.settings.edit', 'admin.settings.*', 'sliders'],
        ];
        $pendingPledges = \App\Models\SupportPledge::where('status', 'pendiente')->count();
        $pendingComments = \App\Models\Comment::where('is_approved', false)->count();
    @endphp

    <div x-data="{ open: false }" class="flex min-h-screen">
        {{-- Sidebar --}}
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-ink-950/60 lg:hidden" @click="open = false"></div>
        <aside :class="open ? 'translate-x-0' : '-translate-x-full'" class="ecos-g-profundo fixed inset-y-0 left-0 z-50 flex w-72 flex-col text-white transition-transform lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
            <div class="flex h-20 items-center px-6"><a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3"><x-brand size="sm" /><span class="border-l border-white/40 pl-3 text-xs font-semibold uppercase tracking-widest text-white/90">Admin</span></a></div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($nav as [$label, $route, $pattern, $icon])
                    <a href="{{ route($route) }}" @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-white text-lion-700 shadow-sm' => request()->routeIs($pattern),
                        'text-white/90 hover:bg-white/15 hover:text-white' => ! request()->routeIs($pattern),
                    ])>
                        <i class="fa-solid fa-{{ $icon }} fa-fw shrink-0 text-base {{ request()->routeIs($pattern) ? 'text-lion-500' : 'text-white/80 group-hover:text-white' }}" aria-hidden="true"></i>
                        <span class="flex-1">{{ $label }}</span>
                        @if ($route === 'admin.pledges.index' && $pendingPledges)<span class="rounded-full bg-coral-500 px-2 py-0.5 text-[11px] font-bold text-white">{{ $pendingPledges }}</span>@endif
                        @if ($route === 'admin.comments.index' && $pendingComments)<span class="rounded-full bg-coral-500 px-2 py-0.5 text-[11px] font-bold">{{ $pendingComments }}</span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="border-t border-white/25 p-4">
                <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-white/90 hover:bg-white/15 hover:text-white"><i class="fa-solid fa-arrow-up-right-from-square fa-fw" aria-hidden="true"></i> Ver sitio público</a>
                <div class="mt-2 flex items-center gap-3 px-3 py-2">
                    <x-avatar :name="auth()->user()->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium">{{ auth()->user()->name }}</div>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="inline-flex items-center gap-1.5 text-xs text-white/85 hover:text-white"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Cerrar sesión</button></form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Contenido --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-ink-900/[.06] bg-white/85 backdrop-blur">
                <div class="flex h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                    <button @click="open = true" class="grid h-10 w-10 place-items-center rounded-full bg-tarjeta lg:hidden" aria-label="Menú">
                        <x-icon name="bars" />
                    </button>
                    <h1 class="flex-1 truncate font-display text-2xl font-medium text-lion-700">{{ $title }}</h1>
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
