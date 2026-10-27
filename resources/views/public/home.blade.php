<x-layouts.site>
    {{-- ================= PORTADA: degradado violeta + banda de píldoras + tarjeta escalonada ================= --}}
    <section class="ecos-g-violeta ecos-band flex min-h-[92svh] items-center pb-16 pt-28 lg:pt-32">
        <div class="container-x">
            <div class="tarjeta-ecos mx-auto max-w-5xl animate-fade-up">
                <span class="tarjeta-ecos__tab"><x-icon name="bolt" /> {{ $edition?->event_name ?? 'LION HUB EVENT' }} · {{ $edition?->period_label }}</span>
                <div class="tarjeta-ecos__body sm:p-10 lg:p-14">
                    <h1 class="hero-title text-lion-700">{!! \App\Models\Setting::headline('hero_title') !!}</h1>
                    <div class="divisor-ecos"></div>
                    <p class="max-w-3xl text-lg leading-relaxed text-lion-700/85 sm:text-xl">
                        {{ str_replace('{lema}', $edition?->tagline ?? 'la Cátedra', \App\Models\Setting::get('hero_quote')) }}
                    </p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('ventures.index') }}" class="btn-lion px-7 py-3.5 text-base">Explorar y votar <x-icon name="arrow-right" /></a>
                        <a href="{{ route('investors') }}" class="btn-ghost px-7 py-3.5 text-base"><x-icon name="hand-holding-heart" /> Quiero apoyar</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= CIFRAS ================= --}}
    <section class="container-x -mt-10 pb-6">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['ventures', 'Emprendimientos en competencia', 'rocket'],
                ['votes', 'Votos de la comunidad', 'check-to-slot'],
                ['community', 'Personas registradas', 'users'],
                ['pledges', 'Intenciones de apoyo', 'handshake'],
            ] as [$key, $label, $icon])
                <div class="reveal flex items-center justify-between gap-4 rounded-ecos bg-white p-5 shadow-xl shadow-lion-900/10 sm:p-6"
                     x-data="counter({{ $stats[$key] }})" x-intersect:enter.half="run()" x-intersect:leave="reset()">
                    <div class="flex min-w-0 flex-col gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-tarjeta"><x-icon :name="$icon" class="text-lg" /></span>
                        <span class="text-sm leading-snug text-ink-600">{{ $label }}</span>
                    </div>
                    <div class="countdown-num shrink-0 font-display text-5xl font-medium leading-none tabular-nums sm:text-6xl"
                         x-text="formatted" aria-label="{{ $stats[$key] }}">{{ number_format($stats[$key], 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= DESTACADOS ================= --}}
    @if ($featured->isNotEmpty())
        <section class="container-x py-16">
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
    <section class="ecos-g-menta ecos-band py-20">
        <div class="container-x">
            <div class="reveal mx-auto max-w-2xl rounded-ecos bg-white/85 px-6 py-6 text-center backdrop-blur">
                <span class="eyebrow"><x-icon name="user-group" /> Tu papel</span>
                <h2 class="section-title mt-2">Tres formas de hacer parte</h2>
                <p class="mt-3 text-ink-700">La comunidad no solo mira: decide. Los votos pesan el {{ $edition?->community_weight ?? 30 }}% de la nota que define a los finalistas.</p>
            </div>
            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ([
                    ['check-to-slot', 'Vota', 'Regístrate, califica con estrellas, deja tu comentario y da tu voto. Hay un voto por persona.', route('ventures.index'), 'Ir a votar'],
                    ['comments', 'Opina', 'Tu retroalimentación llega al equipo y le ayuda a mejorar su pitch antes de la gran final.', route('ranking'), 'Ver ranking'],
                    ['hand-holding-heart', 'Apoya', 'Inversión, asesorías, espacios, equipos, contactos o difusión en redes: todo suma.', route('investors'), 'Cómo apoyar'],
                ] as $i => [$icon, $title, $text, $url, $cta])
                    <div class="tarjeta-ecos reveal flex flex-col">
                        <span class="tarjeta-ecos__tab self-start">0{{ $i + 1 }} · {{ $title }}</span>
                        <div class="tarjeta-ecos__body flex flex-1 flex-col">
                            <span class="grid h-14 w-14 place-items-center rounded-full bg-white text-2xl"><x-icon :name="$icon" /></span>
                            <p class="mt-5 flex-1 text-ink-700">{{ $text }}</p>
                            <a href="{{ $url }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-lion-700 hover:underline">{{ $cta }} <x-icon name="arrow-right" /></a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= VIDEO PROMOCIONAL (se carga al pulsar) ================= --}}
    @if ($videoId = $edition?->promo_video_id)
        <section class="container-x py-20">
            <div class="grid items-center gap-10 lg:grid-cols-12">
                <div class="reveal lg:col-span-4">
                    <span class="eyebrow"><x-icon name="circle-play" /> La Cátedra en video</span>
                    <h2 class="section-title mt-3">Así se vive el {{ $edition->event_name ?? 'LION HUB EVENT' }}</h2>
                    <p class="mt-4 text-ink-600">{{ Str::limit($edition->description, 200) }}</p>
                </div>
                <div class="reveal lg:col-span-8" x-data="{ play: false }">
                    <div class="relative aspect-video overflow-hidden rounded-ecos bg-lion-900 shadow-2xl shadow-lion-900/20">
                        <template x-if="play">
                            <iframe src="https://www.youtube-nocookie.com/embed/{{ $videoId }}?autoplay=1&rel=0" title="Video promocional de la Cátedra UNINAVARRA"
                                    class="absolute inset-0 h-full w-full" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>
                        </template>
                        <button type="button" x-show="!play" @click="play = true" class="group absolute inset-0" aria-label="Reproducir video">
                            <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg" alt="" loading="lazy" class="h-full w-full object-cover">
                            <span class="absolute inset-0 bg-gradient-to-t from-lion-900/60 to-transparent"></span>
                            <span class="absolute left-1/2 top-1/2 grid h-20 w-20 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-white text-3xl text-lion-600 shadow-2xl transition group-hover:scale-110"><i class="fa-solid fa-play ml-1" aria-hidden="true"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ================= EMBUDO: INVERSIONISTAS ================= --}}
    <section class="ecos-g-coral ecos-band py-20">
        <div class="container-x grid items-center gap-8 lg:grid-cols-12">
            <div class="tarjeta-ecos reveal lg:col-span-5">
                <span class="tarjeta-ecos__tab"><x-icon name="briefcase" /> Inversionistas y aliados</span>
                <div class="tarjeta-ecos__body">
                    <h2 class="font-display text-4xl font-medium leading-tight text-lion-700 sm:text-5xl">Tu apoyo puede ser el empujón que les falta</h2>
                    <div class="divisor-ecos"></div>
                    <p class="text-ink-700">No solo con dinero: infraestructura, asesorías, materia prima, equipos, herramientas, contactos, suministros o mercadeo en tus redes. Registras tu intención y la Cátedra te conecta con el equipo.</p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('investors') }}" class="btn-lion px-6 py-3">Quiero apoyar <x-icon name="arrow-right" /></a>
                        <a href="https://wa.me/{{ \App\Models\Setting::whatsapp('investment_contact_phone') }}" target="_blank" rel="noopener" class="btn-ghost px-6 py-3"><x-icon name="whatsapp" style="brands" /> Hablar con la Cátedra</a>
                    </div>
                </div>
            </div>
            <x-support-types compact class="reveal lg:col-span-7 [&>div]:bg-white/90 [&>div]:backdrop-blur" />
        </div>
    </section>

    {{-- ================= CRONOGRAMA ================= --}}
    @if ($edition && $edition->milestones->isNotEmpty())
        <section class="ecos-soft-azul py-20">
            <div class="container-x grid gap-12 lg:grid-cols-12">
                <div class="reveal lg:col-span-4">
                    <div class="lg:sticky lg:top-32">
                        <span class="eyebrow"><x-icon name="route" /> Ruta {{ $edition->period_label }}</span>
                        <h2 class="section-title mt-3">Del aula al escenario</h2>
                        <p class="mt-4 text-ink-600">{{ Str::limit($edition->description, 260) }}</p>
                        @if ($edition->final_location)
                            <div class="mt-8 space-y-2 rounded-ecos bg-white p-5 text-sm">
                                <div class="text-xs font-semibold uppercase tracking-wider text-lion-600">Gran final</div>
                                <div class="flex items-center gap-2 font-semibold text-ink-900"><x-icon name="calendar-days" fw /> {{ $edition->final_at?->translatedFormat('l j \d\e F · g:i a') }}</div>
                                <div class="flex items-center gap-2 text-ink-600"><x-icon name="location-dot" fw /> {{ $edition->final_location }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <ol class="relative space-y-4 lg:col-span-8">
                    <span class="absolute bottom-6 left-[1.35rem] top-6 w-0.5 rounded-full bg-gradient-to-b from-volt-500 via-lion-400 to-naranja-500" aria-hidden="true"></span>
                    @foreach ($edition->milestones as $i => $m)
                        @php $st = $m->status(); @endphp
                        <li class="reveal relative flex gap-5">
                            <span @class([
                                'relative z-10 mt-5 grid h-11 w-11 shrink-0 place-items-center rounded-full border-2 font-display text-sm font-semibold',
                                'border-lion-500 bg-lion-600 text-white ico-inherit' => $st === 'completada',
                                'border-lion-500 bg-white text-lion-600 shadow-[0_0_0_6px_rgba(168,85,247,.18)]' => $st === 'en_curso',
                                'border-ink-200 bg-white text-ink-600' => $st === 'pendiente',
                            ])>
                                @if ($st === 'completada')<x-icon name="check" />@else{{ $i + 1 }}@endif
                            </span>
                            <div @class(['flex-1 rounded-ecos bg-white p-5 sm:p-6', 'ring-2 ring-lion-400' => $st === 'en_curso'])>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-lion-600">{{ $m->date_label }}</span>
                                    @if ($st === 'en_curso')<span class="rounded-full bg-lion-100 px-2.5 py-0.5 text-xs font-semibold text-lion-700"><x-icon name="circle" class="text-[6px]" /> En curso</span>@endif
                                </div>
                                <h3 class="mt-2 font-display text-lg font-medium text-ink-900 sm:text-xl">{{ $m->title }}</h3>
                                @if ($m->description)<p class="mt-1.5 text-sm text-ink-600">{{ $m->description }}</p>@endif
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
                       class="inline-flex items-center gap-2.5 rounded-full bg-tarjeta px-5 py-3 text-sm font-medium text-ink-800 transition hover:-translate-y-0.5 hover:bg-lion-100">
                        @if ($c->icon)<x-icon :name="$c->icon" />@endif
                        {{ $c->name }}
                        <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-lion-700">{{ $c->ventures_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= EDICIONES ANTERIORES ================= --}}
    @if ($pastEditions->isNotEmpty())
        <section class="container-x py-12">
            <div class="flex items-end justify-between gap-6">
                <div class="reveal">
                    <span class="eyebrow"><x-icon name="clock-rotate-left" /> Historia</span>
                    <h2 class="section-title mt-3">Ediciones anteriores</h2>
                </div>
                <a href="{{ route('editions.index') }}" class="reveal hidden items-center gap-2 text-sm font-semibold text-lion-700 sm:inline-flex">Ver todas <x-icon name="arrow-right" /></a>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($pastEditions as $e)
                    <a href="{{ route('editions.show', $e) }}" class="reveal group rounded-ecos bg-tarjeta p-7 transition hover:-translate-y-1 hover:bg-lion-50">
                        <div class="font-display text-6xl font-medium text-lion-300 transition group-hover:text-lion-500">{{ $e->period_label }}</div>
                        <h3 class="mt-4 font-display text-xl font-medium text-ink-900">{{ $e->name }}</h3>
                        <p class="mt-1 text-sm text-ink-600">{{ $e->tagline }}</p>
                        <p class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-lion-700">{{ $e->ventures_count }} emprendimientos <x-icon name="arrow-right" /></p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ================= CIERRE ================= --}}
    <section class="container-x pt-8">
        <div class="ecos-g-profundo reveal rounded-ecos px-6 py-14 text-center sm:px-14">
            <div class="surface-dark text-white">
                <h2 class="mx-auto max-w-3xl font-display text-4xl font-medium leading-tight sm:text-5xl">Haz que una idea ruja</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-white">Vota por tu favorito o apoya a un equipo para que llegue más lejos.</p>
                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <a href="{{ route('ventures.index') }}" class="btn-white px-7 py-3.5 text-base">Explorar y votar <x-icon name="arrow-right" /></a>
                    <a href="{{ route('investors') }}" class="btn border border-white/60 px-7 py-3.5 text-base text-white hover:bg-white/15"><x-icon name="hand-holding-heart" /> Quiero apoyar</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
