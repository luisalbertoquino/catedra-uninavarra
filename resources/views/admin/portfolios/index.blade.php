<x-layouts.admin title="Portafolios">
    <x-slot:actions>
        <a href="{{ route('portfolios') }}" target="_blank" class="a-btn-light hidden sm:inline-flex"><x-icon name="arrow-up-right-from-square" /> Ver en el sitio</a>
        <a href="{{ route('admin.portfolios.create') }}" class="a-btn-primary"><x-icon name="plus" /> Agregar</a>
    </x-slot:actions>

    <p class="mb-6 max-w-3xl text-sm text-slate-600">
        Se listan en la página <b>Portafolios</b> del sitio. El marcado como <b>botón flotante</b> aparece en todas las páginas (abajo a la izquierda) y abre su PDF, o su página web si no tiene PDF.
    </p>

    @if ($portfolios->isEmpty())
        <div class="a-card p-12 text-center text-slate-500">Aún no hay portafolios. <a href="{{ route('admin.portfolios.create') }}" class="font-semibold text-ink-700">Agregar el primero <x-icon name="arrow-right" /></a></div>
    @else
        <div class="a-card divide-y divide-slate-100">
            @foreach ($portfolios as $p)
                <div class="flex flex-wrap items-center gap-4 p-4">
                    <span class="w-20 font-display text-xl font-semibold text-lion-700">{{ $p->period ?: '—' }}</span>
                    <div class="min-w-[12rem] flex-1">
                        <div class="font-semibold text-ink-900">
                            {{ $p->title }}
                            @if ($p->is_featured)<span class="ml-1 rounded-full bg-lion-100 px-2 py-0.5 text-xs font-semibold text-lion-700"><x-icon name="star" /> Botón flotante</span>@endif
                            @unless ($p->is_published)<span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Oculto</span>@endunless
                        </div>
                        <div class="mt-1 flex flex-wrap gap-x-4 text-sm">
                            @if ($p->pdf_url)<a href="{{ $p->pdf_url }}" target="_blank" class="text-ink-700 underline"><x-icon name="file-pdf" /> PDF</a>@endif
                            @if ($p->url)<a href="{{ $p->url }}" target="_blank" rel="noopener" class="text-ink-700 underline"><x-icon name="globe" /> Página web</a>@endif
                        </div>
                    </div>
                    <a href="{{ route('admin.portfolios.edit', $p) }}" class="a-btn-light !py-1.5"><x-icon name="pen" /> Editar</a>
                    <form method="POST" action="{{ route('admin.portfolios.destroy', $p) }}" onsubmit="return confirm(@js("¿Eliminar el {$p->title}?"))">
                        @csrf @method('DELETE')
                        <button class="a-btn !py-1.5 text-rose-600 hover:bg-rose-50" title="Eliminar"><x-icon name="trash-can" style="regular" /></button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
