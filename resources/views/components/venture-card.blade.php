@props(['venture', 'rank' => null, 'voted' => false])
<article {{ $attributes->merge(['class' => 'group card-dark card-dark-hover relative flex flex-col overflow-hidden']) }}>
    <a href="{{ route('ventures.show', $venture) }}" class="absolute inset-0 z-10" aria-label="Ver {{ $venture->name }}"></a>

    <div class="relative aspect-[16/10] overflow-hidden">
        @php $logoAsCover = ! $venture->cover_url && $venture->logo_url; @endphp
        @if ($venture->cover_url)
            <img src="{{ $venture->cover_url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/30 to-transparent"></div>
        @elseif ($logoAsCover)
            {{-- Sin foto de portada: el logo protagoniza la tarjeta --}}
            <div class="grid h-full w-full place-items-center bg-white p-6">
                <img src="{{ $venture->logo_url }}" alt="Logo {{ $venture->name }}" loading="lazy" class="max-h-full max-w-full object-contain transition duration-700 group-hover:scale-105">
            </div>
        @else
            <div class="ecos-g-violeta-menta h-full w-full"></div>
        @endif

        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($venture->final_position === 1)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-lion-600 px-3 py-1 text-xs font-bold text-white ico-inherit"><x-icon name="trophy" /> Ganador</span>
            @elseif ($venture->final_position)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-lion-700 shadow-sm"><x-icon name="medal" /> Puesto {{ $venture->final_position }}</span>
            @elseif ($venture->is_finalist)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-lion-700 shadow-sm"><x-icon name="star" /> Finalista</span>
            @endif
            @if ($venture->category)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-ink-800 shadow-sm backdrop-blur">@if ($venture->category->icon)<x-icon :name="$venture->category->icon" />@endif {{ $venture->category->name }}</span>
            @endif
        </div>

        @if ($rank)
            <span class="absolute right-3 top-3 grid h-9 w-9 place-items-center rounded-full bg-lion-600 font-display text-sm font-semibold text-white shadow-lg">#{{ $rank }}</span>
        @endif

        <div @class(['absolute bottom-3 left-4 flex items-center gap-3', 'hidden' => $logoAsCover])>
            @if ($venture->logo_url)
                <img src="{{ $venture->logo_url }}" alt="" class="h-12 w-12 rounded-2xl border border-white/20 bg-white object-contain p-1">
            @else
                <span class="grid h-12 w-12 place-items-center rounded-full bg-white font-display text-lg font-medium text-lion-600 shadow-sm">{{ $venture->initials }}</span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="font-display text-xl font-medium leading-tight text-lion-700 group-hover:text-lion-500">{{ $venture->name }}</h3>
        <p class="mt-2 line-clamp-2 text-sm text-white/60">{{ $venture->tagline }}</p>

        <div class="mt-auto flex items-center justify-between pt-5 text-sm">
            <span class="inline-flex items-center gap-2 font-semibold text-white/85">
                <x-icon name="heart" :style="$voted ? 'solid' : 'regular'" />
                {{ number_format($venture->votes_count ?? 0, 0, ',', '.') }} votos
            </span>
            @isset($venture->comments_count)
                <span class="inline-flex items-center gap-2 text-white/55"><x-icon name="comment" style="regular" /> {{ $venture->comments_count }}</span>
            @endisset
            <span class="inline-flex items-center gap-2 font-medium text-white/40 transition group-hover:translate-x-1 group-hover:text-lion-300">Ver más <x-icon name="arrow-right" /></span>
        </div>
    </div>
</article>
