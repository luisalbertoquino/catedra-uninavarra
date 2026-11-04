<x-layouts.admin :title="$profile->exists ? $profile->name : 'Nuevo inversionista'">
    <x-slot:actions>
        <a href="{{ route('admin.investor-profiles.index') }}" class="a-btn-light"><x-icon name="arrow-left" /> Volver</a>
    </x-slot:actions>

    @php $field = fn ($f) => old($f, $profile->{$f}); @endphp

    <form method="POST" enctype="multipart/form-data"
          action="{{ $profile->exists ? route('admin.investor-profiles.update', $profile) : route('admin.investor-profiles.store') }}"
          class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($profile->exists) @method('PUT') @endif

        <section class="a-card space-y-5 p-6 xl:col-span-2">
            <div><label class="a-label" for="name">Nombre completo *</label><input id="name" name="name" required class="a-input" value="{{ $field('name') }}"></div>
            <div>
                <label class="a-label" for="headline">Cargo o frase corta</label>
                <input id="headline" name="headline" maxlength="160" class="a-input" value="{{ $field('headline') }}" placeholder="Ej. Empresario · Fundador de RIVART Soluciones Alimentarias S.A.S.">
            </div>
            <div>
                <label class="a-label" for="bio">Trayectoria</label>
                <textarea id="bio" name="bio" rows="10" class="a-input">{{ $field('bio') }}</textarea>
                <p class="mt-1 text-xs text-slate-600">Cada renglón es un párrafo. En el sitio se muestran las primeras líneas y el botón «Ver trayectoria completa».</p>
            </div>
            <div>
                <label class="a-label" for="expertise">Temas en los que aporta</label>
                <input id="expertise" name="expertise" maxlength="255" class="a-input" value="{{ $field('expertise') }}" placeholder="Banca de inversión, Capital privado, Fundraising">
                <p class="mt-1 text-xs text-slate-600">Separados por coma; se muestran como etiquetas.</p>
            </div>
            <div><label class="a-label" for="linkedin"><x-icon name="linkedin" style="brands" /> LinkedIn (opcional)</label><input id="linkedin" type="url" name="linkedin" class="a-input" value="{{ $field('linkedin') }}" placeholder="https://www.linkedin.com/in/…"></div>
            @foreach (['name', 'headline', 'bio', 'expertise', 'linkedin', 'photo', 'sort'] as $f)<x-input-error :messages="$errors->get($f)" />@endforeach
        </section>

        <div class="space-y-6">
            <section class="a-card p-6" x-data="{ src: @js($profile->photo_url) }">
                <h2 class="font-semibold text-ink-900">Foto</h2>
                <label class="mt-4 block cursor-pointer">
                    <span class="relative grid aspect-[4/3] place-items-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 hover:border-lion-400">
                        <img x-show="src" :src="src" class="absolute inset-0 h-full w-full object-cover object-top" alt="">
                        <span x-show="!src" class="text-sm text-slate-500"><x-icon name="image" /> Subir foto</span>
                    </span>
                    <input type="file" name="photo" accept="image/*" class="sr-only" @change="src = URL.createObjectURL($event.target.files[0])">
                </label>
                <p class="mt-2 text-xs text-slate-600">JPG, PNG o WEBP, máx. 4 MB. Se recorta a 4:3, así que la cara debe quedar en la parte de arriba.</p>
                @if ($profile->photo)
                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" name="remove_photo" value="1"> Quitar la foto</label>
                @endif
            </section>

            <section class="a-card space-y-4 p-6">
                <div><label class="a-label" for="sort">Orden</label><input id="sort" type="number" min="0" name="sort" class="a-input !w-28" value="{{ $field('sort') }}"></div>
                <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $profile->is_published))> <span>Publicado en el sitio</span></label>
                <button class="a-btn-primary w-full"><x-icon name="floppy-disk" style="regular" /> Guardar</button>
            </section>
        </div>
    </form>
</x-layouts.admin>
