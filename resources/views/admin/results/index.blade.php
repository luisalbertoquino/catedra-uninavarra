<x-layouts.admin title="Resultados">
    <x-slot:actions>
        @if ($edition)
            <a href="{{ route('admin.results.export', $edition) }}" class="a-btn-light">⬇ Exportar CSV</a>
        @endif
    </x-slot:actions>

    <form method="GET" class="mb-4">
        <select name="edicion" class="a-input sm:!w-72" onchange="this.form.submit()">
            @foreach ($editions as $e)<option value="{{ $e->id }}" @selected($edition?->id === $e->id)>{{ $e->name }}</option>@endforeach
        </select>
    </form>

    @if ($edition)
        <div class="mb-6 grid gap-4 lg:grid-cols-3">
            <div class="a-card p-5 lg:col-span-2">
                <h2 class="font-semibold text-ink-900">¿Cómo se calcula?</h2>
                <p class="mt-2 text-sm text-slate-600">
                    <b>Puntaje final</b> = puntaje del comité técnico (0–100) × <b>{{ 100 - $edition->community_weight }}%</b>
                    + puntaje de la comunidad × <b>{{ $edition->community_weight }}%</b>.
                    El puntaje de la comunidad normaliza los votos: el emprendimiento más votado obtiene 100.
                </p>
            </div>
            <div class="a-card flex flex-col justify-center gap-2 p-5">
                <form method="POST" action="{{ route('admin.results.finalists', $edition) }}" onsubmit="return confirm('Se marcarán como finalistas los {{ $edition->finalists_count }} primeros según el puntaje final. ¿Continuar?')">
                    @csrf
                    <button class="a-btn-lion w-full">⭐ Marcar top {{ $edition->finalists_count }} como finalistas</button>
                </form>
                <p class="text-center text-xs text-slate-500">Requiere puntajes del comité registrados.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.results.update', $edition) }}">
            @csrf @method('PUT')
            <div class="a-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>#</th><th>Emprendimiento</th><th class="text-center">Votos</th><th class="text-center">Pje. comunidad</th>
                                <th class="text-center">Pje. comité (0-100)</th><th class="text-center">Pje. final</th><th class="text-center">Finalista</th><th class="text-center">Posición final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($ranking as $i => $v)
                                <tr @class(['bg-lion-50/60' => $v->is_finalist])>
                                    <td class="font-display font-bold text-slate-400">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="font-semibold text-ink-900">{{ $v->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $v->category?->name }} @unless($v->is_published)· <span class="text-rose-500">no publicado</span>@endunless</div>
                                    </td>
                                    <td class="text-center font-semibold">{{ $v->votes_count }}</td>
                                    <td class="text-center">
                                        <div class="mx-auto w-24">
                                            <div class="text-xs">{{ number_format($v->community_score, 1, ',', '.') }}</div>
                                            <div class="mt-1 h-1.5 rounded-full bg-slate-100"><div class="h-full rounded-full bg-lion-400" style="width: {{ $v->community_score }}%"></div></div>
                                        </div>
                                    </td>
                                    <td class="text-center"><input type="number" step="0.01" min="0" max="100" name="rows[{{ $v->id }}][committee_score]" value="{{ $v->committee_score }}" class="a-input mx-auto !w-24 text-center"></td>
                                    <td class="text-center font-display text-lg font-bold text-ink-900">{{ $v->final_score !== null ? number_format($v->final_score, 2, ',', '.') : '—' }}</td>
                                    <td class="text-center"><input type="checkbox" name="rows[{{ $v->id }}][is_finalist]" value="1" @checked($v->is_finalist) class="h-5 w-5 rounded border-slate-300 text-lion-500 focus:ring-lion-400"></td>
                                    <td class="text-center"><input type="number" min="1" name="rows[{{ $v->id }}][final_position]" value="{{ $v->final_position }}" class="a-input mx-auto !w-20 text-center" placeholder="—"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col items-start justify-between gap-4 border-t border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                    <label class="flex items-center gap-3 text-sm">
                        <input type="checkbox" name="results_published" value="1" @checked($edition->results_published) class="h-5 w-5 rounded border-slate-300 text-ink-700 focus:ring-ink-500">
                        <span><b>Publicar resultados oficiales</b> en el ranking público</span>
                    </label>
                    <button class="a-btn-primary">Guardar resultados</button>
                </div>
            </div>
        </form>
    @endif
</x-layouts.admin>
