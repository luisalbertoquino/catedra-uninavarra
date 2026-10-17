<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Crypt;

/** Campos que enviaría un humano en formularios protegidos por App\Http\Middleware\BlockBots. */
trait PassesBotProtection
{
    protected function humanFields(int $secondsAgo = 10): array
    {
        return ['website' => '', '_hp_ts' => Crypt::encryptString((string) now()->subSeconds($secondsAgo)->timestamp)];
    }
}
