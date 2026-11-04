@props(['profile', 'compact' => false])
{{-- Inversionista invitado: foto, nombre, cargo, temas y trayectoria (se despliega con «Ver trayectoria completa»). --}}
<article {{ $attributes->merge(['class' => 'card-dark reveal flex flex-col overflow-hidden']) }} x-data="{ open: false }">
    <div class="relative aspect-[4/3] overflow-hidden bg-tarjeta">
        @if ($profile->photo_url)
            <img src="{{ $profile->photo_url }}" alt="{{ $profile->name }}" loading="lazy" class="h-full w-full object-cover object-top transition duration-500 hover:scale-[1.03]">
        @else
            <span class="grid h-full w-full place-items-center bg-gradient-to-br from-lion-400 to-magenta-500 font-display text-6xl font-medium text-white">{{ $profile->initials }}</span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <h3 class="font-display text-2xl font-medium leading-tight text-lion-700">{{ $profile->name }}</h3>
        @if ($profile->headline)<p class="mt-1 text-sm font-medium text-ink-700">{{ $profile->headline }}</p>@endif

        @if ($profile->expertise_list)
            <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach ($profile->expertise_list as $tag)
                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-lion-700">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        @if ($profile->bio)
            @if ($compact)
                <p class="mt-4 line-clamp-3 text-sm leading-relaxed text-ink-600">{{ Str::before($profile->bio, "\n") }}</p>
            @else
                <div class="mt-4 space-y-3 text-sm leading-relaxed text-ink-600" :class="open ? '' : 'line-clamp-4'">
                    @foreach (preg_split('/\r?\n/', $profile->bio) as $paragraph)
                        @if (filled($paragraph))<p>{{ $paragraph }}</p>@endif
                    @endforeach
                </div>
                <button type="button" @click="open = !open" class="mt-3 inline-flex items-center gap-1.5 self-start text-sm font-semibold text-lion-700 hover:text-lion-500">
                    <span x-text="open ? 'Ver menos' : 'Ver trayectoria completa'">Ver trayectoria completa</span>
                    <i class="fa-solid fa-chevron-down text-xs transition" :class="open && 'rotate-180'" aria-hidden="true"></i>
                </button>
            @endif
        @endif

        <div class="mt-auto flex flex-wrap items-center gap-3 pt-5">
            @if ($compact)
                <a href="{{ route('investor-profiles') }}#perfil-{{ $profile->id }}" class="inline-flex items-center gap-2 text-sm font-semibold text-lion-700 hover:text-lion-500">Conoce su trayectoria <x-icon name="arrow-right" /></a>
            @endif
            @if ($profile->linkedin)
                <a href="{{ $profile->linkedin }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sm font-medium text-ink-600 hover:text-lion-700"><x-icon name="linkedin" style="brands" /> LinkedIn</a>
            @endif
        </div>
    </div>
</article>
