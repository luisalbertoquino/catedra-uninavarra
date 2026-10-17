<x-layouts.site title="Emprendimientos" :description="'Conoce los emprendimientos de la '.($edition?->name ?? 'Cátedra UNINAVARRA')">
    <section class="relative overflow-hidden pb-10 pt-32 lg:pt-40">
        <div class="pointer-events-none absolute -top-20 left-1/2 -z-10 h-96 w-[50rem] -translate-x-1/2 rounded-full bg-volt-600/20 blur-3xl"></div>
        <div class="container-x">
            <span class="eyebrow"><x-icon name="lightbulb" /> {{ $edition?->name }}</span>
            <h1 class="mt-3 page-title">Emprendimientos</h1>
            <p class="mt-4 max-w-2xl text-lg text-white/60">
                Explora las ideas de negocio de los equipos Navarristas. Mira su video pitch, conoce al equipo y vota por tus favoritas.
            </p>

            @if ($edition)
                <div class="mt-6 flex flex-wrap items-center gap-2 text-sm">
                    @switch($edition->votingStatus())
                        @case('abierta')
                            <span class="chip border-lion-400/30 bg-lion-400/10 text-lion-100"><x-icon name="circle-check" /> Votación abierta hasta el {{ $edition->voting_ends_at->translatedFormat('j \d\e F') }}</span>
                            @break
                        @case('proxima')
                            <span class="chip"><x-icon name="hourglass-half" /> Votación abre el {{ $edition->voting_starts_at->translatedFormat('j \d\e F') }}</span>
                            @break
                        @case('cerrada')
                            <span class="chip"><x-icon name="lock" /> Votación cerrada</span>
                            @break
                    @endswitch
                    @if ($votesLeft !== null && $edition->isVotingOpen())
                        <span class="chip"><x-icon name="check-to-slot" /> Te quedan <b class="text-lion-300">{{ $votesLeft }}</b> de {{ $edition->max_votes_per_user }} votos</span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Filtros --}}
    <div class="sticky top-16 z-30 border-y border-white/10 bg-ink-950/80 backdrop-blur-xl lg:top-20">
        <form method="GET" x-data x-ref="f" class="container-x flex flex-col gap-3 py-4 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <x-icon name="magnifying-glass" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, idea o programa…" class="input-dark !rounded-lg !py-2.5 pl-12"
                       @input.debounce.500ms="$refs.f.submit()">
            </div>
            <div class="flex gap-2 overflow-x-auto no-scrollbar">
                @if ($editions->count() > 1)
                    <select name="edicion" class="input-dark !w-auto !rounded-lg !py-2.5 pr-10 text-sm" @change="$refs.f.submit()">
                        @foreach ($editions as $e)
                            <option value="{{ $e->slug }}" @selected($edition?->id === $e->id)>{{ $e->period_label }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="orden" class="input-dark !w-auto !rounded-lg !py-2.5 pr-10 text-sm" @change="$refs.f.submit()">
                    @foreach (['populares' => 'Más votados', 'comentados' => 'Más comentados', 'recientes' => 'Recientes', 'az' => 'A - Z'] as $k => $l)
                        <option value="{{ $k }}" @selected(request('orden', 'populares') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="categoria" value="{{ request('categoria') }}">
            </div>
        </form>

        @if ($categories->isNotEmpty())
            <div class="container-x -mt-1 flex gap-2 overflow-x-auto pb-4 no-scrollbar">
                <a href="{{ request()->fullUrlWithQuery(['categoria' => null, 'page' => null]) }}"
                   @class(['shrink-0 rounded-lg px-4 py-1.5 text-sm font-medium transition', 'bg-white text-ink-950' => ! request('categoria'), 'border border-white/10 text-white/70 hover:text-white' => request('categoria')])>Todas</a>
                @foreach ($categories as $c)
                    <a href="{{ request()->fullUrlWithQuery(['categoria' => $c->slug, 'page' => null]) }}"
                       @class(['shrink-0 rounded-lg px-4 py-1.5 text-sm font-medium transition', 'bg-white text-ink-950' => request('categoria') === $c->slug, 'border border-white/10 text-white/70 hover:text-white' => request('categoria') !== $c->slug])>
                        @if ($c->icon)<x-icon :name="$c->icon" class="mr-1" />@endif{{ $c->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    <section class="container-x py-12">
        @if ($ventures->isEmpty())
            <div class="card-dark mx-auto max-w-lg p-12 text-center">
                <x-icon name="magnifying-glass" class="text-4xl" />
                <h2 class="mt-4 font-display text-2xl font-semibold">Sin resultados</h2>
                <p class="mt-2 text-white/60">No encontramos emprendimientos con esos filtros.</p>
                <a href="{{ route('ventures.index') }}" class="btn-ghost mt-6">Limpiar filtros</a>
            </div>
        @else
            <p class="mb-6 text-sm text-white/50">{{ $ventures->total() }} emprendimiento{{ $ventures->total() === 1 ? '' : 's' }}</p>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($ventures as $venture)
                    <x-venture-card :venture="$venture" :voted="in_array($venture->id, $myVotes)" class="animate-fade-up" style="animation-delay: {{ $loop->index * 50 }}ms" />
                @endforeach
            </div>
            <div class="mt-12">{{ $ventures->links('vendor.pagination.dark') }}</div>
        @endif
    </section>
</x-layouts.site>
