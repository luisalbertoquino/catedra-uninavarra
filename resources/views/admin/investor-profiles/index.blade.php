<x-layouts.admin title="Inversionistas invitados">
    <x-slot:actions>
        <a href="{{ route('investor-profiles') }}" target="_blank" class="a-btn-light hidden sm:inline-flex"><x-icon name="arrow-up-right-from-square" /> Ver en el sitio</a>
        <a href="{{ route('admin.investor-profiles.create') }}" class="a-btn-primary"><x-icon name="plus" /> Agregar</a>
    </x-slot:actions>

    <p class="mb-6 max-w-3xl text-sm text-slate-600">
        Se muestran en la página <b>Inversionistas</b> del sitio, en el orden indicado, y los tres primeros también en el inicio.
        No son cuentas de usuario: es solo la presentación de cada persona.
    </p>

    @if ($profiles->isEmpty())
        <div class="a-card p-12 text-center text-slate-500">Aún no hay inversionistas. <a href="{{ route('admin.investor-profiles.create') }}" class="font-semibold text-ink-700">Agregar el primero <x-icon name="arrow-right" /></a></div>
    @else
        <div class="a-card divide-y divide-slate-100">
            @foreach ($profiles as $p)
                <div class="flex flex-wrap items-center gap-4 p-4">
                    <span class="w-8 text-center font-display text-lg font-semibold text-slate-400">{{ $p->sort }}</span>
                    @if ($p->photo_url)
                        <img src="{{ $p->photo_url }}" alt="" class="h-14 w-14 rounded-xl object-cover object-top">
                    @else
                        <span class="grid h-14 w-14 place-items-center rounded-xl bg-lion-50 font-semibold text-lion-700">{{ $p->initials }}</span>
                    @endif
                    <div class="min-w-[12rem] flex-1">
                        <div class="font-semibold text-ink-900">{{ $p->name }} @unless ($p->is_published)<span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Oculto</span>@endunless</div>
                        <div class="text-sm text-slate-600">{{ $p->headline }}</div>
                    </div>
                    <a href="{{ route('admin.investor-profiles.edit', $p) }}" class="a-btn-light !py-1.5"><x-icon name="pen" /> Editar</a>
                    <form method="POST" action="{{ route('admin.investor-profiles.destroy', $p) }}" onsubmit="return confirm(@js("¿Eliminar a {$p->name} de la página de inversionistas?"))">
                        @csrf @method('DELETE')
                        <button class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50" title="Eliminar"><x-icon name="trash-can" style="regular" /></button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
