<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

/** Valida el token de Cloudflare Turnstile. Si no hay llaves configuradas, no exige nada. */
class HumanVerification implements ValidationRule
{
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.secret_key'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::enabled()) {
            return;
        }
        if (blank($value)) {
            $fail('Confirma que no eres un robot.');

            return;
        }

        try {
            $ok = Http::asForm()->timeout(8)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ])->json('success') === true;
        } catch (\Throwable) {
            $ok = false;
        }

        if (! $ok) {
            $fail('No pudimos verificar que no eres un robot. Inténtalo de nuevo.');
        }
    }
}
