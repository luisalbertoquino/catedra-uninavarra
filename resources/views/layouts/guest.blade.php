<!DOCTYPE html>
<html lang="es">
<head>
    <x-head title="Acceso" />
</head>
<body class="min-h-screen bg-ink-950 font-sans text-white antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel visual --}}
        <div class="noise relative hidden overflow-hidden lg:block">
            <div class="absolute inset-0 bg-gradient-to-br from-ink-800 via-ink-950 to-ink-950"></div>
            <div class="absolute -left-20 top-20 h-96 w-96 animate-blob rounded-full bg-volt-600/40 blur-3xl"></div>
            <div class="absolute bottom-10 right-0 h-96 w-96 animate-blob rounded-full bg-lion-500/30 blur-3xl [animation-delay:-8s]"></div>
            <div class="absolute inset-0 bg-grid-light [background-size:48px_48px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
            <div class="relative flex h-full flex-col justify-between p-12">
                <a href="{{ route('home') }}"><x-brand /></a>
                <div>
                    <h2 class="font-display text-5xl font-bold leading-tight">Tu voto <span class="text-gradient">impulsa</span><br>a la próxima gran idea.</h2>
                    <p class="mt-5 max-w-md text-lg text-white/60">Únete a la comunidad de la Cátedra UNINAVARRA: vota, opina y conecta con los emprendedores Navarristas.</p>
                    <div class="mt-10 flex gap-8 text-sm">
                        <div><div class="font-display text-3xl font-bold text-lion-300">30%</div><div class="text-white/50">de la evaluación<br>la decide la comunidad</div></div>
                        <div><div class="font-display text-3xl font-bold text-lion-300">5</div><div class="text-white/50">finalistas llegan al<br>LION HUB EVENT</div></div>
                    </div>
                </div>
                <p class="text-xs text-white/40">© {{ date('Y') }} UNINAVARRA · Neiva, Huila</p>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="relative flex flex-col justify-center px-4 py-12 sm:px-12">
            <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 rounded-full bg-volt-600/20 blur-3xl lg:hidden"></div>
            <div class="relative mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-10 inline-block lg:hidden"><x-brand /></a>
                {{ $slot }}
            </div>
        </div>
    </div>
    <x-toasts />
</body>
</html>
