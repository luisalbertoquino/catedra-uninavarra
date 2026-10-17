<x-layouts.admin title="Ediciones">
    <x-slot:actions>
        <a href="{{ route('admin.editions.create') }}" class="a-btn-primary"><x-icon name="plus" /> Nueva edición</a>
    </x-slot:actions>

    <p class="mb-6 max-w-3xl text-sm text-slate-500">Cada semestre crea una nueva edición: se precarga con la configuración y el cronograma de la anterior. La edición <b>activa</b> es la que se muestra en la página de inicio y en la que vota la comunidad.</p>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($editions as $e)
            <div @class(['a-card relative flex flex-col p-6', 'ring-2 ring-lion-400' => $e->is_active])>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-display text-3xl font-semibold text-slate-300">{{ $e->period_label }}</div>
                        <h3 class="mt-1 font-semibold text-ink-900">{{ $e->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $e->tagline }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        @if ($e->is_active)<span class="a-badge gap-1 bg-lion-100 text-lion-800"><i class="fa-solid fa-star" aria-hidden="true"></i> Activa</span>@endif
                        @unless ($e->is_published)<span class="a-badge bg-slate-100 text-slate-500">Oculta</span>@endunless
                        @if ($e->results_published)<span class="a-badge bg-emerald-100 text-emerald-800">Resultados</span>@endif
                    </div>
                </div>
                <dl class="mt-5 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">Emprendimientos</dt><dd class="text-lg font-bold">{{ $e->ventures_count }}</dd></div>
                    <div class="rounded-xl bg-slate-50 p-3"><dt class="text-xs text-slate-500">Votos</dt><dd class="text-lg font-bold">{{ number_format($e->votes_count, 0, ',', '.') }}</dd></div>
                </dl>
                <p class="mt-4 text-xs text-slate-500">
                    Votación: {{ $e->voting_starts_at?->translatedFormat('j M') ?? '—' }} <x-icon name="arrow-right" class="text-[10px]" /> {{ $e->voting_ends_at?->translatedFormat('j M Y') ?? '—' }}<br>
                    Final: {{ $e->final_at?->translatedFormat('j M Y, g:i a') ?? '—' }}
                </p>
                <div class="mt-auto flex flex-wrap gap-2 pt-5">
                    <a href="{{ route('admin.editions.edit', $e) }}" class="a-btn-light">Editar</a>
                    <a href="{{ route('admin.ventures.index', ['edicion' => $e->id]) }}" class="a-btn-light">Emprendimientos</a>
                    @unless ($e->is_active)
                        <form method="POST" action="{{ route('admin.editions.activate', $e) }}">@csrf<button class="a-btn-lion"><x-icon name="power-off" /> Activar</button></form>
                    @endunless
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
