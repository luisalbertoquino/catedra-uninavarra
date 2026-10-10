@props(['compact' => false, 'dark' => true])
{{-- Marca provisional del sitio: reemplazar por el logo oficial que entregue Comunicaciones --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="relative grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-lion-300 via-lion-500 to-lion-700 shadow-lg shadow-lion-500/30">
        <svg viewBox="0 0 24 24" class="h-5 w-5 text-ink-950" fill="currentColor" aria-hidden="true">
            <path d="M12 2l2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 14.4 7.2 16.9l.9-5.4L4.2 7.7l5.4-.8L12 2z" opacity=".35"/>
            <path d="M7 6h2.6v7.2a2.4 2.4 0 104.8 0V6H17v7.2a5 5 0 11-10 0V6z"/>
        </svg>
    </span>
    @unless ($compact)
        <span class="leading-none">
            <span @class(['block font-display text-[15px] font-bold tracking-tight', 'text-white' => $dark, 'text-ink-900' => ! $dark])>Cátedra UNINAVARRA</span>
            <span @class(['block text-[10px] font-semibold uppercase tracking-[.25em]', 'text-lion-400' => $dark, 'text-lion-700' => ! $dark])>Lion Hub · Emprende</span>
        </span>
    @endunless
</span>
