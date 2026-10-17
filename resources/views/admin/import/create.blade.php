<x-layouts.admin title="Importar emprendimientos desde Excel">
    @if ($report = session('report'))
        <div class="a-card mb-6 p-6">
            <h2 class="font-semibold text-ink-900"><x-icon name="circle-check" /> Resultado de la importación · {{ $report['edition'] }}</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Creados</div><div class="text-2xl font-bold">{{ count($report['created']) }}</div></div>
                <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Actualizados</div><div class="text-2xl font-bold">{{ count($report['updated']) }}</div></div>
                <div class="rounded-xl bg-slate-50 p-4"><div class="text-xs text-slate-500">Logos asignados</div><div class="text-2xl font-bold">{{ $report['logos'] }}</div></div>
            </div>
            @if ($report['created'] || $report['updated'])
                <p class="mt-4 text-sm text-slate-600">{{ implode(' · ', array_merge($report['created'], $report['updated'])) }}</p>
            @endif
            @if ($report['warnings'])
                <details class="mt-4 rounded-xl border border-lion-200 bg-lion-50 p-4 text-sm text-lion-900">
                    <summary class="cursor-pointer font-semibold"><x-icon name="triangle-exclamation" /> {{ count($report['warnings']) }} dato(s) por completar</summary>
                    <ul class="mt-2 list-inside list-disc space-y-0.5">@foreach ($report['warnings'] as $w)<li>{{ $w }}</li>@endforeach</ul>
                </details>
            @endif
            <a href="{{ route('admin.ventures.index') }}" class="a-btn-light mt-4"><x-icon name="lightbulb" /> Revisar emprendimientos</a>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <form method="POST" action="{{ route('admin.import.store') }}" enctype="multipart/form-data" class="a-card space-y-5 p-6 xl:col-span-2" x-data="{ logos: [] }">
            @csrf
            <div>
                <label class="a-label" for="edition_id">Edición (convocatoria) *</label>
                <select id="edition_id" name="edition_id" class="a-input" required>
                    @foreach ($editions as $e)<option value="{{ $e->id }}" @selected(old('edition_id', $current?->id) == $e->id)>{{ $e->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="a-label" for="file">Archivo de Excel (.xlsx) *</label>
                <input id="file" type="file" name="file" accept=".xlsx,.xls" required class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-ink-800 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
            </div>
            <div>
                <label class="a-label" for="logos">Logos (opcional, puedes seleccionar varios)</label>
                <input id="logos" type="file" name="logos[]" accept="image/*" multiple @change="logos = [...$event.target.files].map(f => f.name)"
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                <p class="mt-1 text-xs text-slate-500">Nombra cada archivo como el emprendimiento (ej. <code>Rayvix.png</code>). Se asignan por nombre aproximado. Si no subes logos se usan las imágenes pegadas en la columna "Logo" del Excel.</p>
                <p class="mt-2 text-xs text-slate-600" x-show="logos.length" x-text="logos.length + ' logo(s): ' + logos.join(', ')"></p>
            </div>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="publish" value="1" checked class="rounded border-slate-300 text-ink-700 focus:ring-ink-500"> Publicar los emprendimientos nuevos en el sitio</label>
            <button class="a-btn-primary"><x-icon name="file-import" /> Importar</button>
        </form>

        <aside class="a-card h-fit p-6 text-sm text-slate-600">
            <h2 class="font-semibold text-ink-900">Formato del Excel</h2>
            <p class="mt-2">Primera fila con estos encabezados (no importan tildes ni mayúsculas):</p>
            <ol class="mt-2 list-inside list-decimal space-y-0.5 text-xs">
                <li>Nombre (integrante)</li><li>Programa</li><li>Grupo</li><li>Nombre de la Idea de Negocio</li><li>Descripción de la idea</li>
                <li>Población objetivo</li><li>Misión</li><li>Visión</li><li>Monto estimado para iniciar</li><li>Monto que solicitaría a un inversionista</li><li>Logo (imagen, opcional)</li>
            </ol>
            <p class="mt-3 text-xs">La fila con el nombre de la idea inicia el emprendimiento; las siguientes con nombre y programa son sus integrantes. Si importas de nuevo, los emprendimientos se actualizan (por grupo o nombre) y no se duplican.</p>
        </aside>
    </div>
</x-layouts.admin>
