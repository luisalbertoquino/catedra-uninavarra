<x-layouts.admin :title="'Evaluaciones · '.$edition->name">
    <x-slot:actions>
        <a href="{{ route('admin.results.index', ['edicion' => $edition->id]) }}" class="a-btn-light"><x-icon name="arrow-left" /> Resultados</a>
    </x-slot:actions>

    @php $fmt = fn ($n) => $n === null ? '—' : number_format($n, 1, ',', '.'); @endphp

    <p class="mb-6 max-w-3xl text-sm text-slate-500">
        Puntaje de cada jurado (0–100) por emprendimiento. La columna <b>Comité</b> es el promedio ponderado:
        el <b>{{ $edition->expert_title }}</b> pesa ×{{ rtrim(rtrim(number_format($edition->expert_weight, 2, ',', '.'), '0'), ',') }}.
        Pasa el mouse sobre una celda para ver el detalle por criterio.
    </p>

    @if ($juries->isEmpty())
        <div class="a-card p-12 text-center text-slate-400">No hay jurados creados. <a href="{{ route('admin.juries.index') }}" class="font-semibold text-ink-700">Crear jurados <x-icon name="arrow-right" /></a></div>
    @else
        <div class="a-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-slate-50">Emprendimiento</th>
                            @foreach ($juries as $j)
                                <th class="text-center normal-case tracking-normal">
                                    <div class="font-semibold text-slate-700">{{ Str::limit($j->name, 18) }}</div>
                                    @if ($j->is_expert)<div class="text-[10px] font-semibold uppercase text-lion-700"><x-icon name="award" /> ×{{ rtrim(rtrim(number_format($edition->expert_weight, 2, ',', '.'), '0'), ',') }}</div>@endif
                                </th>
                            @endforeach
                            <th class="bg-lion-50 text-center">Comité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($ventures as $v)
                            @php $row = $evaluations->get($v->id, collect()); @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="sticky left-0 z-10 bg-white font-medium text-ink-900">{{ $v->name }}</td>
                                @foreach ($juries as $j)
                                    @php
                                        $e = $row->get($j->id);
                                        $detail = $e ? $edition->criteria->map(fn ($c) => $c->name.': '.($e->scores->firstWhere('rubric_criterion_id', $c->id)?->score ?? '—').'/'.$c->max_score)->implode("\n").($e->comment ? "\n\n«".Str::limit($e->comment, 200).'»' : '') : 'Sin evaluar';
                                    @endphp
                                    <td class="text-center" title="{{ $detail }}">
                                        @if ($e)
                                            <span class="inline-block min-w-[3rem] rounded-md bg-slate-100 px-2 py-1 font-semibold tabular-nums text-slate-800">{{ $fmt((float) $e->total_score) }}</span>
                                            @if ($e->comment)<i class="fa-regular fa-comment ml-1 text-slate-400" aria-hidden="true"></i>@endif
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="bg-lion-50/60 text-center font-display text-base font-semibold text-ink-900">{{ $fmt($scores->get($v->id)['score'] ?? null) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50 text-xs text-slate-500">
                        <tr>
                            <td class="sticky left-0 bg-slate-50 font-semibold">Avance</td>
                            @foreach ($juries as $j)
                                @php $n = $evaluations->filter(fn ($r) => $r->has($j->id))->count(); @endphp
                                <td class="text-center">{{ $n }}/{{ $ventures->count() }}</td>
                            @endforeach
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-layouts.admin>
