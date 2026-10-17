<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\SupportPledge;
use Illuminate\Support\Collection;

/**
 * Ranking de inversionistas y aliados de una edición. Solo cuentan los apoyos CONCRETADOS:
 * la intención de apoyo se registra en la plataforma, el Centro de Emprendimiento hace el contacto
 * y, cuando el apoyo se materializa, el admin la marca como "concretado".
 * Solo se muestran públicamente quienes dieron su consentimiento.
 */
class InvestorRanking
{
    private static function base(Edition $edition)
    {
        return SupportPledge::query()
            ->join('users', 'users.id', '=', 'support_pledges.user_id')
            ->join('ventures', 'ventures.id', '=', 'support_pledges.venture_id')
            ->where('ventures.edition_id', $edition->id)
            ->where('support_pledges.status', 'concretado');
    }

    public static function for(Edition $edition, bool $onlyPublic = true): Collection
    {
        return self::base($edition)
            ->when($onlyPublic, fn ($q) => $q->where('users.show_in_investor_ranking', true))
            ->groupBy('users.id', 'users.name', 'users.organization')
            ->selectRaw("users.id, users.name, users.organization,
                SUM(CASE WHEN support_pledges.type IN ('inversion','donacion') THEN COALESCE(support_pledges.amount, 0) ELSE 0 END) AS total_amount,
                COUNT(DISTINCT support_pledges.venture_id) AS ventures_count,
                COUNT(*) AS completed_count")
            ->orderByDesc('total_amount')->orderByDesc('completed_count')
            ->get();
    }

    /** Aliados con apoyos concretados que no aceptaron aparecer en el ranking público. */
    public static function hiddenCount(Edition $edition): int
    {
        return self::base($edition)->where('users.show_in_investor_ranking', false)->distinct()->count('users.id');
    }
}
