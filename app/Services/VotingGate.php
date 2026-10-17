<?php

namespace App\Services;

use App\Models\User;
use App\Models\Venture;
use Illuminate\Support\Str;

/** Reglas para poder votar por un emprendimiento. Devuelve null si puede, o el motivo si no. */
class VotingGate
{
    public static function denialReason(?User $user, Venture $venture): ?string
    {
        $edition = $venture->edition;

        if (! $edition->isVotingOpen()) {
            return match ($edition->votingStatus()) {
                'proxima' => 'La votación abre el '.$edition->voting_starts_at->translatedFormat('j \d\e F \a \l\a\s g:i a').'.',
                'cerrada' => 'La votación de esta edición ya cerró.',
                default => 'La votación aún no tiene fechas definidas.',
            };
        }
        if (! $user) {
            return 'Inicia sesión para votar.';
        }
        if ($user->isJury() || $user->isAdmin()) {
            return 'Los jurados y administradores no participan en la votación de la comunidad.';
        }
        if (config('catedra.require_verified_to_vote') && ! $user->hasVerifiedEmail()) {
            return 'Verifica tu correo electrónico para poder votar.';
        }
        if ($edition->voting_email_domain && ! Str::endsWith(Str::lower($user->email), '@'.Str::lower($edition->voting_email_domain))) {
            return "Solo pueden votar cuentas @{$edition->voting_email_domain}.";
        }

        return null;
    }

    public static function votesLeft(User $user, Venture $venture): int
    {
        $used = $user->votes()->where('edition_id', $venture->edition_id)->count();

        return max(0, $venture->edition->max_votes_per_user - $used);
    }
}
