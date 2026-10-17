<x-layouts.site :title="$edition->name" :description="$edition->tagline" :image="$edition->banner_url">
    <section class="surface-dark isolate bg-ink-950 text-white relative overflow-hidden pb-14 pt-32 lg:pt-40">
        @if ($edition->banner_url)
            <img src="{{ $edition->banner_url }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover opacity-30">
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-b from-ink-950/40 to-ink-950"></div>
        <div class="container-x">
            <a href="{{ route('editions.index') }}" class="inline-flex items-center gap-2 text-sm text-white/50 hover:text-white"><x-icon name="arrow-left" /> Todas las ediciones</a>
            <div class="mt-6 font-display text-7xl font-semibold text-white/10 sm:text-9xl">{{ $edition->period_label }}</div>
            <h1 class="-mt-6 page-title sm:-mt-10">{{ $edition->name }}</h1>
            <p class="mt-3 text-xl text-lion-300">{{ $edition->event_name }} · {{ $edition->tagline }}</p>
            @if ($edition->description)<p class="mt-5 max-w-3xl text-white/65">{{ $edition->description }}</p>@endif
            <div class="mt-8 flex flex-wrap gap-3 text-sm">
                <span class="chip !px-4 !py-2"><x-icon name="rocket" /> {{ $ventures->count() }} emprendimientos</span>
                <span class="chip !px-4 !py-2"><x-icon name="check-to-slot" /> {{ number_format($edition->votes_count, 0, ',', '.') }} votos</span>
                @if ($edition->final_at)<span class="chip !px-4 !py-2"><x-icon name="flag-checkered" /> Final: {{ $edition->final_at->translatedFormat('j \d\e F Y') }}</span>@endif
                <a href="{{ route('ranking', ['edicion' => $edition->slug]) }}" class="chip !px-4 !py-2 border-lion-400/40 text-lion-200 hover:bg-lion-400/10"><x-icon name="ranking-star" /> Ver ranking</a>
            </div>
        </div>
    </section>

    <section class="container-x pt-12">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($ventures as $v)
                <x-venture-card :venture="$v" class="reveal" />
            @endforeach
        </div>
    </section>
</x-layouts.site>
