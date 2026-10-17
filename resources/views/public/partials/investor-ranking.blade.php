{{-- Ranking público de inversionistas y aliados (solo quienes aceptaron aparecer) --}}
<section class="container-x pb-10 pt-10">
    <div class="mx-auto max-w-4xl">
        @if ($investors->isEmpty())
            <div class="card-dark p-12 text-center">
                <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-lion-400/10"><x-icon name="hand-holding-dollar" class="text-2xl" /></span>
                <h2 class="mt-4 font-display text-2xl font-semibold">Aún no hay apoyos concretados</h2>
                <p class="mt-2 text-white/60">Registra tu intención de apoyo en un emprendimiento. Cuando el Centro de Emprendimiento confirme que el apoyo se concretó, aparecerás aquí (si aceptas ser visible).</p>
                <a href="{{ route('ventures.index') }}" class="btn-lion mt-6">Ver emprendimientos <x-icon name="arrow-right" /></a>
            </div>
        @else
            @php $max = max(1, (float) $investors->max('total_amount')); @endphp
            <div class="space-y-2">
                @foreach ($investors as $i => $inv)
                    <div class="glass reveal flex items-center gap-4 rounded-2xl p-3 pr-5 sm:p-4">
                        <span @class(['grid h-10 w-10 shrink-0 place-items-center rounded-xl font-display font-semibold', 'ico-inherit bg-lion-400 text-ink-950' => $i < 3, 'bg-white/5 text-white/60' => $i >= 3])>
                            @if ($i === 0)<x-icon name="crown" />@else{{ $i + 1 }}@endif
                        </span>
                        <x-avatar :name="$inv->organization ?: $inv->name" class="hidden sm:inline-grid" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-x-2">
                                <span class="truncate font-semibold">{{ $inv->organization ?: $inv->name }}</span>
                                @if ($inv->organization)<span class="truncate text-xs text-white/45">{{ $inv->name }}</span>@endif
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 text-xs text-white/50">
                                <span><x-icon name="lightbulb" /> {{ $inv->ventures_count }} emprendimiento{{ $inv->ventures_count == 1 ? '' : 's' }}</span>
                                <span><x-icon name="circle-check" /> {{ $inv->completed_count }} apoyo{{ $inv->completed_count == 1 ? '' : 's' }} concretado{{ $inv->completed_count == 1 ? '' : 's' }}</span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/5">
                                <div class="h-full rounded-full bg-gradient-to-r from-lion-400 to-lion-600" style="width: {{ round($inv->total_amount / $max * 100, 1) }}%"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-display text-lg font-semibold tabular-nums">{{ $inv->total_amount > 0 ? '$'.number_format($inv->total_amount, 0, ',', '.') : '—' }}</div>
                            <div class="text-[11px] text-white/45">{{ $inv->total_amount > 0 ? 'concretado' : 'mentoría / alianza' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <p class="mt-6 text-center text-xs text-white/40">
            Solo se cuentan los apoyos concretados y confirmados por el Centro de Emprendimiento; la plataforma no procesa pagos en línea.
            @if ($hiddenInvestors > 0) {{ $hiddenInvestors }} aliado{{ $hiddenInvestors == 1 ? '' : 's' }} más prefiri{{ $hiddenInvestors == 1 ? 'ó' : 'eron' }} no aparecer. @endif
        </p>

        <x-investment-contact class="mt-8" />
    </div>
</section>
