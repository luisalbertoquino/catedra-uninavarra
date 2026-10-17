@props(['name', 'style' => 'solid', 'fw' => false])
{{-- Icono Font Awesome en el tono único del sitio. Uso: <x-icon name="trophy" />, <x-icon name="whatsapp" style="brands" /> --}}
<i {{ $attributes->merge(['class' => 'ico fa-'.$style.' fa-'.$name.($fw ? ' fa-fw' : '')]) }} aria-hidden="true"></i>
