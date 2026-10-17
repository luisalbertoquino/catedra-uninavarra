<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/** Rechaza correos de servicios temporales/desechables, muy usados para crear cuentas falsas. */
class NotDisposableEmail implements ValidationRule
{
    private const DOMAINS = [
        '10minutemail.com', '20minutemail.com', 'mailinator.com', 'guerrillamail.com', 'guerrillamail.net', 'sharklasers.com',
        'yopmail.com', 'yopmail.net', 'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'tempmailo.com', 'throwawaymail.com',
        'trashmail.com', 'getnada.com', 'nada.email', 'dispostable.com', 'maildrop.cc', 'mailnesia.com', 'mintemail.com',
        'fakeinbox.com', 'emailondeck.com', 'mohmal.com', 'moakt.com', 'tmail.ws', 'tmpmail.org', 'burnermail.io',
        'mailcatch.com', 'spamgourmet.com', 'mytemp.email', 'tempinbox.com', 'luxusmail.org', 'emailfake.com', 'inboxkitten.com',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = Str::lower(Str::after((string) $value, '@'));
        if (in_array($domain, self::DOMAINS, true)) {
            $fail('No se permiten correos temporales. Usa tu correo personal o institucional.');
        }
    }
}
