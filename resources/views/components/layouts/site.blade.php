@props(['title' => null, 'description' => null, 'image' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <x-head :title="$title" :description="$description" :image="$image" />
</head>
<body class="min-h-screen bg-white font-sans text-ink-900 antialiased">
    @php
        $links = [
            ['Inicio', route('home'), 'home'],
            ['Emprendimientos', route('ventures.index'), 'ventures.*'],
            ['Ranking', route('ranking'), 'ranking'],
            ['Ediciones', route('editions.index'), 'editions.*'],
        ];
    @endphp

    {{-- Navegación --}}
    {{-- Barra: transparente sobre el video de la portada; sólida en las demás páginas --}}
    <header x-data="{ open: false, scrolled: false, solid: @js(! request()->routeIs('home')) }" @scroll.window="scrolled = window.scrollY > 10"
            class="surface-dark fixed inset-x-0 top-0 z-50 text-white transition duration-300"
            :class="scrolled || open || solid ? 'border-b border-white/10 bg-ink-950/90 backdrop-blur-xl' : 'border-b border-transparent'">
        {{-- Anuncio: votación abierta con cuenta regresiva (se puede cerrar; se recuerda por votación) --}}
        @if ($siteEdition?->isVotingOpen())
            @php $announceKey = 'aviso-votacion-'.$siteEdition->id.'-'.$siteEdition->voting_ends_at->timestamp; @endphp
            <div x-data="{ show: true }"
                 x-init="
                    try { show = localStorage.getItem('{{ $announceKey }}') !== '1' } catch (e) {}
                    $watch('show', v => document.documentElement.style.setProperty('--announce-h', v ? $el.offsetHeight + 'px' : '0px'));
                    $nextTick(() => document.documentElement.style.setProperty('--announce-h', show ? $el.offsetHeight + 'px' : '0px'));
                 "
                 x-show="show" x-cloak
                 class="relative bg-gradient-to-r from-lion-500 via-lion-400 to-lion-500 text-ink-950">
                <div class="container-x flex min-h-[2.5rem] items-center justify-center gap-x-4 gap-y-1 py-1.5 pr-10 text-xs font-semibold sm:text-sm">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-check-to-slot" aria-hidden="true"></i> ¡La votación está abierta!</span>
                    <span class="inline-flex items-center gap-1.5 tabular-nums" x-data="countdown('{{ $siteEdition->voting_ends_at->toIso8601String() }}')">
                        <span class="hidden font-medium sm:inline">Cierra en</span>
                        <span class="rounded-md bg-ink-950/10 px-1.5 py-0.5" x-text="d + 'd'"></span>
                        <span class="rounded-md bg-ink-950/10 px-1.5 py-0.5" x-text="h + 'h'"></span>
                        <span class="rounded-md bg-ink-950/10 px-1.5 py-0.5" x-text="m + 'm'"></span>
                        <span class="rounded-md bg-ink-950/10 px-1.5 py-0.5" x-text="s + 's'"></span>
                    </span>
                    <a href="{{ route('ventures.index') }}" class="hidden items-center gap-1.5 underline underline-offset-2 hover:no-underline sm:inline-flex">Vota ahora <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <button type="button" @click="show = false; try { localStorage.setItem('{{ $announceKey }}', '1') } catch (e) {}"
                        class="absolute right-2 top-1/2 grid h-8 w-8 -translate-y-1/2 place-items-center rounded-md hover:bg-ink-950/10 sm:right-4" aria-label="Cerrar anuncio">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

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
                    @elseif (auth()->user()->isJury())
                        <a href="{{ route('jury.index') }}" class="btn-ghost !px-4"><x-icon name="gavel" /> Panel del jurado</a>
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
                        @elseif (auth()->user()->isJury())
                            <a href="{{ route('jury.index') }}" class="btn-ghost">Panel jurado</a>
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
            <div class="fixed inset-x-0 top-[calc(4rem+var(--announce-h,0px))] z-40 border-b border-lion-300 bg-lion-100 text-center text-xs text-lion-900 lg:top-[calc(5rem+var(--announce-h,0px))]">
                <div class="container-x flex flex-wrap items-center justify-center gap-2 py-2">
                    <span><x-icon name="envelope-circle-check" class="mr-1" /> Verifica tu correo para que tus votos cuenten.</span>
                    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="font-semibold underline underline-offset-2">Reenviar enlace</button></form>
                </div>
            </div>
        @endunless
    @endauth

    <main style="padding-top: var(--announce-h, 0px)" class="surface-light min-h-[60vh] pb-24 transition-[padding] duration-300">
        {{ $slot }}
    </main>

    {{-- Pie de página (blanco minimalista) --}}
    @php
        $site = \App\Models\Setting::get('contact_site');
        $siteLabel = preg_replace('~^https?://(www\.)?~', '', rtrim($site, '/'));
    @endphp
    <footer class="surface-light relative border-t border-white/10">
        <div class="container-x relative py-14">
            {{-- Acceso al sitio institucional --}}
            <a href="{{ $site }}" target="_blank" rel="noopener"
               class="group mb-12 flex flex-col items-start justify-between gap-4 rounded-2xl border border-white/10 bg-white/5 p-6 transition hover:border-lion-400/40 sm:flex-row sm:items-center sm:p-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-lion-400"><x-icon name="graduation-cap" /> Fundación Universitaria Navarra</p>
                    <p class="mt-2 font-display text-2xl font-semibold text-white">Conoce los programas que ofrece UNINAVARRA</p>
                    <p class="mt-1 text-sm text-white/60">Pregrados, posgrados y educación continua en el sitio institucional.</p>
                </div>
                <span class="btn-ghost shrink-0 group-hover:border-lion-400/40">{{ $siteLabel }} <x-icon name="arrow-up-right-from-square" /></span>
            </a>

            <div class="grid gap-10 md:grid-cols-12">
                <div class="md:col-span-5">
                    <x-brand :dark="false" size="lg" />
                    <p class="mt-6 max-w-sm text-sm leading-relaxed text-white/60">{{ \App\Models\Setting::get('footer_text') }}</p>
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
                        <li><a href="https://wa.me/{{ \App\Models\Setting::whatsapp('contact_phone') }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-lion-300"><x-icon name="whatsapp" style="brands" fw /> {{ \App\Models\Setting::get('contact_phone') }}</a></li>
                        <li><a href="mailto:{{ \App\Models\Setting::get('contact_email') }}" class="flex items-center gap-3 break-all hover:text-lion-300"><x-icon name="envelope" fw /> {{ \App\Models\Setting::get('contact_email') }}</a></li>
                        <li class="flex items-center gap-3"><x-icon name="location-dot" fw /> {{ \App\Models\Setting::get('contact_address') }}</li>
                        <li><a href="{{ $site }}" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-lion-300"><x-icon name="globe" fw /> {{ $siteLabel }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="container-x flex flex-col items-center justify-between gap-2 py-6 text-xs text-white/50 sm:flex-row">
                <p>© {{ date('Y') }} Fundación Universitaria Navarra – UNINAVARRA. Vigilada MinEducación.</p>
                <p>Neiva, Huila · Colombia</p>
            </div>
        </div>
    </footer>

    <x-toasts />
    @stack('scripts')
</body>
</html>
