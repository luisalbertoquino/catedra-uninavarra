<x-layouts.site title="Portafolios" description="Portafolios de los emprendimientos de cada convocatoria de Cátedra UNINAVARRA, en PDF y en la web.">
    <section class="ecos-soft-azul ecos-band pb-12 pt-32 lg:pt-36">
        <div class="container-x">
            <span class="eyebrow"><x-icon name="book-open" /> Cátedra UNINAVARRA</span>
            <h1 class="mt-3 page-title">Portafolios</h1>
            <p class="mt-4 max-w-2xl text-lg text-white/60">El resumen de los emprendimientos de cada convocatoria: la idea, el equipo, la misión, la visión y lo que necesitan para crecer.</p>
        </div>
    </section>

    <section class="container-x space-y-6 pt-12">
        @forelse ($portfolios as $p)
            <article @class(['card-dark reveal grid gap-6 p-6 sm:p-8 lg:grid-cols-12 lg:items-center', 'ring-2 ring-lion-400' => $p->is_featured])>
                <div class="lg:col-span-3">
                    <div class="font-display text-6xl font-medium text-lion-300 sm:text-7xl">{{ $p->period ?: '—' }}</div>
                    @if ($p->is_featured)<span class="chip mt-2 border-lion-400/30 bg-lion-400/10 text-lion-100"><x-icon name="star" /> Convocatoria actual</span>@endif
                </div>
                <div class="lg:col-span-5">
                    <h2 class="font-display text-2xl font-semibold">{{ $p->title }}</h2>
                    @if ($p->description)<p class="mt-2 text-white/60">{{ $p->description }}</p>@endif
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:col-span-4 lg:flex-col lg:items-stretch">
                    @if ($p->pdf_url)
                        <a href="{{ $p->pdf_url }}" target="_blank" rel="noopener" class="btn-lion px-6 py-3"><x-icon name="file-pdf" class="!text-white" /> Ver PDF</a>
                    @endif
                    @if ($p->url)
                        <a href="{{ $p->url }}" target="_blank" rel="noopener" class="btn-ghost bg-white px-6 py-3"><x-icon name="arrow-up-right-from-square" /> Ver portafolio web</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="py-16 text-center text-white/50">Aún no hay portafolios publicados.</p>
        @endforelse
    </section>
</x-layouts.site>
