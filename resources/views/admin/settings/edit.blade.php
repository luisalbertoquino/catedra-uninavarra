<x-layouts.admin title="Configuración del sitio">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="grid gap-6 xl:grid-cols-3">
        @csrf @method('PUT')
        <div class="space-y-6 xl:col-span-2">
            @foreach ($groups as $group => $fields)
                <section class="a-card p-6">
                    <h2 class="font-semibold text-ink-900">{{ $group }}</h2>
                    <div class="mt-5 space-y-4">
                        @foreach ($fields as $key => [$label, $default, $type, $hint])
                            <div>
                                <label class="a-label" for="{{ $key }}">{{ $label }}</label>
                                @if ($type === 'textarea')
                                    <textarea id="{{ $key }}" name="{{ $key }}" rows="{{ str_starts_with($key, 'welcome_') ? 5 : 3 }}" class="a-input">{{ old($key, \App\Models\Setting::get($key)) }}</textarea>
                                @else
                                    <input id="{{ $key }}" type="{{ $type }}" name="{{ $key }}" class="a-input" value="{{ old($key, \App\Models\Setting::get($key)) }}">
                                @endif
                                @if ($hint)<p class="mt-1 text-xs text-slate-600">{{ $hint }}</p>@endif
                                @if (str_starts_with($key, 'welcome_'))
                                    <a href="{{ route('admin.settings.welcome', ['tipo' => $key === 'welcome_jury' ? 'jurado' : 'admin']) }}" target="_blank" class="mt-1.5 inline-flex items-center gap-1.5 text-xs font-semibold text-ink-700 hover:underline"><x-icon name="eye" style="regular" /> Ver cómo queda el correo (guarda primero los cambios)</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
        <div class="space-y-6">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Otros datos editables</h2>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li><x-icon name="calendar-days" fw /> <a href="{{ route('admin.editions.index') }}" class="font-medium text-ink-700 underline">Ediciones</a>: fechas, video, lema, cronograma, rúbrica y porcentajes de la nota.</li>
                    <li><x-icon name="lightbulb" fw /> <a href="{{ route('admin.ventures.index') }}" class="font-medium text-ink-700 underline">Emprendimientos</a>: textos, logos, equipo, videos y redes.</li>
                    <li><x-icon name="gavel" fw /> <a href="{{ route('admin.juries.index') }}" class="font-medium text-ink-700 underline">Jurados</a>: cuentas y jurado experto.</li>
                    <li><x-icon name="tags" fw /> <a href="{{ route('admin.categories.index') }}" class="font-medium text-ink-700 underline">Categorías</a>: nombres e iconos.</li>
                </ul>
            </section>
            <button class="a-btn-primary w-full py-3"><x-icon name="floppy-disk" style="regular" /> Guardar configuración</button>
        </div>
    </form>
</x-layouts.admin>
