<x-layouts.admin title="Jurados">
    @php $expertTitle = $edition?->expert_title ?? 'Profesor experto'; @endphp

    @if (session('credentials'))
        <div class="mb-6 rounded-2xl border border-lion-300 bg-lion-50 p-5 text-sm text-ink-900" x-data="{ copied: false }">
            <p class="font-semibold"><x-icon name="key" /> Credenciales del nuevo jurado. Cópialas ahora: la contraseña no se volverá a mostrar.</p>
            <div class="mt-3 flex flex-wrap items-center gap-3 font-mono">
                <span class="rounded-lg bg-white px-3 py-1.5 ring-1 ring-lion-200">{{ session('credentials.email') }}</span>
                <span class="rounded-lg bg-white px-3 py-1.5 ring-1 ring-lion-200">{{ session('credentials.password') }}</span>
                <button type="button" class="a-btn-light !py-1.5 font-sans" @click="navigator.clipboard.writeText(@js("Panel del jurado: ".route('login')."\nUsuario: ".session('credentials.email')."\nContraseña: ".session('credentials.password'))); copied = true">
                    <x-icon name="copy" style="regular" /> <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                </button>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Crear jurado --}}
        <form method="POST" action="{{ route('admin.juries.store') }}" class="a-card h-fit space-y-4 p-6">
            @csrf
            <h2 class="font-semibold text-ink-900">Nuevo jurado</h2>
            <div><label class="a-label" for="j-name">Nombre completo *</label><input id="j-name" name="name" required class="a-input" value="{{ old('name') }}"></div>
            <div><label class="a-label" for="j-email">Correo *</label><input id="j-email" type="email" name="email" required class="a-input" value="{{ old('email') }}"></div>
            <div><label class="a-label" for="j-org">Empresa / cargo</label><input id="j-org" name="organization" class="a-input" value="{{ old('organization') }}" placeholder="Ej. Docente investigador, SENA"></div>
            <div>
                <label class="a-label" for="j-pass">Contraseña</label>
                <input id="j-pass" type="text" name="password" class="a-input" autocomplete="new-password" placeholder="Vacío = se genera una automáticamente">
                <p class="mt-1 text-xs text-slate-600">Mínimo 8 caracteres con letras y números.</p>
            </div>
            <label class="flex items-start gap-3 rounded-xl border border-lion-200 bg-lion-50/60 p-3">
                <input type="checkbox" name="is_expert" value="1" @checked(old('is_expert')) class="mt-0.5 rounded border-slate-300 text-lion-600 focus:ring-lion-500">
                <span class="text-sm"><b>{{ $expertTitle }}</b><br><span class="text-slate-600">@if ($edition)Su evaluación {{ $edition->expertWeightText() }}.@endif</span></span>
            </label>
            <button class="a-btn-primary w-full"><x-icon name="user-plus" /> Crear jurado</button>
            <p class="text-xs text-slate-600">El nombre "{{ $expertTitle }}" y los porcentajes se configuran en <a href="{{ $edition ? route('admin.editions.edit', $edition) : route('admin.editions.index') }}#calificacion" class="font-medium text-ink-700 underline">la edición</a>.</p>
        </form>

        {{-- Lista de jurados --}}
        <div class="space-y-3 xl:col-span-2">
            <div class="a-card flex flex-wrap items-center justify-between gap-3 p-4 text-sm">
                <span class="text-slate-600"><b>{{ $juries->count() }}</b> jurado(s) · {{ $total }} emprendimientos por evaluar en {{ $edition?->name ?? '—' }}</span>
                @if ($edition)
                    <a href="{{ route('admin.results.evaluations', $edition) }}" class="a-btn-light !py-1.5"><x-icon name="table-cells" /> Ver matriz de evaluaciones</a>
                @endif
            </div>

            @forelse ($juries as $j)
                @php $pct = $total ? round($j->evaluations_count / $total * 100) : 0; @endphp
                <div class="a-card p-5" x-data="{ edit: false }">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <x-avatar :name="$j->name" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-ink-900">{{ $j->name }}</span>
                                @if ($j->is_expert)<span class="a-badge gap-1 bg-lion-100 text-lion-800"><i class="fa-solid fa-award" aria-hidden="true"></i> {{ $expertTitle }}</span>@endif
                            </div>
                            <div class="text-xs text-slate-600">{{ $j->email }}@if ($j->organization) · {{ $j->organization }}@endif</div>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-lion-500" style="width: {{ $pct }}%"></div></div>
                                <span class="shrink-0 text-xs text-slate-600">{{ $j->evaluations_count }}/{{ $total }} evaluados @if ($j->evaluations_avg_total_score) · nota prom. {{ number_format($j->evaluations_avg_total_score / 20, 2, ',', '.') }} @endif</span>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button type="button" @click="edit = !edit" class="a-btn-light !py-1.5"><x-icon name="pen-to-square" /> Editar</button>
                            <form method="POST" action="{{ route('admin.juries.destroy', $j) }}" onsubmit="return confirm('¿Eliminar a {{ $j->name }}? También se borrarán sus {{ $j->evaluations_count }} evaluación(es).')">
                                @csrf @method('DELETE')
                                <button class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50" title="Eliminar"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </div>
                    <form x-cloak x-show="edit" x-collapse method="POST" action="{{ route('admin.juries.update', $j) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
                        @csrf @method('PUT')
                        <input name="name" value="{{ $j->name }}" required class="a-input" placeholder="Nombre">
                        <input type="email" name="email" value="{{ $j->email }}" required class="a-input" placeholder="Correo">
                        <input name="organization" value="{{ $j->organization }}" class="a-input" placeholder="Empresa / cargo">
                        <input type="text" name="password" class="a-input" placeholder="Nueva contraseña (opcional)" autocomplete="new-password">
                        <label class="flex items-center gap-2 text-sm sm:col-span-2"><input type="checkbox" name="is_expert" value="1" @checked($j->is_expert) class="rounded border-slate-300 text-lion-600 focus:ring-lion-500"> {{ $expertTitle }}</label>
                        <button class="a-btn-primary sm:col-span-2"><x-icon name="floppy-disk" style="regular" /> Guardar cambios</button>
                    </form>
                </div>
            @empty
                <div class="a-card p-12 text-center text-slate-500">Aún no hay jurados. Crea el primero con el formulario.</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
