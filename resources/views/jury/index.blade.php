<x-layouts.jury title="Emprendimientos por evaluar">
    <section class="container-x pt-10">
        @if (! $edition)
            <div class="card-dark mx-auto mt-10 max-w-lg p-12 text-center">
                <x-icon name="calendar-xmark" class="text-3xl" />
                <h1 class="mt-4 font-display text-2xl font-semibold">No hay una edición activa</h1>
                <p class="mt-2 text-white/60">Cuando la Cátedra active la próxima edición, aquí verás los emprendimientos por evaluar.</p>
            </div>
        @else
            @php
                $done = $mine->count();
                $pct = $total ? round($done / $total * 100) : 0;
            @endphp
            <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                <div>
                    <span class="eyebrow"><x-icon name="gavel" /> {{ $edition->name }}</span>
                    <h1 class="mt-3 page-title">Evaluación del comité</h1>
                    <p class="mt-3 max-w-2xl text-white/60">
                        Mira el video pitch de cada equipo y califícalo con la rúbrica. Puedes ajustar tus calificaciones hasta que se publiquen los resultados.
                        @if (auth()->user()->is_expert)
                            <span class="text-lion-300">Como {{ $edition->expert_title }}, tu evaluación {{ $edition->expertWeightText() }}.</span>
                        @endif
                    </p>
                </div>
                <div class="card-dark w-full p-5 lg:w-80">
                    <div class="flex items-end justify-between">
                        <div class="text-sm text-white/60">Tu avance</div>
                        <div class="font-display text-3xl font-semibold">{{ $done }}<span class="text-lg text-white/40">/{{ $total }}</span></div>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-lion-400 to-lion-600" style="width: {{ $pct }}%"></div>
                    </div>
                    @unless ($edition->isJuryOpen())
                        <p class="mt-3 text-xs text-lion-200"><x-icon name="lock" /> La evaluación está cerrada.</p>
                    @endunless
                </div>
            </div>

            @if ($edition->criteria->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-2">
                    <span class="text-sm text-white/50">Rúbrica:</span>
                    @foreach ($edition->criteria as $c)
                        <span class="chip">{{ $c->name }} · {{ $c->weight }}%</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-8 inline-flex rounded-xl border border-white/10 bg-white/[.03] p-1 text-sm">
                @foreach ([null => 'Todos', 'pendientes' => 'Pendientes', 'evaluados' => 'Evaluados'] as $key => $label)
                    <a href="{{ route('jury.index', array_filter(['estado' => $key])) }}"
                       @class(['rounded-lg px-4 py-2 font-semibold transition', 'bg-lion-400 text-ink-950' => $filter == $key, 'text-white/70 hover:text-white' => $filter != $key])>{{ $label }}</a>
                @endforeach
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($ventures as $v)
                    @php $ev = $mine->get($v->id); @endphp
                    <a href="{{ route('jury.show', $v) }}" class="card-dark card-dark-hover group flex flex-col overflow-hidden">
                        <div class="relative aspect-video overflow-hidden">
                            @if ($v->cover_url)
                                <img src="{{ $v->cover_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="h-full w-full bg-gradient-to-br from-volt-600/40 to-ink-800"></div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/20 to-transparent"></div>
                            <span class="absolute inset-0 grid place-items-center opacity-0 transition group-hover:opacity-100">
                                <span class="grid h-14 w-14 place-items-center rounded-full bg-lion-400/90 text-ink-950"><i class="fa-solid fa-play text-xl" aria-hidden="true"></i></span>
                            </span>
                            <span @class(['absolute right-3 top-3 rounded-md px-2.5 py-1 text-xs font-semibold', 'bg-lion-400 text-ink-950' => $ev, 'bg-ink-950/80 text-white/80' => ! $ev])>
                                @if ($ev)<i class="fa-solid fa-check" aria-hidden="true"></i> Nota {{ number_format($ev->total_score / 20, 1, ',', '.') }} @else Pendiente @endif
                            </span>
                            @unless ($v->video_embed_url)
                                <span class="absolute bottom-3 left-3 rounded-md bg-ink-950/80 px-2 py-1 text-xs text-lion-200"><x-icon name="video-slash" /> Sin video</span>
                            @endunless
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="font-display text-xl font-semibold group-hover:text-lion-300">{{ $v->name }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-white/55">{{ $v->tagline }}</p>
                            <span class="mt-auto inline-flex items-center gap-2 pt-4 text-sm font-semibold text-lion-300">
                                {{ $ev ? 'Revisar evaluación' : 'Evaluar' }} <x-icon name="arrow-right" />
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full py-16 text-center text-white/50">
                        {{ $filter === 'pendientes' ? '¡Terminaste! No tienes emprendimientos pendientes.' : 'No hay emprendimientos en esta vista.' }}
                    </p>
                @endforelse
            </div>
        @endif
    </section>
</x-layouts.jury>
