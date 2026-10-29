<x-layouts.admin :title="$edition->exists ? 'Editar edición' : 'Nueva edición'">
    <x-slot:actions>
        <a href="{{ route('admin.editions.index') }}" class="a-btn-light"><x-icon name="arrow-left" /> Volver</a>
    </x-slot:actions>

    @php
        $dt = fn ($field) => old($field, $edition->{$field}?->format('Y-m-d\TH:i'));
        $rows = old('milestones', $milestones);
    @endphp

    <form method="POST" enctype="multipart/form-data"
          action="{{ $edition->exists ? route('admin.editions.update', $edition) : route('admin.editions.store') }}"
          class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($edition->exists) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Información general</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label class="a-label" for="name">Nombre *</label>
                        <input id="name" name="name" class="a-input" required value="{{ old('name', $edition->name) }}" placeholder="Cátedra UNINAVARRA 2027-1">
                    </div>
                    <div class="sm:col-span-1">
                        <label class="a-label" for="year">Año *</label>
                        <input id="year" type="number" name="year" class="a-input" required value="{{ old('year', $edition->year) }}">
                    </div>
                    <div class="sm:col-span-1">
                        <label class="a-label" for="period">Periodo</label>
                        <select id="period" name="period" class="a-input">
                            <option value="">—</option>
                            <option value="1" @selected(old('period', $edition->period) == 1)>1</option>
                            <option value="2" @selected(old('period', $edition->period) == 2)>2</option>
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="a-label" for="event_name">Nombre del evento</label>
                        <input id="event_name" name="event_name" class="a-input" value="{{ old('event_name', $edition->event_name) }}" placeholder="LION HUB EVENT">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="a-label" for="tagline">Lema / temática</label>
                        <input id="tagline" name="tagline" class="a-input" value="{{ old('tagline', $edition->tagline) }}" placeholder="Disruptive Minds">
                    </div>
                    <div class="sm:col-span-6">
                        <label class="a-label" for="description">Descripción</label>
                        <textarea id="description" name="description" rows="4" class="a-input">{{ old('description', $edition->description) }}</textarea>
                    </div>
                    <div class="sm:col-span-6">
                        <label class="a-label" for="slug">URL amigable <span class="font-normal text-slate-500">(se genera sola si la dejas vacía)</span></label>
                        <div class="flex rounded-xl shadow-sm"><span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-600">/ediciones/</span>
                            <input id="slug" name="slug" class="a-input !rounded-l-none" value="{{ old('slug', $edition->slug) }}"></div>
                    </div>
                </div>
            </section>

            <section class="a-card p-6" x-data="{ rows: @js(array_values($rows)) }">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-ink-900">Cronograma / fases</h2>
                        <p class="text-sm text-slate-600">Se muestra como línea de tiempo en la página de inicio.</p>
                    </div>
                    <button type="button" class="a-btn-light" @click="rows.push({ title: '', description: '', starts_at: '', ends_at: '' })"><x-icon name="plus" /> Fase</button>
                </div>
                <div class="mt-5 space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-2 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-ink-800 text-xs font-bold text-white" x-text="i + 1"></span>
                                <div class="grid flex-1 gap-3 sm:grid-cols-6">
                                    <input class="a-input sm:col-span-6" :name="`milestones[${i}][title]`" x-model="row.title" placeholder="Título de la fase" required>
                                    <textarea class="a-input sm:col-span-6" rows="2" :name="`milestones[${i}][description]`" x-model="row.description" placeholder="Descripción breve (opcional)"></textarea>
                                    <label class="text-xs text-slate-600 sm:col-span-3">Inicio<input type="date" class="a-input mt-1" :name="`milestones[${i}][starts_at]`" x-model="row.starts_at"></label>
                                    <label class="text-xs text-slate-600 sm:col-span-3">Fin<input type="date" class="a-input mt-1" :name="`milestones[${i}][ends_at]`" x-model="row.ends_at"></label>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-white hover:text-slate-700" @click="i > 0 && rows.splice(i - 1, 0, rows.splice(i, 1)[0])" title="Subir"><x-icon name="arrow-up" /></button>
                                    <button type="button" class="rounded-lg p-1.5 text-slate-500 hover:bg-white hover:text-slate-700" @click="i < rows.length - 1 && rows.splice(i + 1, 0, rows.splice(i, 1)[0])" title="Bajar"><x-icon name="arrow-down" /></button>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 hover:text-rose-600" @click="rows.splice(i, 1)" title="Eliminar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p x-show="!rows.length" class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Sin fases. Agrega la primera.</p>
                </div>
            </section>

            {{-- Rúbrica de evaluación del jurado --}}
            <section class="a-card p-6" x-data="{ rows: @js(array_values(old('criteria', $criteria ?? []))), get sum() { return this.rows.reduce((a, r) => a + (parseInt(r.weight) || 0), 0) } }">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-ink-900">Rúbrica de evaluación del jurado</h2>
                        <p class="text-sm text-slate-600">Cada jurado califica estos criterios al ver el video. El puntaje se convierte a una escala de 0 a 100.</p>
                    </div>
                    <button type="button" class="a-btn-light shrink-0" @click="rows.push({ name: '', description: '', weight: 10, max_score: 5 })"><x-icon name="plus" /> Criterio</button>
                </div>
                @if ($hasEvaluations ?? false)
                    <p class="mt-4 rounded-xl border border-lion-200 bg-lion-50 p-3 text-xs text-lion-900"><x-icon name="triangle-exclamation" /> Ya hay evaluaciones. Si agregas criterios, los jurados deberán calificarlos; si eliminas uno, se borran sus puntajes. Los totales se recalculan al guardar.</p>
                @endif
                <div class="mt-5 space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <input type="hidden" :name="`criteria[${i}][id]`" :value="row.id" x-bind:disabled="!row.id">
                            <div class="grid gap-3 sm:grid-cols-12">
                                <input class="a-input sm:col-span-6" :name="`criteria[${i}][name]`" x-model="row.name" placeholder="Criterio (ej. Innovación)" required>
                                <label class="text-xs text-slate-600 sm:col-span-3">Peso %<input type="number" min="1" max="100" class="a-input mt-1" :name="`criteria[${i}][weight]`" x-model="row.weight" required></label>
                                <label class="text-xs text-slate-600 sm:col-span-3">Escala 1 a<input type="number" min="2" max="10" class="a-input mt-1" :name="`criteria[${i}][max_score]`" x-model="row.max_score" required></label>
                                <textarea class="a-input sm:col-span-11" rows="2" :name="`criteria[${i}][description]`" x-model="row.description" placeholder="Qué debe valorar el jurado (opcional)"></textarea>
                                <button type="button" class="self-start rounded-lg p-2 text-rose-400 hover:bg-rose-50 hover:text-rose-600 sm:col-span-1" @click="rows.splice(i, 1)" title="Eliminar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </template>
                    <p x-show="!rows.length" class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Sin criterios. Los jurados no podrán evaluar hasta definir la rúbrica.</p>
                </div>
                <p class="mt-3 text-sm" :class="sum === 100 ? 'text-emerald-700' : 'text-lion-700'">
                    Suma de pesos: <b x-text="sum"></b>% <span x-show="sum !== 100">· Se recomienda que sume 100% (si no, se normaliza automáticamente).</span>
                </p>
            </section>
        </div>

        <div class="space-y-6">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Votación y final</h2>
                <div class="mt-5">
                    <label class="a-label" for="promo_video_url"><x-icon name="youtube" style="brands" /> Video promocional (YouTube)</label>
                    <input id="promo_video_url" type="url" name="promo_video_url" class="a-input" value="{{ old('promo_video_url', $edition->promo_video_url) }}" placeholder="https://www.youtube.com/watch?v=…">
                    <p class="mt-1 text-xs text-slate-600">Se reproduce en bucle, sin sonido y oscurecido de fondo en la portada.</p>
                </div>
                <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl bg-slate-50 p-3">
                    <input type="checkbox" name="web_voting" value="1" class="mt-0.5 rounded border-slate-300 text-ink-700 focus:ring-ink-500" @checked(old('web_voting', $edition->web_voting ?? true))>
                    <span class="text-sm"><b>Votación de la comunidad en la página</b><br><span class="text-slate-600">Desmárcala si la comunidad vota en YouTube, Facebook u otras redes: el botón «Votar» se oculta y los votos se registran a mano en Resultados.</span></span>
                </label>
                <div class="mt-5 space-y-4">
                    <div><label class="a-label" for="voting_starts_at">Inicio de votación</label><input id="voting_starts_at" type="datetime-local" name="voting_starts_at" class="a-input" value="{{ $dt('voting_starts_at') }}"></div>
                    <div><label class="a-label" for="voting_ends_at">Cierre de votación</label><input id="voting_ends_at" type="datetime-local" name="voting_ends_at" class="a-input" value="{{ $dt('voting_ends_at') }}"></div>
                    <div><label class="a-label" for="final_at">Gran final</label><input id="final_at" type="datetime-local" name="final_at" class="a-input" value="{{ $dt('final_at') }}"></div>
                    <div><label class="a-label" for="final_location">Lugar de la final</label><input id="final_location" name="final_location" class="a-input" value="{{ old('final_location', $edition->final_location) }}"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="a-label" for="max_votes_per_user">Votos/persona</label><input id="max_votes_per_user" type="number" min="1" name="max_votes_per_user" class="a-input" value="{{ old('max_votes_per_user', $edition->max_votes_per_user ?? 1) }}"></div>
                        <div><label class="a-label" for="finalists_count">Finalistas</label><input id="finalists_count" type="number" min="1" name="finalists_count" class="a-input" value="{{ old('finalists_count', $edition->finalists_count ?? 5) }}"></div>
                    </div>
                    <div>
                        <label class="a-label" for="voting_email_domain">Restringir votos al dominio</label>
                        <div class="flex rounded-xl shadow-sm"><span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-600">@</span>
                            <input id="voting_email_domain" name="voting_email_domain" class="a-input !rounded-l-none" value="{{ old('voting_email_domain', $edition->voting_email_domain) }}" placeholder="uninavarra.edu.co"></div>
                        <p class="mt-1 text-xs text-slate-600">Vacío = cualquier persona registrada y verificada puede votar.</p>
                    </div>
                </div>
            </section>

            @php
                $ranges = old('vote_grade_thresholds') ?: (new \App\Models\Edition(['vote_grade_thresholds' => $edition->vote_grade_thresholds]))->voteRanges();
                $ranges = collect($ranges)->map(fn ($r) => ['min' => $r['min'] ?? '', 'grade' => $r['grade'] ?? ''])->values();
            @endphp
            <section id="calificacion" class="a-card scroll-mt-24 p-6"
                     x-data="{ c: {{ (int) old('community_weight', $edition->community_weight ?? 30) }}, j: {{ (int) old('jury_weight', $edition->jury_weight ?? 70) }}, e: {{ (int) old('expert_score_weight', $edition->expert_score_weight ?? 0) }},
                               committee: @js(old('committee_mode', $edition->committee_mode ?? 'promedio')), mode: @js(old('vote_grade_mode', $edition->vote_grade_mode ?? 'rangos')),
                               ranges: @js($ranges),
                               get sum() { return this.c + this.j + (this.committee === 'promedio' ? 0 : this.e) } }">
                <h2 class="font-semibold text-ink-900"><x-icon name="scale-balanced" /> Calificación (nota de 1 a 5)</h2>

                <div class="mt-4">
                    <span class="a-label">Comité técnico</span>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2 text-sm"><input type="radio" name="committee_mode" value="promedio" x-model="committee" class="mt-0.5 border-slate-300 text-ink-700 focus:ring-ink-500">
                            <span><b>Promedio del comité</b>: la nota del comité es el promedio de los jurados y el experto; el experto pesa igual que cada jurado.</span></label>
                        <label class="flex items-start gap-2 text-sm"><input type="radio" name="committee_mode" value="separado" x-model="committee" class="mt-0.5 border-slate-300 text-ink-700 focus:ring-ink-500">
                            <span><b>Experto con peso propio</b>: el experto tiene su propio porcentaje, aparte del de los jurados.</span></label>
                    </div>
                </div>

                <p class="mt-5 text-sm text-slate-600">Peso de cada componente en la nota final. Debe sumar 100%.</p>
                <div class="mt-2 grid gap-3" :class="committee === 'promedio' ? 'grid-cols-2' : 'grid-cols-3'">
                    <div><label class="a-label" for="community_weight">% Comunidad</label><input id="community_weight" type="number" min="0" max="100" name="community_weight" x-model.number="c" required class="a-input"></div>
                    <div><label class="a-label" for="jury_weight" x-text="committee === 'promedio' ? '% Comité técnico' : '% Jurados'">% Jurados</label><input id="jury_weight" type="number" min="0" max="100" name="jury_weight" x-model.number="j" required class="a-input"></div>
                    <div x-show="committee === 'separado'"><label class="a-label" for="expert_score_weight">% Experto</label><input id="expert_score_weight" type="number" min="0" max="100" name="expert_score_weight" x-model.number="e" :disabled="committee === 'promedio'" class="a-input"></div>
                </div>
                <p class="mt-2 text-sm font-medium" :class="sum === 100 ? 'text-emerald-700' : 'text-rose-600'">Suma: <span x-text="sum"></span>%</p>

                <div class="mt-5">
                    <label class="a-label" for="expert_title">Nombre del jurado experto</label>
                    <input id="expert_title" name="expert_title" required maxlength="60" class="a-input" value="{{ old('expert_title', $edition->expert_title ?? 'Profesor experto') }}">
                    <p class="mt-1 text-xs text-slate-600">Las cuentas se crean en <a href="{{ route('admin.juries.index') }}" class="font-medium text-ink-700 underline">Jurados</a> marcando la casilla de experto.</p>
                </div>

                <div class="mt-5">
                    <span class="a-label">Nota de la comunidad según los votos</span>
                    <div class="space-y-2">
                        <label class="flex items-start gap-2 text-sm"><input type="radio" name="vote_grade_mode" value="rangos" x-model="mode" class="mt-0.5 border-slate-300 text-ink-700 focus:ring-ink-500">
                            <span><b>Por rangos</b>: defines desde cuántos votos se obtiene cada nota.</span></label>
                        <label class="flex items-start gap-2 text-sm"><input type="radio" name="vote_grade_mode" value="relativo" x-model="mode" class="mt-0.5 border-slate-300 text-ink-700 focus:ring-ink-500">
                            <span><b>Relativa</b>: el emprendimiento más votado obtiene 5,0 y los demás en proporción (0 votos = 1,0).</span></label>
                    </div>
                    <div x-show="mode === 'rangos'" x-collapse class="mt-3">
                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2 text-xs font-medium text-slate-600"><span>Desde (votos)</span><span>Nota</span><span class="w-8"></span></div>
                        <template x-for="(r, i) in ranges" :key="i">
                            <div class="mt-1.5 grid grid-cols-[1fr_1fr_auto] items-center gap-2">
                                <input type="number" min="0" :name="`vote_grade_thresholds[${i}][min]`" x-model="r.min" required class="a-input">
                                <input type="number" min="1" max="5" step="0.1" :name="`vote_grade_thresholds[${i}][grade]`" x-model="r.grade" required class="a-input">
                                <button type="button" @click="ranges.splice(i, 1)" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-600" title="Quitar rango"><x-icon name="xmark" /></button>
                            </div>
                        </template>
                        <button type="button" @click="ranges.push({ min: '', grade: '' })" class="mt-2 text-sm font-medium text-ink-700 hover:underline"><x-icon name="plus" /> Agregar rango</button>
                        <p class="mt-1 text-xs text-slate-600">Cada emprendimiento recibe la nota del rango más alto que alcanza. Con menos votos que el primer rango, la nota es 1.</p>
                        <x-input-error :messages="$errors->get('vote_grade_thresholds')" class="mt-1" />
                    </div>
                </div>
            </section>

            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Visibilidad</h2>
                <div class="mt-4 space-y-3">
                    <label class="flex items-center gap-3"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-ink-700 focus:ring-ink-500" @checked(old('is_published', $edition->is_published))><span class="text-sm">Publicada en el sitio</span></label>
                    <label class="flex items-center gap-3"><input type="checkbox" name="results_published" value="1" class="rounded border-slate-300 text-ink-700 focus:ring-ink-500" @checked(old('results_published', $edition->results_published))><span class="text-sm">Publicar resultados oficiales</span></label>
                </div>
            </section>

            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Banner</h2>
                @if ($edition->banner_url)
                    <img src="{{ $edition->banner_url }}" class="mt-4 aspect-video w-full rounded-xl object-cover" alt="">
                    <label class="mt-2 flex items-center gap-2 text-sm text-rose-600"><input type="checkbox" name="remove_banner" value="1" class="rounded border-slate-300 text-rose-600"> Quitar banner</label>
                @endif
                <input type="file" name="banner" accept="image/*" class="mt-4 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
            </section>

            <div class="flex gap-3">
                <button class="a-btn-primary flex-1 py-3">{{ $edition->exists ? 'Guardar cambios' : 'Crear edición' }}</button>
            </div>
        </div>
    </form>

    @if ($edition->exists)
        <form method="POST" action="{{ route('admin.editions.destroy', $edition) }}" class="mt-8 text-right" onsubmit="return confirm('¿Eliminar esta edición? Solo es posible si no tiene emprendimientos.')">
            @csrf @method('DELETE')
            <button class="text-sm text-rose-600 hover:underline">Eliminar edición</button>
        </form>
    @endif
</x-layouts.admin>
