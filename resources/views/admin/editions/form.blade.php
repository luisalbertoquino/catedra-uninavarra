<x-layouts.admin :title="$edition->exists ? 'Editar edición' : 'Nueva edición'">
    <x-slot:actions>
        <a href="{{ route('admin.editions.index') }}" class="a-btn-light">← Volver</a>
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
                        <label class="a-label" for="slug">URL amigable <span class="font-normal text-slate-400">(se genera sola si la dejas vacía)</span></label>
                        <div class="flex rounded-xl shadow-sm"><span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">/ediciones/</span>
                            <input id="slug" name="slug" class="a-input !rounded-l-none" value="{{ old('slug', $edition->slug) }}"></div>
                    </div>
                </div>
            </section>

            <section class="a-card p-6" x-data="{ rows: @js(array_values($rows)) }">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-semibold text-ink-900">Cronograma / fases</h2>
                        <p class="text-sm text-slate-500">Se muestra como línea de tiempo en la página de inicio.</p>
                    </div>
                    <button type="button" class="a-btn-light" @click="rows.push({ title: '', description: '', starts_at: '', ends_at: '' })">+ Fase</button>
                </div>
                <div class="mt-5 space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <div class="flex items-start gap-3">
                                <span class="mt-2 grid h-7 w-7 shrink-0 place-items-center rounded-full bg-ink-800 text-xs font-bold text-white" x-text="i + 1"></span>
                                <div class="grid flex-1 gap-3 sm:grid-cols-6">
                                    <input class="a-input sm:col-span-6" :name="`milestones[${i}][title]`" x-model="row.title" placeholder="Título de la fase" required>
                                    <textarea class="a-input sm:col-span-6" rows="2" :name="`milestones[${i}][description]`" x-model="row.description" placeholder="Descripción breve (opcional)"></textarea>
                                    <label class="text-xs text-slate-500 sm:col-span-3">Inicio<input type="date" class="a-input mt-1" :name="`milestones[${i}][starts_at]`" x-model="row.starts_at"></label>
                                    <label class="text-xs text-slate-500 sm:col-span-3">Fin<input type="date" class="a-input mt-1" :name="`milestones[${i}][ends_at]`" x-model="row.ends_at"></label>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-white hover:text-slate-700" @click="i > 0 && rows.splice(i - 1, 0, rows.splice(i, 1)[0])" title="Subir">↑</button>
                                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-white hover:text-slate-700" @click="i < rows.length - 1 && rows.splice(i + 1, 0, rows.splice(i, 1)[0])" title="Bajar">↓</button>
                                    <button type="button" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 hover:text-rose-600" @click="rows.splice(i, 1)" title="Eliminar">✕</button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p x-show="!rows.length" class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-400">Sin fases. Agrega la primera.</p>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Votación y final</h2>
                <div class="mt-5 space-y-4">
                    <div><label class="a-label" for="voting_starts_at">Inicio de votación</label><input id="voting_starts_at" type="datetime-local" name="voting_starts_at" class="a-input" value="{{ $dt('voting_starts_at') }}"></div>
                    <div><label class="a-label" for="voting_ends_at">Cierre de votación</label><input id="voting_ends_at" type="datetime-local" name="voting_ends_at" class="a-input" value="{{ $dt('voting_ends_at') }}"></div>
                    <div><label class="a-label" for="final_at">Gran final</label><input id="final_at" type="datetime-local" name="final_at" class="a-input" value="{{ $dt('final_at') }}"></div>
                    <div><label class="a-label" for="final_location">Lugar de la final</label><input id="final_location" name="final_location" class="a-input" value="{{ old('final_location', $edition->final_location) }}"></div>
                    <div class="grid grid-cols-3 gap-3">
                        <div><label class="a-label" for="community_weight">% comunidad</label><input id="community_weight" type="number" min="0" max="100" name="community_weight" class="a-input" value="{{ old('community_weight', $edition->community_weight ?? 30) }}"></div>
                        <div><label class="a-label" for="max_votes_per_user">Votos/persona</label><input id="max_votes_per_user" type="number" min="1" name="max_votes_per_user" class="a-input" value="{{ old('max_votes_per_user', $edition->max_votes_per_user ?? 3) }}"></div>
                        <div><label class="a-label" for="finalists_count">Finalistas</label><input id="finalists_count" type="number" min="1" name="finalists_count" class="a-input" value="{{ old('finalists_count', $edition->finalists_count ?? 5) }}"></div>
                    </div>
                    <div>
                        <label class="a-label" for="voting_email_domain">Restringir votos al dominio</label>
                        <div class="flex rounded-xl shadow-sm"><span class="inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500">@</span>
                            <input id="voting_email_domain" name="voting_email_domain" class="a-input !rounded-l-none" value="{{ old('voting_email_domain', $edition->voting_email_domain) }}" placeholder="uninavarra.edu.co"></div>
                        <p class="mt-1 text-xs text-slate-500">Vacío = cualquier persona registrada y verificada puede votar.</p>
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
