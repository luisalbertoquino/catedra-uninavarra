<x-layouts.admin title="Categorías">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="a-card h-fit space-y-4 p-6" x-data="{ icon: @js(old('icon', 'rocket')) }">
            @csrf
            <h2 class="font-semibold text-ink-900">Nueva categoría</h2>
            <div><label class="a-label" for="c-name">Nombre</label><input id="c-name" name="name" required class="a-input" value="{{ old('name') }}"></div>
            <div>
                <label class="a-label" for="c-icon">Icono</label>
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-lion-50 text-lg"><i class="ico fa-solid" :class="'fa-' + icon" aria-hidden="true"></i></span>
                    <input id="c-icon" name="icon" x-model="icon" maxlength="40" class="a-input" placeholder="rocket">
                </div>
                <div class="mt-3 grid grid-cols-6 gap-1.5">
                    @foreach (\App\Models\Category::ICONS as $ic)
                        <button type="button" @click="icon = '{{ $ic }}'" title="{{ $ic }}"
                                class="grid h-9 place-items-center rounded-lg border transition hover:border-lion-400"
                                :class="icon === '{{ $ic }}' ? 'border-lion-400 bg-lion-50' : 'border-slate-200'">
                            <x-icon :name="$ic" />
                        </button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-slate-600">Cualquier icono gratuito de <a href="https://fontawesome.com/search?o=r&m=free&s=solid" target="_blank" rel="noopener" class="font-medium text-ink-700 underline">Font Awesome</a> (escribe solo el nombre, ej. <code>seedling</code>).</p>
            </div>
            <button class="a-btn-primary w-full"><x-icon name="plus" /> Agregar</button>
        </form>

        <div class="a-card divide-y divide-slate-100 lg:col-span-2">
            @foreach ($categories as $c)
                <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="flex flex-wrap items-center gap-3 p-4" x-data="{ icon: @js($c->icon) }">
                    @csrf @method('PUT')
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-lion-50 text-lg"><i class="ico fa-solid" :class="'fa-' + (icon || 'tag')" aria-hidden="true"></i></span>
                    <input name="icon" x-model="icon" maxlength="40" class="a-input !w-40" placeholder="nombre-icono">
                    <input name="name" value="{{ $c->name }}" required class="a-input min-w-[10rem] flex-1">
                    <span class="w-24 text-xs text-slate-600">{{ $c->ventures_count }} emprend.</span>
                    <button class="a-btn-light !py-1.5"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Guardar</button>
                    <button type="submit" form="del-cat-{{ $c->id }}" class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50" title="Eliminar"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                </form>
                <form id="del-cat-{{ $c->id }}" method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('¿Eliminar la categoría {{ $c->name }}?')" class="hidden">@csrf @method('DELETE')</form>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
