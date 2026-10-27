<!DOCTYPE html>
<html lang="es">
<head>
    <x-head title="Acceso" />
</head>
<body class="min-h-screen bg-white font-sans text-ink-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-2">
        {{-- Panel visual --}}
        <div class="surface-dark ecos-g-profundo hidden lg:block">
            <div class="flex h-full flex-col items-center justify-between p-12 text-white">
                <a href="{{ route('home') }}"><x-brand /></a>
                <div class="tarjeta-ecos w-full max-w-lg text-left">
                    <span class="tarjeta-ecos__tab">Cátedra UNINAVARRA</span>
                    <div class="tarjeta-ecos__body">
                        <h2 class="font-display text-5xl font-medium leading-[1.05] tracking-tight text-lion-700">Tu voto impulsa a la próxima gran idea.</h2>
                        <div class="divisor-ecos"></div>
                        <p class="text-ink-700">Únete a la comunidad: vota, opina y conecta con los emprendedores Navarristas. Si eres empresario o mentor, regístrate como aliado.</p>
                        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                            <div class="rounded-ecos bg-white p-4"><div class="font-display text-3xl text-lion-600">30%</div><div class="text-ink-600">de la nota la decide la comunidad</div></div>
                            <div class="rounded-ecos bg-white p-4"><div class="font-display text-3xl text-lion-600">5</div><div class="text-ink-600">finalistas llegan al LION HUB EVENT</div></div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-white">© {{ date('Y') }} UNINAVARRA · Neiva, Huila</p>
            </div>
        </div>

        {{-- Formulario --}}
        <div class="surface-light relative flex flex-col justify-center px-4 py-12 sm:px-12">
            <div class="relative mx-auto w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-10 inline-block lg:hidden"><x-brand :dark="false" /></a>
                {{ $slot }}
            </div>
        </div>
    </div>
    <x-toasts />
</body>
</html>
