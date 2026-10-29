@props(['label' => 'Continuar con Google', 'perfil' => null])
{{-- Botón de ingreso con Google; solo aparece si las credenciales están configuradas en el .env --}}
@if (\App\Http\Controllers\Auth\GoogleController::enabled())
    <div {{ $attributes }}>
        <a href="{{ route('google.redirect', array_filter(['perfil' => $perfil])) }}"
           class="flex w-full items-center justify-center gap-3 rounded-full border-2 border-ink-900/15 bg-white px-6 py-3 text-base font-semibold text-ink-900 transition hover:border-lion-400 hover:bg-lavanda/30">
            <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34.1 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
            {{ $label }}
        </a>
        <div class="mt-6 flex items-center gap-3 text-xs text-ink-500">
            <span class="h-px flex-1 bg-ink-900/10"></span> o con tu correo <span class="h-px flex-1 bg-ink-900/10"></span>
        </div>
    </div>
@endif
