@props(['dark' => true, 'size' => 'md'])
{{-- Logo oficial LION HUB EVENT. Sobre fondos oscuros se usa la versión blanca --}}
@php
    $heights = ['sm' => 'h-7', 'md' => 'h-10 lg:h-11', 'lg' => 'h-11 lg:h-12'];
@endphp
<img src="{{ asset($dark ? 'images/lion-hub-event-white.png' : 'images/lion-hub-event.png') }}"
     alt="LION HUB EVENT · Cátedra UNINAVARRA" width="1424" height="432"
     {{ $attributes->merge(['class' => 'w-auto select-none '.($heights[$size] ?? $heights['md'])]) }}>
