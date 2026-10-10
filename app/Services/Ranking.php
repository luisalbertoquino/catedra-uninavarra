<?php

namespace App\Services;

use App\Models\Edition;
use Illuminate\Support\Collection;

/**
 * Calcula el ranking de una edición siguiendo la metodología de la Cátedra:
 * puntaje comité técnico (0-100) * (100 - peso comunidad)% + votación comunidad normalizada * peso%.
 * La votación se normaliza contra el emprendimiento con más votos (= 100).
 */
class Ranking
{
    public static function for(Edition $edition, bool $onlyPublished = true): Collection
    {
        $ventures = $edition->ventures()
            ->when($onlyPublished, fn ($q) => $q->published())
            ->with('category')
            ->withCount(['votes', 'approvedComments as comments_count'])
            ->withAvg('approvedComments as rating_avg', 'rating')
            ->get();

        $maxVotes = max(1, (int) $ventures->max('votes_count'));
        $weight = $edition->community_weight / 100;

        return $ventures->map(function ($v) use ($maxVotes, $weight) {
            $v->community_score = round($v->votes_count / $maxVotes * 100, 2);
            $v->final_score = $v->committee_score === null
                ? null
                : round((float) $v->committee_score * (1 - $weight) + $v->community_score * $weight, 2);

            return $v;
        })->sortByDesc(fn ($v) => [$v->final_score ?? -1, $v->votes_count])->values();
    }
}
