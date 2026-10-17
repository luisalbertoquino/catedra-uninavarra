<?php

namespace App\Services;

use App\Models\Edition;
use Illuminate\Support\Collection;

/**
 * Calcula el ranking de una edición siguiendo la metodología de la Cátedra:
 * puntaje comité técnico (0-100) * (100 - peso comunidad)% + votación comunidad normalizada * peso%.
 * El puntaje del comité sale de las evaluaciones de los jurados (JuryScoring); si un emprendimiento
 * aún no tiene evaluaciones se usa el puntaje manual registrado por el admin.
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

        $jury = JuryScoring::forEdition($edition);
        $maxVotes = max(1, (int) $ventures->max('votes_count'));
        $weight = $edition->community_weight / 100;

        return $ventures->map(function ($v) use ($maxVotes, $weight, $jury) {
            $j = $jury->get($v->id);
            $v->jury_count = $j['count'] ?? 0;
            $v->jury_experts = $j['experts'] ?? 0;
            $v->jury_score = $j['score'] ?? null;
            $v->committee_source = $j ? 'jurados' : ($v->committee_score !== null ? 'manual' : null);
            $committee = $j ? $j['score'] : $v->committee_score;

            $v->committee_total = $committee === null ? null : (float) $committee;
            $v->community_score = round($v->votes_count / $maxVotes * 100, 2);
            $v->final_score = $committee === null
                ? null
                : round((float) $committee * (1 - $weight) + $v->community_score * $weight, 2);

            return $v;
        })->sortByDesc(fn ($v) => [$v->final_score ?? -1, $v->votes_count])->values();
    }
}
