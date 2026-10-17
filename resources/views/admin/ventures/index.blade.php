<x-layouts.admin title="Emprendimientos">
    <x-slot:actions>
        <a href="{{ route('admin.ventures.create', ['edicion' => $editionId]) }}" class="a-btn-primary"><x-icon name="plus" /> Nuevo</a>
    </x-slot:actions>

    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <select name="edicion" class="a-input sm:!w-64" onchange="this.form.submit()">
            <option value="">Todas las ediciones</option>
            @foreach ($editions as $e)
                <option value="{{ $e->id }}" @selected($editionId == $e->id)>{{ $e->name }}</option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre…" class="a-input sm:!w-72">
        <button class="a-btn-light">Filtrar</button>
    </form>

    <div class="a-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead>
                    <tr><th>Emprendimiento</th><th>Categoría</th><th class="text-center">Equipo</th><th class="text-center">Votos</th><th class="text-center">Coment.</th><th class="text-center">Apoyos</th><th>Estado</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ventures as $v)
                        <tr class="hover:bg-slate-50">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if ($v->logo_url)
                                        <img src="{{ $v->logo_url }}" class="h-10 w-10 rounded-xl border object-contain" alt="">
                                    @elseif ($v->cover_url)
                                        <img src="{{ $v->cover_url }}" class="h-10 w-10 rounded-xl object-cover" alt="">
                                    @else
                                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-ink-800 text-xs font-bold text-lion-300">{{ $v->initials }}</span>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.ventures.edit', $v) }}" class="font-semibold text-ink-900 hover:underline">{{ $v->name }}</a>
                                        <div class="max-w-xs truncate text-xs text-slate-500">{{ $v->edition->period_label }} · {{ $v->tagline }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">@if ($v->category)<x-icon :name="$v->category->icon ?: 'tag'" fw /> {{ $v->category->name }}@else — @endif</td>
                            <td class="text-center">{{ $v->members_count }}</td>
                            <td class="text-center font-semibold">{{ $v->votes_count }}</td>
                            <td class="text-center">{{ $v->comments_count }}</td>
                            <td class="text-center">{{ $v->pledges_count }}</td>
                            <td class="whitespace-nowrap">
                                @if ($v->is_published)<span class="a-badge bg-emerald-100 text-emerald-800">Publicado</span>@else<span class="a-badge bg-slate-100 text-slate-500">Borrador</span>@endif
                                @if ($v->is_finalist)<span class="a-badge bg-lion-100 text-lion-800">Finalista</span>@endif
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <a href="{{ route('ventures.show', $v) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-ink-900"><x-icon name="arrow-up-right-from-square" /> Ver</a>
                                <a href="{{ route('admin.ventures.edit', $v) }}" class="ml-3 text-sm font-semibold text-ink-700 hover:text-ink-900">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-slate-400">No hay emprendimientos. <a href="{{ route('admin.ventures.create', ['edicion' => $editionId]) }}" class="font-semibold text-ink-700">Crea el primero <x-icon name="arrow-right" /></a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $ventures->links() }}</div>
</x-layouts.admin>
