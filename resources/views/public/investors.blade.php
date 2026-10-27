<x-layouts.site title="Inversionistas y aliados" description="Apoya a los emprendimientos de los estudiantes Navarristas con inversión, asesorías, infraestructura, equipos, contactos o mercadeo.">
    {{-- ================= ENCABEZADO ================= --}}
    <section class="ecos-g-coral ecos-band pb-16 pt-28 lg:pb-24 lg:pt-36">
        <div class="container-x grid items-end gap-8 lg:grid-cols-12">
            <div class="tarjeta-ecos animate-fade-up lg:col-span-8">
                <span class="tarjeta-ecos__tab"><x-icon name="handshake" /> Inversionistas y aliados</span>
                <div class="tarjeta-ecos__body">
                    <h1 class="page-title">Apoya el talento que emprende en el Huila</h1>
                    <div class="divisor-ecos"></div>
                    <p class="max-w-2xl text-lg text-ink-700">No tienes que invertir dinero para hacer la diferencia: un espacio, una asesoría, un contacto o una publicación en tus redes pueden impulsar a un equipo. Tú eliges cómo ayudar y la Cátedra te conecta con ellos.</p>
                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        @guest
                            <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn-lion px-7 py-3.5 text-base">Registrarme como aliado <x-icon name="arrow-right" /></a>
                            <a href="#emprendimientos" class="btn-ghost px-7 py-3.5 text-base">Ver a quién apoyar</a>
                        @else
                            <a href="#emprendimientos" class="btn-lion px-7 py-3.5 text-base">Elegir un emprendimiento <x-icon name="arrow-down" /></a>
                        @endguest
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 lg:col-span-4 lg:grid-cols-1">
                @foreach ([['rocket', $stats['ventures'], 'emprendimientos buscan apoyo'], ['hand-holding-heart', $stats['pledges'], 'intenciones de apoyo'], ['circle-check', $stats['completed'], 'apoyos concretados']] as [$icon, $n, $label])
                    <div class="rounded-ecos bg-white/85 p-4 backdrop-blur sm:p-5">
                        <div class="font-display text-3xl font-medium text-lion-700 sm:text-4xl">{{ number_format($n, 0, ',', '.') }}</div>
                        <div class="mt-1 text-xs text-ink-600 sm:text-sm"><x-icon :name="$icon" /> {{ $label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= FORMAS DE APOYO ================= --}}
    <section class="container-x py-20">
        <div class="reveal max-w-2xl">
            <span class="eyebrow"><x-icon name="layer-group" /> Formas de apoyo</span>
            <h2 class="section-title mt-3">¿Qué puedes aportar?</h2>
            <p class="mt-4 text-ink-600">Elige una o varias. Al registrar tu intención marcas todo lo que puedes ofrecer y, si es inversión o donación, un monto aproximado.</p>
        </div>
        <x-support-types class="reveal mt-10" />
    </section>

    {{-- ================= CÓMO FUNCIONA ================= --}}
    <section class="ecos-g-profundo py-20">
        <div class="container-x">
            <div class="surface-dark reveal mx-auto max-w-2xl text-center text-white">
                <span class="text-sm font-semibold uppercase tracking-[.06em] text-white">Paso a paso</span>
                <h2 class="mt-3 font-display text-4xl font-medium sm:text-5xl">Así funciona</h2>
            </div>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['magnifying-glass', 'Elige un emprendimiento', 'Mira su video pitch, el equipo, lo que necesitan y cuánto requieren para arrancar.'],
                    ['clipboard-list', 'Registra tu intención', 'Marca las formas de apoyo y deja un mensaje. No hay cobros ni pagos en línea.'],
                    ['people-arrows', 'Te conectamos', 'El '.\App\Models\Setting::get('investment_contact_name').' te contacta y te reúne con el equipo. Cuando el apoyo se concreta, apareces en el ranking de aliados.'],
                ] as $i => [$icon, $title, $text])
                    <div class="tarjeta-ecos reveal">
                        <span class="tarjeta-ecos__tab">Paso 0{{ $i + 1 }}</span>
                        <div class="tarjeta-ecos__body h-full">
                            <span class="grid h-12 w-12 place-items-center rounded-full bg-white text-xl"><x-icon :name="$icon" /></span>
                            <h3 class="mt-4 font-display text-2xl text-lion-700">{{ $title }}</h3>
                            <p class="mt-2 text-ink-600">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= EMPRENDIMIENTOS ================= --}}
    <section id="emprendimientos" class="container-x scroll-mt-28 py-20">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <div class="reveal">
                <span class="eyebrow"><x-icon name="seedling" /> {{ $edition?->period_label }}</span>
                <h2 class="section-title mt-3">Equipos que buscan aliados</h2>
            </div>
            <a href="{{ route('ventures.index') }}" class="btn-ghost reveal self-start sm:self-auto">Ver todos <x-icon name="arrow-right" /></a>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($ventures as $venture)
                <div class="reveal flex flex-col">
                    <x-venture-card :venture="$venture" class="flex-1" />
                    <a href="{{ route('ventures.show', $venture) }}#apoyar" class="btn-lion mt-3 w-full">
                        <x-icon name="hand-holding-heart" /> Apoyar a {{ Str::limit($venture->name, 22) }}
                        @if ($venture->funding_goal || $venture->startup_amount)
                            <span class="rounded-full bg-ink-950/30 px-2 py-0.5 text-xs">${{ number_format($venture->funding_goal ?: $venture->startup_amount, 0, ',', '.') }}</span>
                        @endif
                    </a>
                </div>
            @empty
                <p class="text-ink-600">Pronto publicaremos los emprendimientos de la próxima convocatoria.</p>
            @endforelse
        </div>
    </section>

    {{-- ================= RANKING DE ALIADOS + CONTACTO ================= --}}
    <section class="ecos-soft-azul py-20">
        <div class="container-x grid gap-10 lg:grid-cols-2 [&>*]:min-w-0">
            <div class="reveal">
                <span class="eyebrow"><x-icon name="trophy" /> Reconocimiento</span>
                <h2 class="section-title mt-3">Ranking de aliados</h2>
                <p class="mt-4 text-ink-600">Quienes concretan su apoyo aparecen aquí (si lo autorizan), como reconocimiento de la Cátedra.</p>
                <div class="mt-6 space-y-2">
                    @forelse ($investors as $i => $inv)
                        <div class="flex items-center gap-4 rounded-ecos bg-white p-4">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-lion-600 font-semibold text-white">{{ $i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-semibold text-ink-900">{{ $inv->organization ?: $inv->name }}</div>
                                <div class="text-xs text-ink-500">{{ $inv->completed_count }} apoyo(s) · {{ $inv->ventures_count }} emprendimiento(s)</div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-ecos bg-white p-6 text-ink-600"><x-icon name="star" /> Aún no hay apoyos concretados en esta edición. ¡El primer lugar puede ser tuyo!</div>
                    @endforelse
                </div>
                <a href="{{ route('ranking', ['vista' => 'inversionistas']) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-lion-700 hover:underline">Ver el ranking completo <x-icon name="arrow-right" /></a>
            </div>
            <div class="reveal">
                <div class="tarjeta-ecos">
                    <span class="tarjeta-ecos__tab"><x-icon name="headset" /> ¿Tienes preguntas?</span>
                    <div class="tarjeta-ecos__body">
                        <h3 class="font-display text-3xl text-lion-700">Hablemos</h3>
                        <p class="mt-2 text-ink-600">Si prefieres conversar antes de registrarte, el equipo de gestión de inversiones te atiende.</p>
                        <x-investment-contact class="mt-5" />
                        @guest
                            <a href="{{ route('register', ['perfil' => 'inversionista']) }}" class="btn-lion mt-6 w-full py-3.5 text-base">Registrarme como aliado <x-icon name="arrow-right" /></a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.site>
