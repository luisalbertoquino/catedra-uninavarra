<?php

namespace App\Services;

use App\Models\Edition;
use Illuminate\Support\Collection;

/**
 * Ranking de una edición. Nota final de 1 a 5 =
 *   nota comunidad × peso comunidad + nota jurados × peso jurados + nota experto × peso experto
 * (pesos configurables en la edición; por defecto 30 % / 40 % / 30 %).
 *
 * - Nota comunidad: Edition::voteGrade() a partir de los votos.
 * - Notas de jurados y experto: JuryScoring a partir de las evaluaciones con la rúbrica.
 * - Si aún falta algún componente, su peso se reparte proporcionalmente entre los disponibles
 *   y la nota se marca como parcial.
 * - Respaldo: si no hay ninguna evaluación y el admin registró un puntaje manual (0-100),
 *   ese puntaje se usa como nota de jurados y de experto.
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

        $scores = JuryScoring::forEdition($edition);
        $maxVotes = (int) $ventures->max('votes_count');
        $weights = $edition->gradeWeights();

        return $ventures->map(function ($v) use ($edition, $scores, $maxVotes, $weights) {
            $s = $scores->get($v->id, ['jury' => null, 'jury_count' => 0, 'expert' => null, 'expert_count' => 0]);

            $manual = $s['jury'] === null && $s['expert'] === null && $v->committee_score !== null
                ? JuryScoring::toGrade((float) $v->committee_score)
                : null;

            $v->community_grade = $edition->voteGrade($v->votes_count, $maxVotes);
            $v->jury_grade = $s['jury'] ?? $manual;
            $v->expert_grade = $s['expert'] ?? $manual;
            $v->jury_count = $s['jury_count'];
            $v->expert_count = $s['expert_count'];
            $v->committee_source = $manual !== null ? 'manual' : (($s['jury_count'] + $s['expert_count']) ? 'jurados' : null);

            $parts = collect([
                ['community', $v->community_grade],
                ['jury', $v->jury_grade],
                ['expert', $v->expert_grade],
            ])->filter(fn ($p) => $p[1] !== null && $weights[$p[0]] > 0);

            $available = $parts->sum(fn ($p) => $weights[$p[0]]);
            $hasCommittee = $v->jury_grade !== null || $v->expert_grade !== null;

            $v->final_grade = $hasCommittee && $available > 0
                ? round($parts->sum(fn ($p) => $p[1] * $weights[$p[0]]) / $available, 2)
                : null;
            $v->is_partial = $v->final_grade !== null && $available < array_sum($weights);

            return $v;
        })->sortByDesc(fn ($v) => [$v->final_grade ?? -1, $v->votes_count])->values();
    }
}
