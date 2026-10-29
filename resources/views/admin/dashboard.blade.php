<x-layouts.admin title="Dashboard">
    <x-slot:actions>
        <form method="GET">
            <select name="edicion" class="a-input !w-auto !py-2 pr-9" onchange="this.form.submit()">
                @foreach ($editions as $e)
                    <option value="{{ $e->id }}" @selected($edition?->id === $e->id)>{{ $e->name }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>

    @if (! $edition)
        <div class="a-card p-10 text-center">
            <p class="text-lg font-semibold">Aún no hay ediciones</p>
            <a href="{{ route('admin.editions.create') }}" class="a-btn-primary mt-4">Crear la primera edición</a>
        </div>
    @else
        {{-- Estado de la edición --}}
        <div class="ecos-g-profundo mb-6 rounded-ecos p-6 text-white sm:p-8">
            <div class="relative flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @if ($edition->is_active)<span class="rounded-full bg-white px-2.5 py-1 font-semibold text-lion-700"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Edición activa</span>@endif
                        @php $vs = $edition->votingStatus(); @endphp
                        <span class="rounded-full bg-white/20 px-2.5 py-1">Votación: {{ ['abierta' => 'abierta', 'proxima' => 'próxima', 'cerrada' => 'cerrada', 'sin_fecha' => 'sin fechas', 'redes' => 'en redes (registro manual)'][$vs] }}</span>
                        <span class="rounded-full bg-white/20 px-2.5 py-1">Resultados {{ $edition->results_published ? 'publicados' : 'ocultos' }}</span>
                    </div>
                    <h2 class="mt-3 font-display text-2xl font-semibold sm:text-3xl">{{ $edition->name }}</h2>
                    <p class="text-white/90">{{ $edition->event_name }} · {{ $edition->tagline }}</p>
                </div>
                @if ($edition->final_at)
                    <div class="text-left sm:text-right">
                        <div class="text-sm font-semibold uppercase tracking-wide text-white/90">Gran final</div>
                        <div class="font-display text-2xl font-semibold text-white">{{ $edition->final_at->isFuture() ? 'en '.now()->diffInDays($edition->final_at).' días' : $edition->final_at->translatedFormat('j M Y') }}</div>
                        <div class="text-sm text-white/90">{{ $edition->final_at->translatedFormat('l j \d\e F, g:i a') }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Emprendimientos', $kpis['ventures'], 'rocket', route('admin.ventures.index', ['edicion' => $edition->id]), null],
                ['Votos', number_format($kpis['votes'], 0, ',', '.'), 'check-to-slot', route('admin.results.index', ['edicion' => $edition->id]), $kpis['voters'].' votantes únicos'],
                ['Usuarios registrados', number_format($kpis['users'], 0, ',', '.'), 'users', route('admin.users.index'), $kpis['investors'].' inversionistas/aliados'],
                ['Intenciones de apoyo', $kpis['pledges'], 'handshake', route('admin.pledges.index'), '$'.number_format($kpis['pledged_amount'], 0, ',', '.').' en intención'],
            ] as [$label, $value, $icon, $url, $sub])
                <a href="{{ $url }}" class="a-card group p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-center justify-between"><span class="text-sm text-slate-600">{{ $label }}</span><span class="grid h-10 w-10 place-items-center rounded-xl bg-lion-50"><x-icon :name="$icon" /></span></div>
                    <div class="mt-3 font-display text-3xl font-semibold text-ink-900">{{ $value }}</div>
                    @if ($sub)<div class="mt-1 text-xs text-slate-600">{{ $sub }}</div>@endif
                </a>
            @endforeach
        </div>

        @if ($kpis['pending_pledges'] || $kpis['pending_comments'])
            <div class="mt-4 flex flex-wrap gap-3">
                @if ($kpis['pending_pledges'])
                    <a href="{{ route('admin.pledges.index', ['estado' => 'pendiente']) }}" class="rounded-xl border border-lion-300 bg-lion-50 px-4 py-2 text-sm font-medium text-lion-800 hover:bg-lion-100"><x-icon name="hourglass-half" /> {{ $kpis['pending_pledges'] }} intenciones de apoyo por gestionar <x-icon name="arrow-right" /></a>
                @endif
                @if ($kpis['pending_comments'])
                    <a href="{{ route('admin.comments.index', ['estado' => 'pendientes']) }}" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100"><x-icon name="comments" /> {{ $kpis['pending_comments'] }} comentarios por moderar <x-icon name="arrow-right" /></a>
                @endif
            </div>
        @endif

        {{-- Gráficas --}}
        <div class="mt-6 grid gap-6 xl:grid-cols-3">
            <div class="a-card p-5 xl:col-span-2">
                <h3 class="font-semibold text-ink-900">Votos por día</h3>
                <div class="mt-4 h-64"><canvas id="chart-votes"></canvas></div>
            </div>
            <div class="a-card p-5">
                <h3 class="font-semibold text-ink-900">Tipos de apoyo</h3>
                <div class="mt-4 h-64">
                    @if ($pledgesByType->isEmpty())
                        <p class="grid h-full place-items-center text-sm text-slate-500">Sin intenciones aún</p>
                    @else
                        <canvas id="chart-pledges"></canvas>
                    @endif
                </div>
            </div>
            <div class="a-card p-5 xl:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-ink-900">Top emprendimientos por votos</h3>
                    <a href="{{ route('admin.results.index', ['edicion' => $edition->id]) }}" class="text-sm font-medium text-ink-600 hover:text-ink-900">Ver resultados <x-icon name="arrow-right" /></a>
                </div>
                <div class="mt-4 h-72"><canvas id="chart-ranking"></canvas></div>
            </div>
            <div class="a-card p-5">
                <h3 class="font-semibold text-ink-900">Últimos comentarios</h3>
                <ul class="mt-4 divide-y divide-slate-100">
                    @forelse ($latestComments as $c)
                        <li class="py-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate font-medium">{{ $c->user->name }}</span>
                                <span class="shrink-0 text-xs text-slate-500">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-slate-600">en {{ $c->venture->name }}</div>
                            <p class="mt-1 line-clamp-2 text-slate-600">{{ $c->body }}</p>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-slate-500">Sin comentarios</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="a-card mt-6 overflow-hidden">
            <div class="flex items-center justify-between p-5">
                <h3 class="font-semibold text-ink-900">Últimas intenciones de apoyo</h3>
                <a href="{{ route('admin.pledges.index') }}" class="text-sm font-medium text-ink-600 hover:text-ink-900">Gestionar <x-icon name="arrow-right" /></a>
            </div>
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead><tr><th>Persona</th><th>Emprendimiento</th><th>Tipo</th><th>Monto</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($latestPledges as $p)
                            <tr>
                                <td><div class="font-medium">{{ $p->user->name }}</div><div class="text-xs text-slate-600">{{ $p->user->organization ?? $p->user->email }}</div></td>
                                <td>{{ $p->venture->name }}</td>
                                <td>{{ $p->type_label }}</td>
                                <td>{{ $p->amount ? '$'.number_format($p->amount, 0, ',', '.') : '—' }}</td>
                                <td><x-admin.status :status="$p->status" /></td>
                                <td class="whitespace-nowrap text-slate-600">{{ $p->created_at->translatedFormat('j M') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @push('scripts')
            @vite('resources/js/admin-charts.js')
            <script type="module">
                window.renderAdminCharts({
                    votesByDay: @js($votesByDay),
                    ranking: @js($ranking->map(fn ($v) => ['name' => $v->name, 'votes' => $v->votes_count])->values()),
                    pledgesByType: @js($pledgesByType->mapWithKeys(fn ($t, $k) => [\App\Models\SupportPledge::TYPES[$k]['label'] ?? $k => $t])),
                });
            </script>
        @endpush
    @endif
</x-layouts.admin>
