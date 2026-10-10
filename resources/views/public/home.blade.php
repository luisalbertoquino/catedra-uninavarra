<x-layouts.site>
    {{-- ================= HERO ================= --}}
    <section class="noise relative isolate overflow-hidden pb-20 pt-32 lg:pb-32 lg:pt-44">
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-grid-light [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            <div class="absolute -left-40 top-10 h-[34rem] w-[34rem] animate-blob rounded-full bg-volt-600/30 blur-3xl"></div>
            <div class="absolute -right-32 top-40 h-[30rem] w-[30rem] animate-blob rounded-full bg-lion-500/25 blur-3xl [animation-delay:-6s]"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 animate-blob rounded-full bg-aqua-500/20 blur-3xl [animation-delay:-12s]"></div>
        </div>

        <div class="container-x">
            <div class="mx-auto max-w-5xl text-center">
                <div class="animate-fade-up">
                    <span class="chip border-lion-400/30 bg-lion-400/10 text-lion-200">
                        <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-lion-400 opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-lion-400"></span></span>
                        {{ $edition?->name ?? 'Cátedra UNINAVARRA' }} · {{ $edition?->event_name ?? 'LION HUB EVENT' }}
                    </span>
                </div>

                <h1 class="mt-8 animate-fade-up font-display text-5xl font-bold leading-[.95] tracking-tight [animation-delay:.1s] sm:text-7xl lg:text-8xl">
                    Ideas que <span class="text-gradient">rugen</span><br class="hidden sm:block">
                    desde el Huila
                </h1>

                <p class="mx-auto mt-7 max-w-2xl animate-fade-up text-lg text-white/65 [animation-delay:.2s] sm:text-xl">
                    Descubre los emprendimientos creados por los estudiantes Navarristas{{ $edition?->tagline ? ' en «'.$edition->tagline.'»' : '' }}.
                    Vota por tus favoritos, déjales tu opinión o súmate como inversionista y aliado.
                </p>

                <div class="mt-10 flex animate-fade-up flex-col items-center justify-center gap-3 [animation-delay:.3s] sm:flex-row">
                    <a href="{{ route('ventures.index') }}" class="btn-lion px-7 py-3.5 text-base">
                        Explorar emprendimientos
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn-ghost px-7 py-3.5 text-base">💼 Quiero invertir</a>
                </div>

                @if ($edition?->final_at && $edition->final_at->isFuture())
                    <div x-data="countdown('{{ $edition->final_at->toIso8601String() }}')" class="mx-auto mt-16 max-w-xl animate-fade-up [animation-delay:.4s]">
                        <p class="text-xs font-semibold uppercase tracking-[.25em] text-white/50">Gran final · {{ $edition->final_at->translatedFormat('l j \d\e F, g:i a') }}</p>
                        <div class="mt-4 grid grid-cols-4 gap-2 sm:gap-3">
                            @foreach (['d' => 'Días', 'h' => 'Horas', 'm' => 'Min', 's' => 'Seg'] as $k => $label)
                                <div class="glass rounded-2xl py-4">
                                    <div class="font-display text-3xl font-bold tabular-nums sm:text-4xl" x-text="{{ $k }}">00</div>
                                    <div class="mt-1 text-[11px] uppercase tracking-widest text-white/50">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ================= MARQUEE ================= --}}
    @if ($featured->isNotEmpty())
        <div class="relative overflow-hidden border-y border-white/10 bg-white/[.02] py-5 mask-fade-x">
            <div class="flex w-max animate-marquee gap-10 hover:[animation-play-state:paused]">
                @foreach ([1, 2] as $_)
                    @foreach ($featured as $v)
                        <a href="{{ route('ventures.show', $v) }}" class="flex items-center gap-3 whitespace-nowrap font-display text-2xl font-semibold text-white/40 transition hover:text-white">
                            <span class="text-lion-400">✦</span> {{ $v->name }}
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================= STATS ================= --}}
    <section class="container-x py-20">
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['ventures', 'Emprendimientos en competencia', '🚀'],
                ['votes', 'Votos de la comunidad', '🗳️'],
                ['community', 'Personas registradas', '🦁'],
                ['pledges', 'Intenciones de apoyo', '🤝'],
            ] as [$key, $label, $icon])
                <div class="card-dark reveal p-6" x-data="counter({{ $stats[$key] }})" x-intersect.once="run()">
                    <div class="text-2xl">{{ $icon }}</div>
                    <div class="mt-4 font-display text-4xl font-bold tabular-nums text-white sm:text-5xl" x-text="formatted">{{ $stats[$key] }}</div>
                    <div class="mt-1 text-sm text-white/55">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= DESTACADOS ================= --}}
    @if ($featured->isNotEmpty())
        <section class="container-x py-12">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div class="reveal">
                    <span class="eyebrow">✦ En competencia</span>
                    <h2 class="section-title mt-3">Los más votados<br class="hidden sm:block"> por la comunidad</h2>
                </div>
                <a href="{{ route('ventures.index') }}" class="btn-ghost reveal self-start sm:self-auto">Ver todos →</a>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $i => $venture)
                    <x-venture-card :venture="$venture" :rank="$i + 1" class="reveal" style="transition-delay: {{ $i * 70 }}ms" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CÓMO PARTICIPAR ================= --}}
    <section class="container-x py-20">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow">✦ Tu papel</span>
            <h2 class="section-title mt-3">Tres formas de hacer parte</h2>
            <p class="mt-4 text-white/60">La comunidad no solo mira: decide. Tu voto pesa el {{ $edition?->community_weight ?? 30 }}% de la evaluación que define a los finalistas.</p>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach ([
                ['🗳️', 'Vota', 'Regístrate y elige hasta '.($edition?->max_votes_per_user ?? 3).' emprendimientos favoritos. Puedes cambiar tu voto mientras la votación esté abierta.', 'from-lion-400/30', route('ventures.index'), 'Ir a votar'],
                ['💬', 'Opina', 'Califica de 1 a 5 y deja tu retroalimentación. Los equipos la usan para mejorar su pitch antes de la gran final.', 'from-volt-500/30', route('ranking'), 'Ver ranking'],
                ['💼', 'Invierte o apoya', 'Si eres empresario, inversionista o mentor, registra tu intención de apoyo y la Cátedra te conecta con el equipo.', 'from-aqua-500/30', route('register', ['perfil' => 'inversionista']), 'Registrarme como aliado'],
            ] as $i => [$icon, $title, $text, $grad, $url, $cta])
                <div class="card-dark card-dark-hover reveal relative overflow-hidden p-8" style="transition-delay: {{ $i * 100 }}ms">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-gradient-to-br {{ $grad }} to-transparent blur-2xl"></div>
                    <div class="relative">
                        <span class="font-display text-sm font-bold text-white/30">0{{ $i + 1 }}</span>
                        <div class="mt-4 text-4xl">{{ $icon }}</div>
                        <h3 class="mt-5 font-display text-2xl font-bold">{{ $title }}</h3>
                        <p class="mt-3 text-white/60">{{ $text }}</p>
                        <a href="{{ $url }}" class="mt-6 inline-flex items-center gap-1 text-sm font-semibold text-lion-300 hover:text-lion-200">{{ $cta }} →</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= CRONOGRAMA ================= --}}
    @if ($edition && $edition->milestones->isNotEmpty())
        <section class="relative overflow-hidden py-20">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-transparent via-volt-600/[.07] to-transparent"></div>
            <div class="container-x grid gap-12 lg:grid-cols-12">
                <div class="reveal lg:col-span-4">
                    <div class="lg:sticky lg:top-32">
                        <span class="eyebrow">✦ Ruta {{ $edition->period_label }}</span>
                        <h2 class="section-title mt-3">Del aula al escenario</h2>
                        <p class="mt-4 text-white/60">{{ Str::limit($edition->description, 260) }}</p>
                        @if ($edition->final_location)
                            <div class="glass mt-8 rounded-2xl p-5 text-sm">
                                <div class="text-white/50">Gran final</div>
                                <div class="mt-1 font-semibold">{{ $edition->final_at?->translatedFormat('l j \d\e F · g:i a') }}</div>
                                <div class="mt-1 text-white/60">📍 {{ $edition->final_location }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <ol class="relative space-y-4 lg:col-span-8">
                    <span class="absolute bottom-6 left-[1.35rem] top-6 w-px bg-gradient-to-b from-lion-400 via-volt-500 to-aqua-500/0" aria-hidden="true"></span>
                    @foreach ($edition->milestones as $i => $m)
                        @php $st = $m->status(); @endphp
                        <li class="reveal relative flex gap-5" style="transition-delay: {{ $i * 60 }}ms">
                            <span @class([
                                'relative z-10 mt-5 grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 font-display text-sm font-bold',
                                'border-lion-400 bg-lion-400 text-ink-950' => $st === 'completada',
                                'border-lion-400 bg-ink-950 text-lion-300 shadow-[0_0_0_6px_rgba(245,183,10,.15)]' => $st === 'en_curso',
                                'border-white/15 bg-ink-900 text-white/50' => $st === 'pendiente',
                            ])>{{ $st === 'completada' ? '✓' : $i + 1 }}</span>
                            <div @class(['card-dark flex-1 p-5 sm:p-6', '!border-lion-400/40 !bg-lion-400/[.06]' => $st === 'en_curso'])>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-lion-300">{{ $m->date_label }}</span>
                                    @if ($st === 'en_curso')<span class="chip border-emerald-400/30 bg-emerald-400/10 !py-0.5 text-emerald-300">● En curso</span>@endif
                                </div>
                                <h3 class="mt-2 font-display text-lg font-bold sm:text-xl">{{ $m->title }}</h3>
                                @if ($m->description)<p class="mt-1.5 text-sm text-white/60">{{ $m->description }}</p>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- ================= CATEGORÍAS ================= --}}
    @if ($categories->isNotEmpty())
        <section class="container-x py-16">
            <div class="reveal text-center">
                <span class="eyebrow">✦ Sectores</span>
                <h2 class="section-title mt-3">Encuentra lo que te mueve</h2>
            </div>
            <div class="reveal mt-10 flex flex-wrap justify-center gap-3">
                @foreach ($categories as $c)
                    <a href="{{ route('ventures.index', ['categoria' => $c->slug]) }}"
                       class="group inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[.03] px-5 py-3 text-sm font-medium transition hover:-translate-y-0.5 hover:border-white/30"
                       style="--c: {{ $c->color }}">
                        <span class="text-lg">{{ $c->icon }}</span>
                        {{ $c->name }}
                        <span class="rounded-full px-2 py-0.5 text-xs font-bold text-ink-950" style="background: var(--c)">{{ $c->ventures_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CTA INVERSIONISTAS ================= --}}
    <section class="container-x py-16">
        <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-lion-300 via-lion-500 to-lion-700 p-8 text-ink-950 sm:p-14">
            <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/30 blur-3xl"></div>
            <svg class="pointer-events-none absolute -bottom-10 right-0 h-72 w-72 text-ink-950/10" viewBox="0 0 24 24" fill="currentColor"><path d="M7 6h2.6v7.2a2.4 2.4 0 104.8 0V6H17v7.2a5 5 0 11-10 0V6z"/></svg>
            <div class="relative grid items-center gap-8 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <h2 class="font-display text-3xl font-bold leading-tight sm:text-5xl">¿Eres inversionista, empresario o mentor?</h2>
                    <p class="mt-4 max-w-xl text-lg text-ink-950/75">Conecta con el talento emprendedor del sur colombiano. Registra tu intención de inversión, donación, mentoría o alianza y te ponemos en contacto con los equipos.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:col-span-2 lg:justify-end">
                    <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn bg-ink-950 px-7 py-3.5 text-base text-white hover:bg-ink-800">Registrarme como aliado</a>
                    <a href="https://wa.me/{{ config('catedra.contact.whatsapp') }}" target="_blank" rel="noopener" class="btn border border-ink-950/20 px-7 py-3.5 text-base hover:bg-ink-950/10">Hablar con la Cátedra</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= EDICIONES ANTERIORES ================= --}}
    @if ($pastEditions->isNotEmpty())
        <section class="container-x py-16">
            <div class="flex items-end justify-between gap-6">
                <div class="reveal">
                    <span class="eyebrow">✦ Historia</span>
                    <h2 class="section-title mt-3">Ediciones anteriores</h2>
                </div>
                <a href="{{ route('editions.index') }}" class="reveal hidden text-sm font-semibold text-lion-300 sm:block">Ver todas →</a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($pastEditions as $e)
                    <a href="{{ route('editions.show', $e) }}" class="card-dark card-dark-hover reveal group p-7">
                        <div class="font-display text-6xl font-bold text-white/10 transition group-hover:text-lion-400/30">{{ $e->period_label }}</div>
                        <h3 class="mt-4 font-display text-xl font-bold">{{ $e->name }}</h3>
                        <p class="mt-1 text-sm text-white/55">{{ $e->tagline }}</p>
                        <p class="mt-6 text-sm text-white/70">{{ $e->ventures_count }} emprendimientos →</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
