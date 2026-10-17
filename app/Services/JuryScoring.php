<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\Evaluation;
use Illuminate\Support\Collection;

/**
 * Notas del comité a partir de las evaluaciones con la rúbrica (cada evaluación se guarda en 0-100).
 * Se separan en dos componentes, ambos en escala 1 a 5:
 *  - jurados: promedio de los jurados normales
 *  - experto: promedio del Profesor experto (o de los expertos, si hay varios)
 */
class JuryScoring
{
    /** Convierte un puntaje de rúbrica 0-100 a nota 0-5 (rúbrica en escala 1..5 → nota 1..5) */
    public static function toGrade(float $score100): float
    {
        return round($score100 / 20, 2);
    }

    /** @return Collection<int, array{jury: ?float, jury_count: int, expert: ?float, expert_count: int}> indexado por venture_id */
    public static function forEdition(Edition $edition): Collection
    {
        return Evaluation::query()
            ->where('evaluations.edition_id', $edition->id)
            ->join('users', 'users.id', '=', 'evaluations.user_id')
            ->get(['evaluations.venture_id', 'evaluations.total_score', 'users.is_expert'])
            ->groupBy('venture_id')
            ->map(function ($evals) {
                $jury = $evals->where('is_expert', false);
                $expert = $evals->where('is_expert', true);

                return [
                    'jury' => $jury->isEmpty() ? null : self::toGrade($jury->avg(fn ($e) => (float) $e->total_score)),
                    'jury_count' => $jury->count(),
                    'expert' => $expert->isEmpty() ? null : self::toGrade($expert->avg(fn ($e) => (float) $e->total_score)),
                    'expert_count' => $expert->count(),
                ];
            });
    }
}
