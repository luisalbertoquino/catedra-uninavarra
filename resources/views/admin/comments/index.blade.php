<x-layouts.admin title="Comentarios">
    <form method="GET" class="mb-6 flex flex-col gap-3 sm:flex-row">
        <select name="estado" class="a-input sm:!w-48" onchange="this.form.submit()">
            <option value="">Todos</option>
            <option value="pendientes" @selected(request('estado') === 'pendientes')>Ocultos / pendientes</option>
            <option value="aprobados" @selected(request('estado') === 'aprobados')>Publicados</option>
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar en el texto…" class="a-input sm:!w-72">
        <button class="a-btn-light">Filtrar</button>
    </form>

    <div class="a-card divide-y divide-slate-100">
        @forelse ($comments as $c)
            <div @class(['flex flex-col gap-3 p-5 sm:flex-row sm:items-start', 'bg-rose-50/40' => ! $c->is_approved])>
                <x-avatar :name="$c->user->name" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-sm">
                        <span class="font-semibold text-ink-900">{{ $c->user->name }}</span>
                        <span class="text-slate-400">en</span>
                        <a href="{{ route('ventures.show', $c->venture) }}#opiniones" target="_blank" class="font-medium text-ink-700 hover:underline">{{ $c->venture->name }}</a>
                        @if ($c->rating)<span class="inline-flex gap-0.5 text-xs">@for ($s = 1; $s <= 5; $s++)<x-icon name="star" :style="$s <= $c->rating ? 'solid' : 'regular'" />@endfor</span>@endif
                        <span class="text-xs text-slate-400">{{ $c->created_at->diffForHumans() }}</span>
                        @unless ($c->is_approved)<span class="a-badge bg-rose-100 text-rose-700">Oculto</span>@endunless
                    </div>
                    <p class="mt-1 break-words text-slate-700">{{ $c->body }}</p>
                </div>
                <div class="flex shrink-0 gap-2">
                    <form method="POST" action="{{ route('admin.comments.toggle', $c) }}">@csrf @method('PATCH')
                        <button class="a-btn-light !py-1.5"><x-icon :name="$c->is_approved ? 'eye-slash' : 'eye'" /> {{ $c->is_approved ? 'Ocultar' : 'Publicar' }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.comments.destroy', $c) }}" onsubmit="return confirm('¿Eliminar este comentario?')">@csrf @method('DELETE')
                        <button class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Eliminar</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="p-12 text-center text-slate-400">No hay comentarios.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $comments->links() }}</div>
</x-layouts.admin>
