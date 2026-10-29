<x-layouts.admin title="Resultados">
    <x-slot:actions>
        @if ($edition)
            <a href="{{ route('admin.results.export', $edition) }}" class="a-btn-light"><x-icon name="file-arrow-down" /> Exportar CSV</a>
        @endif
    </x-slot:actions>

    <form method="GET" class="mb-4">
        <select name="edicion" class="a-input sm:!w-72" onchange="this.form.submit()">
            @foreach ($editions as $e)<option value="{{ $e->id }}" @selected($edition?->id === $e->id)>{{ $e->name }}</option>@endforeach
        </select>
    </form>

    @if ($edition)
        @php
            $w = $edition->gradeWeights();
            $combined = $edition->expertCountsAsJury();
            $g = fn ($n) => $n === null ? '—' : number_format($n, 2, ',', '.');
            $web = $edition->hasWebVoting();
        @endphp
        <div class="mb-6 grid gap-4 lg:grid-cols-3">
            <div class="a-card p-5 lg:col-span-2">
                <h2 class="font-semibold text-ink-900">¿Cómo se calcula la nota final? (escala 1 a 5)</h2>
                <div @class(['mt-3 grid gap-3', 'sm:grid-cols-2' => $combined, 'sm:grid-cols-3' => ! $combined])>
                    @php
                        $components = array_filter([
                            ['users', 'Comunidad', $w['community'], $edition->vote_grade_mode === 'rangos' ? 'Nota por rangos de votos' : 'El más votado = 5; los demás en proporción'],
                            $combined
                                ? ['gavel', 'Comité técnico', $w['jury'], 'Promedio de los jurados y el '.$edition->expert_title.': cada uno pesa igual']
                                : ['gavel', 'Jurados', $w['jury'], 'Promedio de la rúbrica de los jurados'],
                            $combined ? null : ['award', $edition->expert_title, $w['expert'], 'Promedio de la rúbrica del experto'],
                        ]);
                    @endphp
                    @foreach ($components as [$icon, $label, $pct, $hint])
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div class="flex items-center justify-between text-sm"><span class="font-medium text-slate-700"><x-icon :name="$icon" /> {{ $label }}</span><b class="text-lg text-ink-900">{{ $pct }}%</b></div>
                            <p class="mt-1 text-xs text-slate-600">{{ $hint }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-slate-600">
                    Si a un emprendimiento le falta algún componente (por ejemplo, el experto aún no lo evalúa), su peso se reparte entre los demás y la nota aparece como <b>parcial</b>.
                    Los porcentajes y la forma de calcular la nota de los votos se cambian en <a href="{{ route('admin.editions.edit', $edition) }}#calificacion" class="font-medium text-ink-700 underline">la edición</a>.
                </p>
                <a href="{{ route('admin.results.evaluations', $edition) }}" class="a-btn-light mt-4 !py-1.5"><x-icon name="table-cells" /> Ver evaluaciones de los jurados</a>
            </div>
            <div class="a-card flex flex-col justify-center gap-2 p-5">
                <div class="mb-3 border-b border-slate-200 pb-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold text-ink-900"><x-icon name="check-to-slot" /> Votación en la página</span>
                        <span @class(['rounded-full px-2.5 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $web, 'bg-slate-200 text-slate-700' => ! $web])>{{ $web ? 'Habilitada' : 'Deshabilitada' }}</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-600">{{ $web ? 'La comunidad vota desde cada emprendimiento.' : 'La comunidad vota en redes (YouTube, Facebook…): escribe sus votos en la columna «Votos» y guarda.' }}</p>
                    <form method="POST" action="{{ route('admin.results.web-voting', $edition) }}" class="mt-3"
                          onsubmit="return confirm(@js($web ? '¿Deshabilitar la votación en la página? Se ocultará el botón «Votar» y podrás registrar a mano los votos de las redes.' : '¿Habilitar de nuevo la votación en la página?'))">
                        @csrf
                        <button class="{{ $web ? 'a-btn-light' : 'a-btn-primary' }} w-full"><x-icon :name="$web ? 'toggle-off' : 'toggle-on'" /> {{ $web ? 'Deshabilitar votación en la página' : 'Habilitar votación en la página' }}</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('admin.results.finalists', $edition) }}" onsubmit="return confirm('Se marcarán como finalistas los {{ $edition->finalists_count }} primeros según la nota final. ¿Continuar?')">
                    @csrf
                    <button class="a-btn-lion w-full"><x-icon name="star" /> Marcar top {{ $edition->finalists_count }} como finalistas</button>
                </form>
                <p class="text-center text-xs text-slate-600">Usa la nota final actual (incluidas las parciales).</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.results.update', $edition) }}">
            @csrf @method('PUT')
            <div class="a-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="a-table">
                        <thead>
                            <tr>
                                <th>#</th><th class="min-w-[11rem]">Emprendimiento</th>
                                {{-- Comité técnico: una columna por jurado y por experto, con la nota que dio --}}
                                @foreach ($evaluators as $u)
                                    <th class="min-w-[6.5rem] text-center align-bottom" title="{{ $u->name }} · {{ $u->email }}">
                                        <span class="block text-[10px] font-semibold {{ $u->is_expert ? 'text-lion-700' : 'text-slate-500' }}"><x-icon :name="$u->is_expert ? 'award' : 'gavel'" /> {{ $u->is_expert ? Str::limit($edition->expert_title, 18) : 'Jurado' }}</span>
                                        <span class="block font-medium normal-case leading-tight text-ink-900">{{ $u->name }}</span>
                                    </th>
                                @endforeach
                                @if ($combined)
                                    <th class="bg-slate-50 text-center">Comité técnico<br><span class="font-normal normal-case">{{ $w['jury'] }}%</span></th>
                                @else
                                    <th class="bg-slate-50 text-center">Jurados<br><span class="font-normal normal-case">{{ $w['jury'] }}%</span></th>
                                    <th class="bg-slate-50 text-center">{{ Str::limit($edition->expert_title, 16) }}<br><span class="font-normal normal-case">{{ $w['expert'] }}%</span></th>
                                @endif
                                <th class="text-center">Votos<br><span class="font-normal normal-case">{{ $web ? 'en la página' : 'en redes' }}</span></th>
                                <th class="bg-slate-50 text-center">Comunidad<br><span class="font-normal normal-case">{{ $w['community'] }}%</span></th>
                                <th class="bg-lion-50 text-center">Nota final</th>
                                <th class="text-center">Manual<br><span class="font-normal normal-case">0-100</span></th>
                                <th class="text-center">Finalista</th><th class="text-center">Posición</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($ranking as $i => $v)
                                <tr @class(['bg-lion-50/60' => $v->is_finalist])>
                                    <td class="font-display font-semibold text-slate-500">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="font-semibold text-ink-900">{{ $v->name }}</div>
                                        <div class="text-xs text-slate-600">{{ $v->team_group }}@if ($v->team_group && $v->category) · @endif{{ $v->category?->name }} @unless($v->is_published)· <span class="text-rose-500">no publicado</span>@endunless</div>
                                    </td>
                                    @foreach ($evaluators as $u)
                                        @php $ug = $grades->get($v->id)?->get($u->id); @endphp
                                        <td @class(['text-center tabular-nums', 'text-slate-400' => $ug === null])>{{ $g($ug) }}</td>
                                    @endforeach
                                    <td class="bg-slate-50/70 text-center font-semibold tabular-nums">
                                        {{ $g($v->jury_grade) }}
                                        @php $n = $v->jury_count + ($combined ? $v->expert_count : 0); @endphp
                                        @if ($n)<div class="text-[11px] font-normal text-slate-500"><x-icon name="gavel" /> {{ $n }}</div>@endif
                                    </td>
                                    @unless ($combined)
                                        <td class="bg-slate-50/70 text-center font-semibold tabular-nums">
                                            {{ $g($v->expert_grade) }}
                                            @if ($v->expert_count)<div class="text-[11px] font-normal text-slate-500"><x-icon name="award" /> {{ $v->expert_count }}</div>@endif
                                        </td>
                                    @endunless
                                    <td class="text-center">
                                        @php $pageVotes = $v->votes_count - $v->external_votes; @endphp
                                        @if ($web)
                                            <span class="font-semibold">{{ $v->votes_count }}</span>
                                            @if ($v->external_votes)<div class="text-[11px] text-slate-500">{{ $v->external_votes }} de redes</div>@endif
                                        @else
                                            <input type="number" min="0" step="1" name="rows[{{ $v->id }}][external_votes]" value="{{ $v->external_votes }}" class="a-input mx-auto !w-24 text-center" title="Votos de la comunidad en redes (YouTube, Facebook…)">
                                            @if ($pageVotes)<div class="text-[11px] text-slate-500">+{{ $pageVotes }} en la página</div>@endif
                                        @endif
                                    </td>
                                    <td class="bg-slate-50/70 text-center font-semibold tabular-nums">{{ $g($v->community_grade) }}</td>
                                    <td class="bg-lion-50/40 text-center">
                                        <span class="font-display text-lg font-semibold text-ink-900">{{ $g($v->final_grade) }}</span>
                                        @if ($v->is_partial)<div class="text-[10px] font-semibold uppercase text-lion-700">Parcial</div>@endif
                                    </td>
                                    <td class="text-center">
                                        @if ($v->committee_source === 'jurados')
                                            <input type="hidden" name="rows[{{ $v->id }}][committee_score]" value="{{ $v->committee_score }}">
                                            <span class="text-xs text-slate-500" title="Ya tiene evaluaciones de jurados">—</span>
                                        @else
                                            <input type="number" step="0.01" min="0" max="100" name="rows[{{ $v->id }}][committee_score]" value="{{ $v->committee_score }}" class="a-input mx-auto !w-20 text-center" placeholder="—" title="Puntaje manual del comité (solo si no hay evaluaciones)">
                                        @endif
                                    </td>
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
                        <span><b>Publicar resultados oficiales</b> en el ranking público (cierra la evaluación de los jurados)</span>
                    </label>
                    <button class="a-btn-primary">Guardar resultados</button>
                </div>
            </div>
        </form>
    @endif
</x-layouts.admin>
