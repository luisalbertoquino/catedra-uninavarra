<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protección anti-bots para formularios públicos (registro, recuperar contraseña):
 *  - Campo trampa "website" invisible: los humanos lo dejan vacío, los bots lo llenan.
 *  - Marca de tiempo cifrada: un humano tarda al menos unos segundos en llenar el formulario.
 * Usar junto con el componente <x-honeypot /> dentro del formulario.
 */
class BlockBots
{
    private const MIN_SECONDS = 3;
    private const MAX_SECONDS = 60 * 60 * 2;

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post')) {
            $elapsed = null;
            try {
                $elapsed = now()->timestamp - (int) Crypt::decryptString((string) $request->input('_hp_ts'));
            } catch (\Throwable) {
                // marca ausente o manipulada
            }

            if (filled($request->input('website')) || $elapsed === null || $elapsed < self::MIN_SECONDS || $elapsed > self::MAX_SECONDS) {
                Log::warning('Formulario bloqueado por protección anti-bots', ['ip' => $request->ip(), 'path' => $request->path(), 'elapsed' => $elapsed]);

                throw ValidationException::withMessages([
                    'email' => $elapsed !== null && $elapsed > self::MAX_SECONDS
                        ? 'El formulario expiró. Recarga la página e inténtalo de nuevo.'
                        : 'No pudimos procesar el envío. Espera unos segundos e inténtalo de nuevo.',
                ]);
            }
        }

        return $next($request);
    }
}
