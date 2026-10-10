@props(['venture', 'rank' => null, 'voted' => false])
<article {{ $attributes->merge(['class' => 'group card-dark card-dark-hover relative flex flex-col overflow-hidden']) }}>
    <a href="{{ route('ventures.show', $venture) }}" class="absolute inset-0 z-10" aria-label="Ver {{ $venture->name }}"></a>

    <div class="relative aspect-[16/10] overflow-hidden">
        @if ($venture->cover_url)
            <img src="{{ $venture->cover_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
        @else
            <div class="h-full w-full bg-gradient-to-br from-volt-600/60 via-ink-800 to-lion-500/40"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/30 to-transparent"></div>

        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($venture->final_position === 1)
                <span class="chip border-lion-300/50 bg-lion-400 font-bold text-ink-950">🏆 Ganador</span>
            @elseif ($venture->final_position)
                <span class="chip border-lion-300/40 bg-ink-950/70 text-lion-200">🥇 Puesto {{ $venture->final_position }}</span>
            @elseif ($venture->is_finalist)
                <span class="chip border-lion-300/40 bg-ink-950/70 text-lion-200">⭐ Finalista</span>
            @endif
            @if ($venture->category)
                <span class="chip bg-ink-950/60 backdrop-blur">{{ $venture->category->icon }} {{ $venture->category->name }}</span>
            @endif
        </div>

        @if ($rank)
            <span class="absolute right-3 top-3 grid h-9 w-9 place-items-center rounded-full bg-white font-display text-sm font-bold text-ink-950 shadow-lg">#{{ $rank }}</span>
        @endif

        <div class="absolute bottom-3 left-4 flex items-center gap-3">
            @if ($venture->logo_url)
                <img src="{{ $venture->logo_url }}" alt="" class="h-12 w-12 rounded-2xl border border-white/20 bg-white object-contain p-1">
            @else
                <span class="grid h-12 w-12 place-items-center rounded-2xl border border-white/20 bg-ink-950/70 font-display text-lg font-bold text-lion-300 backdrop-blur">{{ $venture->initials }}</span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-xl font-bold leading-tight text-white group-hover:text-lion-300">{{ $venture->name }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-white/60">{{ $venture->tagline }}</p>

        <div class="mt-auto flex items-center justify-between pt-5 text-sm">
            <span @class(['inline-flex items-center gap-1.5 font-semibold', 'text-lion-300' => $voted, 'text-white/80' => ! $voted])>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $voted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path stroke-linejoin="round" d="M12 21s-7.5-4.6-9.5-9.3C1.1 8.4 3.2 4.5 7 4.5c2 0 3.6 1.1 5 2.9 1.4-1.8 3-2.9 5-2.9 3.8 0 5.9 3.9 4.5 7.2C19.5 16.4 12 21 12 21z"/></svg>
                {{ number_format($venture->votes_count ?? 0, 0, ',', '.') }} votos
            </span>
            @isset($venture->comments_count)
                <span class="inline-flex items-center gap-1.5 text-white/50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linejoin="round" d="M4 5h16v11H8l-4 4V5z"/></svg>
                    {{ $venture->comments_count }}
                </span>
            @endisset
            <span class="inline-flex items-center gap-1 font-medium text-white/40 transition group-hover:translate-x-1 group-hover:text-lion-300">Ver más →</span>
        </div>
    </div>
</article>
