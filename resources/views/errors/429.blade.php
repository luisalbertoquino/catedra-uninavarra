<!DOCTYPE html>
<html lang="es">
<head>
    <x-head title="Demasiadas solicitudes" />
</head>
<body class="grid min-h-screen place-items-center bg-ink-950 px-4 font-sans text-white antialiased">
    <div class="max-w-md text-center">
        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-lion-400/10"><x-icon name="hourglass-half" class="text-2xl" /></span>
        <h1 class="mt-6 font-display text-4xl font-semibold">Vas muy rápido</h1>
        <p class="mt-3 text-white/60">Recibimos demasiadas solicitudes desde tu conexión. Espera un minuto y vuelve a intentarlo.</p>
        <a href="{{ url('/') }}" class="btn-lion mt-8">Volver al inicio</a>
    </div>
</body>
</html>
