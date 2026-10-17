<x-layouts.site>
    {{-- ================= HERO con video promocional de fondo ================= --}}
    @php $videoId = $edition?->promo_video_id; @endphp
    <section class="noise relative isolate overflow-hidden pb-20 pt-24 lg:pb-32 lg:pt-32" x-data="{ watch: false }">
        <div class="pointer-events-none absolute inset-0 -z-10">
            @if ($videoId)
                {{-- Póster mientras carga el video (o si el navegador bloquea la reproducción automática) --}}
                <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="hero-video absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                    <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1&mute=1&loop=1&playlist={{ $videoId }}&controls=0&disablekb=1&modestbranding=1&playsinline=1&rel=0&iv_load_policy=3&fs=0"
                            title="Video promocional de la Cátedra UNINAVARRA" tabindex="-1" class="h-full w-full"
                            allow="autoplay; encrypted-media; picture-in-picture" loading="eager"></iframe>
                </div>
                {{-- Oscurecido para que el video no compita con el contenido --}}
                <div class="absolute inset-0 bg-ink-950/65"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-ink-950/60 via-transparent to-ink-950"></div>
            @endif
            <div class="absolute inset-0 bg-grid-light [background-size:56px_56px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            <div class="absolute -left-40 top-10 h-[34rem] w-[34rem] animate-blob rounded-full bg-volt-600/20 blur-3xl"></div>
            <div class="absolute -right-32 top-40 h-[30rem] w-[30rem] animate-blob rounded-full bg-lion-500/15 blur-3xl [animation-delay:-6s]"></div>
        </div>

        <div class="container-x">
            <div class="mx-auto max-w-6xl text-center">
                <h1 class="hero-title animate-fade-up">
                    Ideas que <span class="text-gradient">rugen</span><br class="hidden sm:block">
                    desde el Huila
                </h1>

                <p class="mx-auto mt-8 max-w-3xl animate-fade-up text-lg font-light italic leading-relaxed text-lion-300 [animation-delay:.2s] sm:text-2xl">
                    &ldquo;Descubre los emprendimientos creados por los estudiantes Navarristas{{ $edition?->tagline ? ' en «'.$edition->tagline.'»' : '' }}.
                    Vota por tus favoritos, déjales tu opinión o súmate como inversionista y aliado.&rdquo;
                </p>

                <div class="mt-10 flex animate-fade-up flex-col items-center justify-center gap-3 [animation-delay:.3s] sm:flex-row">
                    <a href="{{ route('ventures.index') }}" class="btn-lion px-7 py-3.5 text-base">
                        Explorar emprendimientos <x-icon name="arrow-right" />
                    </a>
                    <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn-ghost px-7 py-3.5 text-base"><x-icon name="briefcase" /> Quiero invertir</a>
                    @if ($videoId)
                        <button type="button" @click="watch = true" class="btn-ghost px-7 py-3.5 text-base"><x-icon name="circle-play" /> Ver video</button>
                    @endif
                </div>

                @if ($edition?->final_at && $edition->final_at->isFuture())
                    {{-- Contador de números grandes hacia la gran final --}}
                    <div x-data="countdown('{{ $edition->final_at->toIso8601String() }}')" class="mx-auto mt-16 animate-fade-up [animation-delay:.4s] lg:mt-20">
                        <p class="flex flex-col items-center justify-center gap-1.5 text-center text-xs font-semibold uppercase tracking-[.22em] text-white/90 sm:text-sm sm:tracking-[.3em]">
                            <span class="inline-flex items-center justify-center gap-2"><x-icon name="flag-checkered" /> Gran final</span>
                            <span>{{ $edition->final_at->translatedFormat('l j \d\e F, g:i a') }}</span>
                        </p>
                        <div class="mt-6 flex items-start justify-center gap-1 sm:gap-3 lg:gap-5">
                            @foreach (['d' => 'Días', 'h' => 'Horas', 'm' => 'Minutos', 's' => 'Segundos'] as $k => $label)
                                @unless ($loop->first)
                                    <span class="countdown-sep font-display text-5xl font-semibold leading-none text-lion-400/70 sm:text-7xl lg:text-8xl" aria-hidden="true">:</span>
                                @endunless
                                <div class="min-w-[3.75rem] text-center sm:min-w-[7rem] lg:min-w-[9rem]">
                                    <div class="countdown-num font-display text-5xl font-semibold leading-none tabular-nums sm:text-7xl lg:text-8xl" x-text="{{ $k }}">00</div>
                                    <div class="mt-3 text-[10px] font-semibold uppercase tracking-[.25em] text-white/50 sm:text-xs">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Video con sonido y controles --}}
        @if ($videoId)
            <template x-teleport="body">
                <div x-cloak x-show="watch" x-transition.opacity @keydown.escape.window="watch = false"
                     class="fixed inset-0 z-[80] flex items-center justify-center bg-ink-950/95 p-4 backdrop-blur" @click.self="watch = false">
                    <button type="button" @click="watch = false" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-lg bg-white/10 hover:bg-white/20" aria-label="Cerrar"><x-icon name="xmark" class="text-xl" /></button>
                    <div class="aspect-video w-full max-w-5xl overflow-hidden rounded-2xl border border-white/10 bg-black shadow-2xl">
                        <template x-if="watch">
                            <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1&rel=0" title="Video promocional de la Cátedra UNINAVARRA"
                                    class="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                        </template>
                    </div>
                </div>
            </template>
        @endif
    </section>

    {{-- ================= MARQUEE ================= --}}
    @if ($featured->isNotEmpty())
        <div class="relative overflow-hidden border-y border-white/10 bg-white/[.02] py-5 mask-fade-x">
            <div class="flex w-max animate-marquee gap-10 hover:[animation-play-state:paused]">
                @foreach ([1, 2] as $_)
                    @foreach ($featured as $v)
                        <a href="{{ route('ventures.show', $v) }}" class="flex items-center gap-3 whitespace-nowrap font-display text-2xl font-semibold text-white/40 transition hover:text-white">
                            <x-icon name="star" class="text-sm" /> {{ $v->name }}
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================= STATS ================= --}}
    <section class="container-x py-20">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['ventures', 'Emprendimientos en competencia', 'rocket'],
                ['votes', 'Votos de la comunidad', 'check-to-slot'],
                ['community', 'Personas registradas', 'users'],
                ['pledges', 'Intenciones de apoyo', 'handshake'],
            ] as [$key, $label, $icon])
                <div class="card-dark card-dark-hover reveal flex items-center justify-between gap-4 overflow-hidden p-5 sm:p-6"
                     x-data="counter({{ $stats[$key] }})" x-intersect:enter.half="run()" x-intersect:leave="reset()">
                    <div class="flex min-w-0 flex-col gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-lion-400/10"><x-icon :name="$icon" class="text-lg" /></span>
                        <span class="text-sm leading-snug text-white/60">{{ $label }}</span>
                    </div>
                    <div class="countdown-num shrink-0 font-display text-5xl font-semibold leading-none tabular-nums sm:text-6xl xl:text-7xl"
                         x-text="formatted" aria-label="{{ $stats[$key] }}">{{ number_format($stats[$key], 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= DESTACADOS ================= --}}
    @if ($featured->isNotEmpty())
        <section class="container-x py-12">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div class="reveal">
                    <span class="eyebrow"><x-icon name="fire" /> En competencia</span>
                    <h2 class="section-title mt-3">Los más votados<br class="hidden sm:block"> por la comunidad</h2>
                </div>
                <a href="{{ route('ventures.index') }}" class="btn-ghost reveal self-start sm:self-auto">Ver todos <x-icon name="arrow-right" /></a>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $i => $venture)
                    <x-venture-card :venture="$venture" :rank="$i + 1" class="reveal" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CÓMO PARTICIPAR ================= --}}
    <section class="container-x py-20">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="eyebrow"><x-icon name="user-group" /> Tu papel</span>
            <h2 class="section-title mt-3">Tres formas de hacer parte</h2>
            <p class="mt-4 text-white/60">La comunidad no solo mira: decide. Tu voto pesa el {{ $edition?->community_weight ?? 30 }}% de la evaluación que define a los finalistas.</p>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach ([
                ['check-to-slot', 'Vota', 'Regístrate y elige hasta '.($edition?->max_votes_per_user ?? 3).' emprendimientos favoritos. Puedes cambiar tu voto mientras la votación esté abierta.', route('ventures.index'), 'Ir a votar'],
                ['comments', 'Opina', 'Califica de 1 a 5 y deja tu retroalimentación. Los equipos la usan para mejorar su pitch antes de la gran final.', route('ranking'), 'Ver ranking'],
                ['briefcase', 'Invierte o apoya', 'Si eres empresario, inversionista o mentor, registra tu intención de apoyo y la Cátedra te conecta con el equipo.', route('register', ['perfil' => 'inversionista']), 'Registrarme como aliado'],
            ] as $i => [$icon, $title, $text, $url, $cta])
                <div class="card-dark card-dark-hover reveal relative overflow-hidden p-8">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-lion-400/10 blur-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl border border-lion-400/20 bg-lion-400/10"><x-icon :name="$icon" class="text-2xl" /></span>
                            <span class="font-display text-sm font-semibold text-white/25">0{{ $i + 1 }}</span>
                        </div>
                        <h3 class="mt-6 font-display text-2xl font-semibold">{{ $title }}</h3>
                        <p class="mt-3 text-white/60">{{ $text }}</p>
                        <a href="{{ $url }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-lion-300 hover:text-lion-200">{{ $cta }} <x-icon name="arrow-right" /></a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= FRANJA CÁTEDRA ================= --}}
    <section class="reveal relative overflow-hidden">
        <img src="{{ asset('images/catedra-banner.jpg') }}" alt="Cátedra UNINAVARRA en el auditorio" loading="lazy"
             class="h-32 w-full object-cover sm:h-44 lg:h-auto lg:max-h-60">
        <div class="absolute inset-0 bg-gradient-to-r from-ink-950 via-ink-950/10 to-ink-950"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/40 via-transparent to-ink-950/40"></div>
    </section>

    {{-- ================= CRONOGRAMA ================= --}}
    @if ($edition && $edition->milestones->isNotEmpty())
        <section class="relative overflow-hidden py-20">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-transparent via-volt-600/[.07] to-transparent"></div>
            <div class="container-x grid gap-12 lg:grid-cols-12">
                <div class="reveal lg:col-span-4">
                    <div class="lg:sticky lg:top-32">
                        <span class="eyebrow"><x-icon name="route" /> Ruta {{ $edition->period_label }}</span>
                        <h2 class="section-title mt-3">Del aula al escenario</h2>
                        <p class="mt-4 text-white/60">{{ Str::limit($edition->description, 260) }}</p>
                        @if ($edition->final_location)
                            <div class="glass mt-8 space-y-2 rounded-2xl p-5 text-sm">
                                <div class="text-white/50">Gran final</div>
                                <div class="flex items-center gap-2 font-semibold"><x-icon name="calendar-days" fw /> {{ $edition->final_at?->translatedFormat('l j \d\e F · g:i a') }}</div>
                                <div class="flex items-center gap-2 text-white/70"><x-icon name="location-dot" fw /> {{ $edition->final_location }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <ol class="relative space-y-4 lg:col-span-8">
                    <span class="absolute bottom-6 left-[1.35rem] top-6 w-px bg-gradient-to-b from-lion-400 via-lion-400/40 to-transparent" aria-hidden="true"></span>
                    @foreach ($edition->milestones as $i => $m)
                        @php $st = $m->status(); @endphp
                        <li class="reveal relative flex gap-5">
                            <span @class([
                                'relative z-10 mt-5 grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 font-display text-sm font-semibold',
                                'border-lion-400 bg-lion-400 text-ink-950 ico-inherit' => $st === 'completada',
                                'border-lion-400 bg-ink-950 text-lion-300 shadow-[0_0_0_6px_rgba(245,183,10,.15)]' => $st === 'en_curso',
                                'border-white/15 bg-ink-900 text-white/50' => $st === 'pendiente',
                            ])>
                                @if ($st === 'completada')<x-icon name="check" />@else{{ $i + 1 }}@endif
                            </span>
                            <div @class(['card-dark flex-1 p-5 sm:p-6', '!border-lion-400/40 !bg-lion-400/[.06]' => $st === 'en_curso'])>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-lion-300">{{ $m->date_label }}</span>
                                    @if ($st === 'en_curso')<span class="chip border-lion-400/30 bg-lion-400/10 !py-0.5 text-lion-200"><x-icon name="circle" class="text-[6px]" /> En curso</span>@endif
                                </div>
                                <h3 class="mt-2 font-display text-lg font-semibold sm:text-xl">{{ $m->title }}</h3>
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
                <span class="eyebrow"><x-icon name="layer-group" /> Sectores</span>
                <h2 class="section-title mt-3">Encuentra lo que te mueve</h2>
            </div>
            <div class="reveal mt-10 flex flex-wrap justify-center gap-3">
                @foreach ($categories as $c)
                    <a href="{{ route('ventures.index', ['categoria' => $c->slug]) }}"
                       class="group inline-flex items-center gap-2.5 rounded-lg border border-white/10 bg-white/[.03] px-5 py-3 text-sm font-medium transition hover:-translate-y-0.5 hover:border-lion-400/40">
                        @if ($c->icon)<x-icon :name="$c->icon" />@endif
                        {{ $c->name }}
                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs font-bold text-white/80">{{ $c->ventures_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CTA INVERSIONISTAS ================= --}}
    <section class="container-x py-16">
        <div class="reveal relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-lion-300 via-lion-500 to-lion-700 p-8 text-ink-950 sm:p-14">
            <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/30 blur-3xl"></div>
            <i class="fa-solid fa-handshake pointer-events-none absolute -bottom-8 right-6 text-[12rem] text-ink-950/10" aria-hidden="true"></i>
            <div class="relative grid items-center gap-8 lg:grid-cols-5">
                <div class="lg:col-span-3">
                    <h2 class="font-display text-4xl font-semibold leading-[1.05] tracking-tight sm:text-6xl">¿Eres inversionista, empresario o mentor?</h2>
                    <p class="mt-4 max-w-xl text-lg text-ink-950/75">Conecta con el talento emprendedor del sur colombiano. Registra tu intención de inversión, donación, mentoría o alianza y te ponemos en contacto con los equipos.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:col-span-2 lg:justify-end">
                    <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn bg-ink-950 px-7 py-3.5 text-base text-white hover:bg-ink-800">Registrarme como aliado</a>
                    <a href="https://wa.me/{{ config('catedra.contact.whatsapp') }}" target="_blank" rel="noopener" class="btn border border-ink-950/20 px-7 py-3.5 text-base hover:bg-ink-950/10"><x-icon name="whatsapp" style="brands" /> Hablar con la Cátedra</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= EDICIONES ANTERIORES ================= --}}
    @if ($pastEditions->isNotEmpty())
        <section class="container-x py-16">
            <div class="flex items-end justify-between gap-6">
                <div class="reveal">
                    <span class="eyebrow"><x-icon name="clock-rotate-left" /> Historia</span>
                    <h2 class="section-title mt-3">Ediciones anteriores</h2>
                </div>
                <a href="{{ route('editions.index') }}" class="reveal hidden items-center gap-2 text-sm font-semibold text-lion-300 sm:inline-flex">Ver todas <x-icon name="arrow-right" /></a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($pastEditions as $e)
                    <a href="{{ route('editions.show', $e) }}" class="card-dark card-dark-hover reveal group p-7">
                        <div class="font-display text-6xl font-semibold text-white/10 transition group-hover:text-lion-400/30">{{ $e->period_label }}</div>
                        <h3 class="mt-4 font-display text-xl font-semibold">{{ $e->name }}</h3>
                        <p class="mt-1 text-sm text-white/55">{{ $e->tagline }}</p>
                        <p class="mt-6 inline-flex items-center gap-2 text-sm text-white/70">{{ $e->ventures_count }} emprendimientos <x-icon name="arrow-right" /></p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.site>
