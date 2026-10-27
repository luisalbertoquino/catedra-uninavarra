<x-layouts.admin :title="$venture->exists ? $venture->name : 'Nuevo emprendimiento'">
    <x-slot:actions>
        @if ($venture->exists)
            <a href="{{ route('ventures.show', $venture) }}" target="_blank" class="a-btn-light hidden sm:inline-flex"><x-icon name="arrow-up-right-from-square" /> Ver en el sitio</a>
        @endif
        <a href="{{ route('admin.ventures.index', ['edicion' => $venture->edition_id]) }}" class="a-btn-light"><x-icon name="arrow-left" /> Volver</a>
    </x-slot:actions>

    @php
        $members = collect(old('members', $members))->values()->map(fn ($m, $i) => $m + ['uid' => isset($m['id']) ? 'm'.$m['id'] : 'n'.$i.uniqid()])->all();
        $field = fn ($f) => old($f, $venture->{$f});
    @endphp

    <form method="POST" enctype="multipart/form-data"
          action="{{ $venture->exists ? route('admin.ventures.update', $venture) : route('admin.ventures.store') }}"
          class="grid gap-6 xl:grid-cols-3">
        @csrf
        @if ($venture->exists) @method('PUT') @endif

        <div class="space-y-6 xl:col-span-2">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Información del emprendimiento</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="a-label" for="name">Nombre *</label><input id="name" name="name" required class="a-input" value="{{ $field('name') }}"></div>
                    <div class="sm:col-span-2"><label class="a-label" for="tagline">Frase corta (eslogan)</label><input id="tagline" name="tagline" maxlength="200" class="a-input" value="{{ $field('tagline') }}" placeholder="En una línea: ¿qué hacen y para quién?"></div>
                    <div class="sm:col-span-2"><label class="a-label" for="description">Descripción</label><textarea id="description" name="description" rows="5" class="a-input">{{ $field('description') }}</textarea></div>
                    <div><label class="a-label" for="problem"><x-icon name="bullseye" /> Problema</label><textarea id="problem" name="problem" rows="4" class="a-input">{{ $field('problem') }}</textarea></div>
                    <div><label class="a-label" for="solution"><x-icon name="lightbulb" /> Solución</label><textarea id="solution" name="solution" rows="4" class="a-input">{{ $field('solution') }}</textarea></div>
                    <div class="sm:col-span-2"><label class="a-label" for="target_market"><x-icon name="users-viewfinder" /> Población objetivo</label><textarea id="target_market" name="target_market" rows="2" class="a-input">{{ $field('target_market') }}</textarea></div>
                    <div><label class="a-label" for="mission"><x-icon name="flag" /> Misión</label><textarea id="mission" name="mission" rows="3" class="a-input">{{ $field('mission') }}</textarea></div>
                    <div><label class="a-label" for="vision"><x-icon name="binoculars" /> Visión</label><textarea id="vision" name="vision" rows="3" class="a-input">{{ $field('vision') }}</textarea></div>
                    <div class="sm:col-span-2" x-data="{ url: @js($field('video_url')) }">
                        <label class="a-label" for="video_url">Video pitch (YouTube, Vimeo o Google Drive)</label>
                        <input id="video_url" type="url" name="video_url" x-model="url" class="a-input" placeholder="https://youtu.be/…">
                        <p class="mt-1 text-xs text-slate-600">En Drive, el archivo debe estar compartido como "Cualquier persona con el enlace".</p>
                    </div>
                </div>
            </section>

            <section class="a-card p-6" x-data="{ rows: @js($members) }">
                <div class="flex items-center justify-between">
                    <div><h2 class="font-semibold text-ink-900">Equipo</h2><p class="text-sm text-slate-600">Normalmente 5 integrantes.</p></div>
                    <button type="button" class="a-btn-light" @click="rows.push({ uid: 'n' + Date.now(), name: '', role: '', academic_program: '', linkedin: '' })"><x-icon name="user-plus" /> Integrante</button>
                </div>
                <div class="mt-5 space-y-3">
                    <template x-for="(row, i) in rows" :key="row.uid">
                        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-start">
                            <div class="shrink-0">
                                <template x-if="row.photo_url"><img :src="row.photo_url" class="h-14 w-14 rounded-full object-cover" alt=""></template>
                                <template x-if="!row.photo_url"><span class="grid h-14 w-14 place-items-center rounded-full bg-lion-50 text-xl"><x-icon name="user" /></span></template>
                            </div>
                            <input type="hidden" :name="`members[${row.uid}][id]`" :value="row.id" x-bind:disabled="!row.id">
                            <div class="grid flex-1 gap-3 sm:grid-cols-2">
                                <input class="a-input" :name="`members[${row.uid}][name]`" x-model="row.name" placeholder="Nombre completo *" required>
                                <input class="a-input" :name="`members[${row.uid}][role]`" x-model="row.role" placeholder="Rol (CEO, Marketing…)">
                                <input class="a-input" :name="`members[${row.uid}][academic_program]`" x-model="row.academic_program" placeholder="Programa académico">
                                <input class="a-input" type="url" :name="`members[${row.uid}][linkedin]`" x-model="row.linkedin" placeholder="https://linkedin.com/in/…">
                                <input type="file" accept="image/*" :name="`members[${row.uid}][photo]`" class="text-xs file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold sm:col-span-2">
                            </div>
                            <button type="button" class="self-end rounded-lg p-2 text-rose-400 hover:bg-rose-50 hover:text-rose-600 sm:self-start" @click="rows.splice(i, 1)" title="Quitar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </div>
                    </template>
                    <p x-show="!rows.length" class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Sin integrantes registrados.</p>
                </div>
            </section>

            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Galería de imágenes</h2>
                @if ($venture->exists && $venture->images->isNotEmpty())
                    <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                        @foreach ($venture->images as $img)
                            <div class="group relative aspect-square overflow-hidden rounded-xl">
                                <img src="{{ $img->url }}" class="h-full w-full object-cover" alt="">
                                <button type="submit" form="del-img-{{ $img->id }}" class="absolute right-1.5 top-1.5 grid h-7 w-7 place-items-center rounded-lg bg-white/90 text-xs text-rose-600 opacity-0 shadow transition group-hover:opacity-100" title="Eliminar"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div x-data="{ previews: [] }" class="mt-4">
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 p-6 text-center hover:border-ink-400 hover:bg-slate-50">
                        <x-icon name="images" class="text-2xl" />
                        <span class="mt-1 text-sm font-medium text-slate-600">Agregar imágenes</span>
                        <span class="text-xs text-slate-500">JPG/PNG, máx. 4 MB c/u, hasta 12</span>
                        <input type="file" name="gallery[]" accept="image/*" multiple class="sr-only" @change="previews = [...$event.target.files].map(f => URL.createObjectURL(f))">
                    </label>
                    <div class="mt-3 grid grid-cols-4 gap-2" x-show="previews.length">
                        <template x-for="src in previews"><img :src="src" class="aspect-square rounded-lg object-cover" alt=""></template>
                    </div>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Clasificación</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="a-label" for="edition_id">Edición *</label>
                        <select id="edition_id" name="edition_id" class="a-input" required>
                            @foreach ($editions as $e)<option value="{{ $e->id }}" @selected($field('edition_id') == $e->id)>{{ $e->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="a-label" for="category_id">Categoría</label>
                        <select id="category_id" name="category_id" class="a-input">
                            <option value="">— Sin categoría —</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($field('category_id') == $c->id)>{{ $c->icon }} {{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="a-label" for="academic_program">Programa académico principal</label><input id="academic_program" name="academic_program" class="a-input" value="{{ $field('academic_program') }}"></div>
                    <div><label class="a-label" for="funding_goal">Monto que solicita a inversionistas (COP)</label><input id="funding_goal" type="number" min="0" step="1000" name="funding_goal" class="a-input" value="{{ $field('funding_goal') }}"></div>
                    <div><label class="a-label" for="startup_amount">Monto estimado para iniciar (COP)</label><input id="startup_amount" type="number" min="0" step="1000" name="startup_amount" class="a-input" value="{{ $field('startup_amount') }}"></div>
                    <div><label class="a-label" for="team_group">Grupo</label><input id="team_group" name="team_group" maxlength="40" class="a-input" value="{{ $field('team_group') }}" placeholder="Ej. Grupo 7"></div>
                    <div><label class="a-label" for="slug">URL amigable</label><input id="slug" name="slug" class="a-input" value="{{ $field('slug') }}" placeholder="se genera automáticamente"></div>
                    <label class="flex items-center gap-3 pt-1"><input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-ink-700 focus:ring-ink-500" @checked(old('is_published', $venture->is_published))><span class="text-sm">Publicado en el sitio</span></label>
                </div>
            </section>

            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Imágenes principales</h2>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    @foreach (['logo' => 'Logo', 'cover' => 'Portada'] as $key => $label)
                        <label x-data="{ src: @js($venture->{$key.'_url'}) }" class="block cursor-pointer">
                            <span class="a-label">{{ $label }}</span>
                            <span class="relative grid aspect-square place-items-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 hover:border-ink-400">
                                <img x-show="src" :src="src" class="absolute inset-0 h-full w-full {{ $key === 'logo' ? 'object-contain p-2' : 'object-cover' }}" alt="">
                                <span x-show="!src" class="text-xs text-slate-500">Subir</span>
                            </span>
                            <input type="file" name="{{ $key }}" accept="image/*" class="sr-only" @change="src = URL.createObjectURL($event.target.files[0])">
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="a-card p-6">
                <h2 class="font-semibold text-ink-900">Contacto y redes</h2>
                <div class="mt-4 space-y-3">
                    <input name="contact_name" class="a-input" value="{{ $field('contact_name') }}" placeholder="Nombre de contacto">
                    <input name="contact_email" type="email" class="a-input" value="{{ $field('contact_email') }}" placeholder="Correo">
                    <input name="contact_phone" class="a-input" value="{{ $field('contact_phone') }}" placeholder="Celular / WhatsApp">
                    <input name="website" type="url" class="a-input" value="{{ $field('website') }}" placeholder="Sitio web https://…">
                    <input name="instagram" type="url" class="a-input" value="{{ $field('instagram') }}" placeholder="Instagram https://…">
                    <input name="facebook" type="url" class="a-input" value="{{ $field('facebook') }}" placeholder="Facebook https://…">
                    <input name="tiktok" type="url" class="a-input" value="{{ $field('tiktok') }}" placeholder="TikTok https://…">
                </div>
            </section>

            <button class="a-btn-primary w-full py-3">{{ $venture->exists ? 'Guardar cambios' : 'Crear emprendimiento' }}</button>
        </div>
    </form>

    @if ($venture->exists)
        @foreach ($venture->images as $img)
            <form id="del-img-{{ $img->id }}" method="POST" action="{{ route('admin.ventures.images.destroy', $img) }}" onsubmit="return confirm('¿Eliminar esta imagen?')">@csrf @method('DELETE')</form>
        @endforeach
        <form method="POST" action="{{ route('admin.ventures.destroy', $venture) }}" class="mt-8 text-right" onsubmit="return confirm('¿Eliminar definitivamente este emprendimiento con sus votos y comentarios?')">
            @csrf @method('DELETE')
            <button class="text-sm text-rose-600 hover:underline">Eliminar emprendimiento</button>
        </form>
    @endif
</x-layouts.admin>
