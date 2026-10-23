<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\Evaluation;
use Illuminate\Support\Collection;

/**
 * Notas del comité a partir de las evaluaciones con la rúbrica (cada evaluación se guarda en 0-100).
 * Componentes, todos en escala 1 a 5:
 *  - jurados: promedio de los jurados normales
 *  - experto: promedio del Profesor experto (o de los expertos, si hay varios)
 *  - comité: promedio de todas las evaluaciones (experto incluido, pesando igual que un jurado)
 */
class JuryScoring
{
    /** Convierte un puntaje de rúbrica 0-100 a nota 0-5 (rúbrica en escala 0..5 → nota 0..5) */
    public static function toGrade(float $score100): float
    {
        return round($score100 / 20, 2);
    }

    /** @return Collection<int, array{jury: ?float, jury_count: int, expert: ?float, expert_count: int, committee: float}> indexado por venture_id */
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

                // Sin redondear: la nota final se redondea al final, como en el consolidado en Excel
                $grade = fn ($set) => $set->isEmpty() ? null : $set->avg(fn ($e) => (float) $e->total_score) / 20;

                return [
                    'jury' => $grade($jury),
                    'jury_count' => $jury->count(),
                    'expert' => $grade($expert),
                    'expert_count' => $expert->count(),
                    'committee' => $grade($evals),
                ];
            });
    }
}
