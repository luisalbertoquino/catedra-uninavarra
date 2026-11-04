<x-layouts.admin :title="$portfolio->exists ? $portfolio->title : 'Nuevo portafolio'">
    <x-slot:actions>
        <a href="{{ route('admin.portfolios.index') }}" class="a-btn-light"><x-icon name="arrow-left" /> Volver</a>
    </x-slot:actions>

    @php $field = fn ($f) => old($f, $portfolio->{$f}); @endphp

    <form method="POST" enctype="multipart/form-data"
          action="{{ $portfolio->exists ? route('admin.portfolios.update', $portfolio) : route('admin.portfolios.store') }}"
          class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($portfolio->exists) @method('PUT') @endif

        <section class="a-card space-y-5 p-6 xl:col-span-2">
            <div class="grid gap-5 sm:grid-cols-3">
                <div class="sm:col-span-2"><label class="a-label" for="title">Título *</label><input id="title" name="title" required class="a-input" value="{{ $field('title') }}" placeholder="Portafolio LION HUB 2027-1"></div>
                <div><label class="a-label" for="period">Periodo</label><input id="period" name="period" maxlength="20" class="a-input" value="{{ $field('period') }}" placeholder="2027-1"></div>
            </div>
            <div><label class="a-label" for="description">Descripción corta</label><textarea id="description" name="description" rows="3" maxlength="500" class="a-input">{{ $field('description') }}</textarea></div>
            <div><label class="a-label" for="url"><x-icon name="globe" /> Enlace a la página web</label><input id="url" type="url" name="url" class="a-input" value="{{ $field('url') }}" placeholder="https://uninavarra.edu.co/portafolio-lion-hub-…"></div>
            <div>
                <label class="a-label" for="pdf_file"><x-icon name="file-pdf" /> PDF</label>
                @if ($portfolio->pdf_url)
                    <p class="mb-2 text-sm">Actual: <a href="{{ $portfolio->pdf_url }}" target="_blank" class="font-medium text-ink-700 underline">{{ basename($portfolio->pdf) }}</a></p>
                @endif
                <input id="pdf_file" type="file" name="pdf_file" accept="application/pdf" class="text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold">
                <p class="mt-1 text-xs text-slate-600">Máx. 30 MB. {{ $portfolio->pdf ? 'Si subes otro, reemplaza al actual.' : '' }}</p>
                @if ($portfolio->pdf)
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="remove_pdf" value="1"> Quitar el PDF</label>
                @endif
            </div>
            @foreach (['title', 'period', 'description', 'url', 'pdf_file', 'sort'] as $f)<x-input-error :messages="$errors->get($f)" />@endforeach
        </section>

        <section class="a-card h-fit space-y-4 p-6">
            <label class="flex items-start gap-3 text-sm">
                <input type="checkbox" name="is_featured" value="1" class="mt-0.5" @checked(old('is_featured', $portfolio->is_featured))>
                <span><b>Botón flotante</b><br><span class="text-slate-600">Aparece en todas las páginas del sitio. Solo puede haber uno: al marcar este se desmarca el anterior.</span></span>
            </label>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $portfolio->is_published))> <span>Publicado en el sitio</span></label>
            <div><label class="a-label" for="sort">Orden</label><input id="sort" type="number" min="0" name="sort" class="a-input !w-28" value="{{ $field('sort') }}"></div>
            <button class="a-btn-primary w-full"><x-icon name="floppy-disk" style="regular" /> Guardar</button>
        </section>
    </form>
</x-layouts.admin>
