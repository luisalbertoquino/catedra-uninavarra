<x-layouts.site title="Ediciones">
    <section class="ecos-soft-azul ecos-band pb-12 pt-32 lg:pt-36">
        <div class="container-x">
            <span class="eyebrow"><x-icon name="clock-rotate-left" /> Historia</span>
            <h1 class="mt-3 page-title">Ediciones de la Cátedra</h1>
            <p class="mt-4 max-w-2xl text-lg text-white/60">Cada semestre, nuevos equipos Navarristas llevan sus ideas del aula al escenario del LION HUB EVENT.</p>
        </div>
    </section>

    <section class="container-x space-y-6 pt-12">
        @foreach ($editions as $e)
            <a href="{{ route('editions.show', $e) }}" class="card-dark card-dark-hover reveal group grid gap-6 overflow-hidden p-6 sm:p-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-3">
                    <div class="font-display text-6xl font-medium text-lion-300 transition group-hover:text-lion-500 sm:text-7xl">{{ $e->period_label }}</div>
                    @if ($e->is_active)<span class="chip mt-2 border-lion-400/30 bg-lion-400/10 text-lion-100"><x-icon name="circle-check" /> Edición actual</span>@endif
                </div>
                <div class="lg:col-span-5">
                    <h2 class="font-display text-2xl font-semibold group-hover:text-lion-300">{{ $e->name }}</h2>
                    <p class="mt-1 text-white/55">{{ $e->event_name }} · {{ $e->tagline }}</p>
                    <div class="mt-4 flex gap-6 text-sm text-white/70">
                        <span><b class="text-white">{{ $e->ventures_count }}</b> emprendimientos</span>
                        <span><b class="text-white">{{ number_format($e->votes_count, 0, ',', '.') }}</b> votos</span>
                    </div>
                </div>
                <div class="lg:col-span-4">
                    @if ($e->results_published && $e->ventures->isNotEmpty())
                        <div class="space-y-2">
                            @foreach ($e->ventures->take(3) as $w)
                                <div class="flex items-center gap-3 rounded-xl bg-white/[.04] px-3 py-2 text-sm">
                                    <x-icon :name="$w->final_position === 1 ? 'trophy' : 'medal'" fw /><span class="text-xs font-bold text-white/50">{{ $w->final_position }}º</span>
                                    <span class="truncate font-medium">{{ $w->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-white/45">Resultados por anunciar</p>
                    @endif
                </div>
            </a>
        @endforeach
    </section>
</x-layouts.site>
