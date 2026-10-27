@props(['title' => 'Panel del jurado'])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :title="$title.' · Jurado'" />
</head>
<body class="surface-light min-h-screen bg-white font-sans text-ink-900 antialiased">
    @php
        $user = auth()->user();
        $juryEdition = \App\Models\Edition::where('is_active', true)->first();
    @endphp
    <header class="sticky top-0 z-40 border-b border-ink-900/[.06] bg-white/90 backdrop-blur-xl">
        <div class="container-x flex h-16 items-center justify-between gap-4 lg:h-20">
            <a href="{{ route('jury.index') }}" class="flex items-center gap-3">
                <x-brand :dark="false" size="sm" />
                <span class="hidden border-l border-white/15 pl-3 text-xs font-semibold uppercase tracking-widest text-white/60 sm:inline">Panel del jurado</span>
            </a>
            <div class="flex items-center gap-3">
                <div class="hidden text-right sm:block">
                    <div class="text-sm font-semibold">{{ $user->name }}</div>
                    <div class="text-xs {{ $user->is_expert ? 'text-lion-300' : 'text-white/50' }}">
                        @if ($user->is_expert)<x-icon name="award" /> {{ $juryEdition?->expert_title ?? 'Jurado experto' }}@else Jurado @endif
                    </div>
                </div>
                <x-avatar :name="$user->name" size="sm" />
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="grid h-10 w-10 place-items-center rounded-full bg-tarjeta hover:bg-lion-100" title="Cerrar sesión" aria-label="Cerrar sesión"><x-icon name="arrow-right-from-bracket" /></button>
                </form>
            </div>
        </div>
    </header>

    <main class="pb-20">
        {{ $slot }}
    </main>

    <x-toasts />
    @stack('scripts')
</body>
</html>
