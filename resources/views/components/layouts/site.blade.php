@props(['title' => null, 'description' => null, 'image' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :title="$title" :description="$description" :image="$image" />
</head>
<body class="min-h-screen bg-ink-950 font-sans text-white antialiased">
    @php
        $links = [
            ['Inicio', route('home'), 'home'],
            ['Emprendimientos', route('ventures.index'), 'ventures.*'],
            ['Ranking', route('ranking'), 'ranking'],
            ['Ediciones', route('editions.index'), 'editions.*'],
        ];
    @endphp

    {{-- Navegación --}}
    <header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 10"
            class="fixed inset-x-0 top-0 z-50 transition duration-300"
            :class="scrolled || open ? 'border-b border-white/10 bg-ink-950/80 backdrop-blur-xl' : 'border-b border-transparent'">
        <nav class="container-x flex h-16 items-center justify-between lg:h-20">
            <a href="{{ route('home') }}" class="shrink-0"><x-brand /></a>

            <div class="hidden items-center gap-1 rounded-xl border border-white/10 bg-white/[.03] p-1 backdrop-blur md:flex">
                @foreach ($links as [$label, $url, $pattern])
                    <a href="{{ $url }}" @class([
                        'rounded-lg px-4 py-2 text-sm font-medium transition',
                        'bg-white text-ink-950' => request()->routeIs($pattern),
                        'text-white/70 hover:text-white' => ! request()->routeIs($pattern),
                    ])>{{ $label }}</a>
                @endforeach
            </div>

            <div class="hidden items-center gap-2 md:flex">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-ghost !px-4"><x-icon name="gauge-high" /> Panel admin</a>
                    @endif
                    <div x-data="{ menu: false }" class="relative">
                        <button @click="menu = !menu" @click.outside="menu = false" class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 py-1 pl-1 pr-3 text-sm hover:bg-white/10">
                            <x-avatar :name="auth()->user()->name" size="sm" />
                            <span class="max-w-[8rem] truncate">{{ Str::before(auth()->user()->name, ' ') }}</span>
                            <x-icon name="chevron-down" class="text-xs" />
                        </button>
                        <div x-cloak x-show="menu" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 overflow-hidden rounded-2xl border border-white/10 bg-ink-900 p-1 shadow-2xl">
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-white/80 hover:bg-white/5 hover:text-white"><x-icon name="chart-simple" fw /> Mi actividad</a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-white/80 hover:bg-white/5 hover:text-white"><x-icon name="user-gear" fw /> Mi perfil</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm text-white/80 hover:bg-white/5 hover:text-white"><x-icon name="arrow-right-from-bracket" fw /> Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-3 text-sm font-medium text-white/80 hover:text-white">Ingresar</a>
                    <a href="{{ route('register') }}" class="btn-lion">Únete y vota <x-icon name="arrow-right" /></a>
                @endauth
            </div>

            <button @click="open = !open" class="grid h-10 w-10 place-items-center rounded-lg border border-white/15 md:hidden" aria-label="Menú">
                <x-icon name="bars" x-show="!open" class="text-lg" />
                <x-icon name="xmark" x-cloak x-show="open" class="text-lg" />
            </button>
        </nav>

        <div x-cloak x-show="open" x-collapse class="md:hidden">
            <div class="container-x space-y-1 pb-6 pt-2">
                @foreach ($links as [$label, $url, $pattern])
                    <a href="{{ $url }}" @class(['block rounded-lg px-4 py-3 text-base font-medium', 'bg-white/10 text-white' => request()->routeIs($pattern), 'text-white/70' => ! request()->routeIs($pattern)])>{{ $label }}</a>
                @endforeach
                <div class="mt-4 grid grid-cols-2 gap-2 border-t border-white/10 pt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-ghost">Mi actividad</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Admin</a>
                        @else
                            <a href="{{ route('profile.edit') }}" class="btn-ghost">Mi perfil</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="col-span-2">@csrf<button class="w-full py-2 text-sm text-rose-300">Cerrar sesión</button></form>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost">Ingresar</a>
                        <a href="{{ route('register') }}" class="btn-lion">Registrarme</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    @auth
        @unless (auth()->user()->hasVerifiedEmail())
            <div class="fixed inset-x-0 top-16 z-40 border-b border-lion-400/20 bg-lion-400/10 text-center text-xs text-lion-200 backdrop-blur lg:top-20">
                <div class="container-x flex flex-wrap items-center justify-center gap-2 py-2">
                    <span><x-icon name="envelope-circle-check" class="mr-1" /> Verifica tu correo para que tus votos cuenten.</span>
                    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="font-semibold underline underline-offset-2">Reenviar enlace</button></form>
                </div>
            </div>
        @endunless
    @endauth

    <main>
        {{ $slot }}
    </main>

    {{-- Pie de página --}}
    <footer class="relative mt-24 overflow-hidden border-t border-white/10 bg-ink-950">
        <div class="pointer-events-none absolute -bottom-40 left-1/2 h-80 w-[60rem] -translate-x-1/2 rounded-full bg-lion-500/10 blur-3xl"></div>
        <div class="container-x relative grid gap-10 py-16 md:grid-cols-12">
            <div class="md:col-span-5">
                <x-brand size="lg" />
                <p class="mt-6 max-w-sm text-sm leading-relaxed text-white/60">
                    Vitrina oficial de los emprendimientos creados por los estudiantes Navarristas en la Cátedra UNINAVARRA. Conócelos, vota por tus favoritos y súmate como inversionista, mentor o aliado.
                </p>
            </div>
            <div class="md:col-span-3">
                <h3 class="text-sm font-semibold text-white">Explora</h3>
                <ul class="mt-4 space-y-2 text-sm text-white/60">
                    @foreach ($links as [$label, $url])
                        <li><a href="{{ $url }}" class="hover:text-lion-300">{{ $label }}</a></li>
                    @endforeach
                    <li><a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="hover:text-lion-300">Soy inversionista</a></li>
                </ul>
            </div>
            <div class="md:col-span-4">
                <h3 class="text-sm font-semibold text-white">Contacto</h3>
                <ul class="mt-4 space-y-2 text-sm text-white/60">
                    <li><a href="https://wa.me/{{ config('catedra.contact.whatsapp') }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-lion-300"><x-icon name="whatsapp" style="brands" fw /> {{ config('catedra.contact.phone') }}</a></li>
                    <li><a href="mailto:{{ config('catedra.contact.email') }}" class="flex items-center gap-3 break-all hover:text-lion-300"><x-icon name="envelope" fw /> {{ config('catedra.contact.email') }}</a></li>
                    <li class="flex items-center gap-3"><x-icon name="location-dot" fw /> {{ config('catedra.contact.address') }}</li>
                    <li><a href="{{ config('catedra.contact.site') }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-lion-300"><x-icon name="globe" fw /> uninavarra.edu.co</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/5">
            <div class="container-x flex flex-col items-center justify-between gap-2 py-6 text-xs text-white/40 sm:flex-row">
                <p>© {{ date('Y') }} Fundación Universitaria Navarra – UNINAVARRA. Vigilada MinEducación.</p>
                <p>Neiva, Huila · Colombia</p>
            </div>
        </div>
    </footer>

    <x-toasts />
    @stack('scripts')
</body>
</html>
