<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\SupportPledge;
use Illuminate\Support\Collection;

/**
 * Ranking de inversionistas y aliados de una edición: monto comprometido en inversiones y donaciones
 * (intenciones no descartadas). Solo se muestran públicamente quienes dieron su consentimiento.
 */
class InvestorRanking
{
    public static function for(Edition $edition, bool $onlyPublic = true): Collection
    {
        return SupportPledge::query()
            ->join('users', 'users.id', '=', 'support_pledges.user_id')
            ->join('ventures', 'ventures.id', '=', 'support_pledges.venture_id')
            ->where('ventures.edition_id', $edition->id)
            ->where('support_pledges.status', '!=', 'descartado')
            ->when($onlyPublic, fn ($q) => $q->where('users.show_in_investor_ranking', true))
            ->groupBy('users.id', 'users.name', 'users.organization')
            ->selectRaw("users.id, users.name, users.organization,
                SUM(CASE WHEN support_pledges.type IN ('inversion','donacion') THEN COALESCE(support_pledges.amount, 0) ELSE 0 END) AS total_amount,
                COUNT(DISTINCT support_pledges.venture_id) AS ventures_count,
                COUNT(*) AS pledges_count,
                SUM(support_pledges.status = 'concretado') AS completed_count")
            ->orderByDesc('total_amount')->orderByDesc('pledges_count')
            ->get();
    }

    /** Aliados con intenciones activas que no aceptaron aparecer en el ranking público. */
    public static function hiddenCount(Edition $edition): int
    {
        return SupportPledge::query()
            ->join('users', 'users.id', '=', 'support_pledges.user_id')
            ->join('ventures', 'ventures.id', '=', 'support_pledges.venture_id')
            ->where('ventures.edition_id', $edition->id)
            ->where('support_pledges.status', '!=', 'descartado')
            ->where('users.show_in_investor_ranking', false)
            ->distinct()->count('users.id');
    }
}
