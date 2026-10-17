@props(['name' => '?', 'src' => null, 'size' => 'md'])
@php
    $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $palettes = ['from-lion-300 to-lion-600', 'from-volt-400 to-volt-600', 'from-aqua-400 to-volt-500', 'from-pink-400 to-volt-500', 'from-emerald-300 to-aqua-500'];
    $palette = $palettes[crc32($name) % count($palettes)];
    $sizes = ['xs' => 'h-6 w-6 text-[10px]', 'sm' => 'h-8 w-8 text-xs', 'md' => 'h-10 w-10 text-sm', 'lg' => 'h-16 w-16 text-lg', 'xl' => 'h-24 w-24 text-2xl'];
@endphp
@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" loading="lazy" {{ $attributes->merge(['class' => 'shrink-0 rounded-lg object-cover '.$sizes[$size]]) }}>
@else
    <span {{ $attributes->merge(['class' => "inline-grid shrink-0 place-items-center rounded-full bg-gradient-to-br font-bold text-ink-950 $palette ".$sizes[$size]]) }} aria-hidden="true">{{ $initials }}</span>
@endif
