@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginación" class="flex items-center justify-center gap-1.5">
        @if ($paginator->onFirstPage())
            <span class="grid h-10 w-10 place-items-center rounded-full border border-white/5 text-white/25">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid h-10 w-10 place-items-center rounded-lg border border-white/10 text-white/70 hover:border-white/30 hover:text-white">‹</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-white/40">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="grid h-10 min-w-[2.5rem] place-items-center rounded-full bg-lion-400 px-3 text-sm font-bold text-ink-950">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="grid h-10 min-w-[2.5rem] place-items-center rounded-lg border border-white/10 px-3 text-sm text-white/70 hover:border-white/30 hover:text-white">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid h-10 w-10 place-items-center rounded-lg border border-white/10 text-white/70 hover:border-white/30 hover:text-white">›</a>
        @else
            <span class="grid h-10 w-10 place-items-center rounded-full border border-white/5 text-white/25">›</span>
        @endif
    </nav>
@endif
