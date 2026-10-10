<x-layouts.admin title="Categorías">
    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="a-card h-fit space-y-4 p-6">
            @csrf
            <h2 class="font-semibold text-ink-900">Nueva categoría</h2>
            <div><label class="a-label" for="c-name">Nombre</label><input id="c-name" name="name" required class="a-input" value="{{ old('name') }}"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="a-label" for="c-icon">Emoji</label><input id="c-icon" name="icon" maxlength="16" class="a-input" value="{{ old('icon') }}" placeholder="🚀"></div>
                <div><label class="a-label" for="c-color">Color</label><input id="c-color" type="color" name="color" value="{{ old('color', '#f5b70a') }}" class="h-[38px] w-full cursor-pointer rounded-xl border border-slate-300"></div>
            </div>
            <button class="a-btn-primary w-full">Agregar</button>
        </form>

        <div class="a-card divide-y divide-slate-100 lg:col-span-2">
            @foreach ($categories as $c)
                <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="flex flex-wrap items-center gap-3 p-4">
                    @csrf @method('PUT')
                    <span class="grid h-10 w-10 place-items-center rounded-xl text-xl" style="background: {{ $c->color }}22">{{ $c->icon }}</span>
                    <input name="icon" value="{{ $c->icon }}" maxlength="16" class="a-input !w-16 text-center">
                    <input name="name" value="{{ $c->name }}" required class="a-input min-w-[10rem] flex-1">
                    <input type="color" name="color" value="{{ $c->color }}" class="h-[38px] w-12 cursor-pointer rounded-lg border border-slate-300">
                    <span class="w-24 text-xs text-slate-500">{{ $c->ventures_count }} emprend.</span>
                    <button class="a-btn-light !py-1.5">Guardar</button>
                    <button type="submit" form="del-cat-{{ $c->id }}" class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50">✕</button>
                </form>
                <form id="del-cat-{{ $c->id }}" method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('¿Eliminar la categoría {{ $c->name }}?')" class="hidden">@csrf @method('DELETE')</form>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
