<x-layouts.site title="Ranking">
    @php
        $byScore = $edition?->results_published;
        $podium = $ranking->take(3);
        $order = [1, 0, 2]; // 2º, 1º, 3º
    @endphp

    <section class="relative overflow-hidden pb-6 pt-32 text-center lg:pt-40">
        <div class="pointer-events-none absolute -top-10 left-1/2 -z-10 h-[28rem] w-[50rem] -translate-x-1/2 rounded-full bg-lion-500/15 blur-3xl"></div>
        <div class="container-x">
            <span class="eyebrow"><x-icon name="ranking-star" /> {{ $edition?->name }}</span>
            <h1 class="mt-3 page-title">
                @if ($tab === 'inversionistas') Ranking de inversionistas @else {{ $byScore ? 'Resultados oficiales' : 'Ranking de la comunidad' }} @endif
            </h1>
            <p class="mx-auto mt-4 max-w-2xl text-white/60">
                @if ($tab === 'inversionistas')
                    Aliados que más le apuestan al talento Navarrista, según el monto de inversiones y donaciones ya concretadas.
                @elseif ($byScore)
                    Nota final de 1 a 5: comunidad {{ $edition->community_weight }}% + jurados {{ $edition->jury_weight }}% + {{ $edition->expert_title }} {{ $edition->expert_score_weight }}%.
                @else
                    Así va la votación en tiempo real. La comunidad aporta el {{ $edition?->community_weight ?? 30 }}% de la nota final; el resto lo definen los jurados y el {{ $edition?->expert_title ?? 'Profesor experto' }}.
                @endif
            </p>
            <div class="mt-8 inline-flex rounded-xl border border-white/10 bg-white/[.03] p-1">
                @foreach (['emprendimientos' => ['lightbulb', 'Emprendimientos'], 'inversionistas' => ['hand-holding-dollar', 'Inversionistas']] as $key => [$icon, $label])
                    <a href="{{ route('ranking', array_filter(['edicion' => request('edicion'), 'vista' => $key === 'emprendimientos' ? null : $key])) }}"
                       @class(['inline-flex items-center gap-2 rounded-lg px-5 py-2 text-sm font-semibold transition', 'ico-inherit bg-lion-400 text-ink-950' => $tab === $key, 'text-white/70 hover:text-white' => $tab !== $key])>
                        <x-icon :name="$icon" /> {{ $label }}
                    </a>
                @endforeach
            </div>
            @if ($editions->count() > 1)
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @foreach ($editions as $e)
                        <a href="{{ route('ranking', array_filter(['edicion' => $e->slug, 'vista' => $tab === 'inversionistas' ? $tab : null])) }}" @class(['rounded-lg px-4 py-1.5 text-sm font-medium', 'bg-white text-ink-950' => $edition?->id === $e->id, 'border border-white/10 text-white/70 hover:text-white' => $edition?->id !== $e->id])>{{ $e->period_label }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($tab === 'inversionistas')
        @include('public.partials.investor-ranking')
    @elseif ($ranking->isEmpty())
        <p class="container-x py-20 text-center text-white/50">Aún no hay emprendimientos en esta edición.</p>
    @else
        {{-- Podio --}}
        <section class="container-x pt-10">
            <div class="mx-auto grid max-w-4xl grid-cols-3 items-end gap-2 sm:gap-5">
                @foreach ($order as $idx)
                    @if ($v = $podium->get($idx))
                        @php $place = $idx + 1; @endphp
                        <a href="{{ route('ventures.show', $v) }}" class="group animate-fade-up text-center" style="animation-delay: {{ $place * 120 }}ms">
                            <div class="relative mx-auto mb-3 w-fit">
                                @if ($v->logo_url)
                                    <img src="{{ $v->logo_url }}" class="h-16 w-16 rounded-2xl bg-white object-contain p-1 sm:h-24 sm:w-24" alt="">
                                @else
                                    <span class="grid h-16 w-16 place-items-center rounded-2xl border border-white/15 bg-ink-800 font-display text-xl font-semibold text-lion-300 sm:h-24 sm:w-24 sm:text-3xl">{{ $v->initials }}</span>
                                @endif
                                <span class="absolute -right-3 -top-3 grid h-9 w-9 place-items-center rounded-full border border-lion-400/40 bg-ink-950 sm:h-10 sm:w-10"><x-icon :name="$idx === 0 ? 'crown' : 'medal'" /></span>
                            </div>
                            <div class="truncate px-1 text-sm font-bold group-hover:text-lion-300 sm:text-lg">{{ $v->name }}</div>
                            <div class="text-xs text-white/50 sm:text-sm">
                                {{ $byScore && $v->final_grade !== null ? 'Nota '.number_format($v->final_grade, 2, ',', '.') : number_format($v->votes_count, 0, ',', '.').' votos' }}
                            </div>
                            <div @class([
                                'mt-3 rounded-t-2xl border border-b-0 border-white/10 font-display text-4xl font-semibold sm:text-6xl',
                                'h-40 bg-gradient-to-b from-lion-400 to-lion-600 pt-6 text-ink-950 sm:h-56' => $place === 1,
                                'h-28 bg-gradient-to-b from-white/25 to-white/5 pt-5 text-white/80 sm:h-40' => $place === 2,
                                'h-20 bg-gradient-to-b from-orange-700/60 to-orange-900/20 pt-3 text-white/80 sm:h-28' => $place === 3,
                            ])>{{ $place }}</div>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- Tabla --}}
        <section class="container-x pb-10">
            <div class="mx-auto max-w-4xl space-y-2 border-t border-white/10 pt-8">
                @foreach ($ranking as $i => $v)
                    @php $pct = $totalVotes ? round($v->votes_count / $totalVotes * 100, 1) : 0; @endphp
                    <a href="{{ route('ventures.show', $v) }}" class="glass reveal group flex items-center gap-4 rounded-2xl p-3 pr-5 transition hover:border-lion-400/40 hover:bg-white/[.07] sm:p-4" style="transition-delay: {{ min($i, 10) * 40 }}ms">
                        <span @class(['grid h-10 w-10 shrink-0 place-items-center rounded-xl font-display font-semibold', 'bg-lion-400 text-ink-950' => $i < 3, 'bg-white/5 text-white/60' => $i >= 3])>{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="truncate font-semibold group-hover:text-lion-300">{{ $v->name }}</span>
                                @if ($v->is_finalist)<span class="chip !py-0 text-[10px] text-lion-100"><x-icon name="star" /> Finalista</span>@endif
                                @if ($v->category)<span class="hidden items-center gap-1.5 text-xs text-white/40 sm:inline-flex">@if ($v->category->icon)<x-icon :name="$v->category->icon" />@endif {{ $v->category->name }}</span>@endif
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5">
                                <div class="h-full rounded-full bg-gradient-to-r from-lion-400 to-volt-500 transition-all duration-1000" style="width: {{ $byScore && $v->final_grade !== null ? round($v->final_grade / 5 * 100, 1) : ($totalVotes ? round($v->votes_count / max(1, $ranking->max('votes_count')) * 100, 1) : 0) }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            @if ($byScore && $v->final_grade !== null)
                                <div class="font-display text-lg font-semibold tabular-nums">{{ number_format($v->final_grade, 2, ',', '.') }}<span class="text-xs text-white/40">/5</span></div>
                                <div class="text-[11px] text-white/45">{{ $v->votes_count }} votos</div>
                            @else
                                <div class="font-display text-lg font-semibold tabular-nums">{{ number_format($v->votes_count, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-white/45">{{ number_format($pct, 1, ',', '.') }}%</div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <p class="mt-6 text-center text-sm text-white/40">{{ number_format($totalVotes, 0, ',', '.') }} votos en total</p>
        </section>
    @endif
</x-layouts.site>
