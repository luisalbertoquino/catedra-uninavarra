@props(['src', 'title', 'url' => null, 'lazy' => false])
{{--
    Video incrustado con botón propio de pantalla completa. YouTube muestra los Shorts (videos verticales)
    con su reproductor de Shorts, que no trae botón de pantalla completa: aquí se pone en pantalla completa
    el contenedor completo. Si el navegador no lo permite (iPhone), se abre el video en YouTube.
--}}
<div x-data="{
        fs: false,
        toggle() {
            const box = this.$refs.box;
            if (document.fullscreenElement || document.webkitFullscreenElement) {
                return (document.exitFullscreen || document.webkitExitFullscreen).call(document);
            }
            if (box.requestFullscreen) return box.requestFullscreen();
            if (box.webkitRequestFullscreen) return box.webkitRequestFullscreen();
            if (@js($url)) window.open(@js($url), '_blank', 'noopener');
        },
     }"
     @fullscreenchange.document="fs = document.fullscreenElement === $refs.box"
     @webkitfullscreenchange.document="fs = document.webkitFullscreenElement === $refs.box">
    <div x-ref="box" {{ $attributes->merge(['class' => 'relative aspect-video overflow-hidden bg-black [&:fullscreen]:rounded-none [&:fullscreen]:border-0']) }}>
        <iframe src="{{ $src }}" title="{{ $title }}" class="absolute inset-0 h-full w-full" @if ($lazy) loading="lazy" @endif
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen" allowfullscreen></iframe>
        <button type="button" x-show="fs" x-cloak @click="toggle()"
                class="absolute left-1/2 top-3 z-10 inline-flex -translate-x-1/2 items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold backdrop-blur"
                style="background: rgba(0, 0, 0, .7); color: #fff">
            <x-icon name="compress" x-bind:style="'color:#fff'" /> Salir de pantalla completa
        </button>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <button type="button" @click="toggle()" class="inline-flex items-center gap-2 font-semibold text-lion-700 hover:text-lion-500">
            <x-icon name="expand" /> Ver en pantalla completa
        </button>
        @if ($url)
            <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-ink-600 hover:text-lion-700"><x-icon name="arrow-up-right-from-square" /> Abrir el video en una pestaña nueva</a>
        @endif
    </div>
</div>
