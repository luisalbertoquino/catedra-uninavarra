<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\Evaluation;
use Illuminate\Support\Collection;

/**
 * Puntaje del comité técnico a partir de las evaluaciones de los jurados.
 * Cada evaluación vale 0-100 según la rúbrica; el promedio es ponderado: un jurado normal pesa 1
 * y el jurado experto pesa lo que diga la edición (expert_weight, 2 por defecto).
 */
class JuryScoring
{
    /** @return Collection<int, array{score: float, count: int, experts: int}> indexado por venture_id */
    public static function forEdition(Edition $edition): Collection
    {
        $expertWeight = (float) $edition->expert_weight;

        return Evaluation::query()
            ->where('evaluations.edition_id', $edition->id)
            ->join('users', 'users.id', '=', 'evaluations.user_id')
            ->get(['evaluations.venture_id', 'evaluations.total_score', 'users.is_expert'])
            ->groupBy('venture_id')
            ->map(function ($evals) use ($expertWeight) {
                $weight = fn ($e) => $e->is_expert ? $expertWeight : 1.0;
                $weighted = $evals->sum(fn ($e) => (float) $e->total_score * $weight($e));
                $totalWeight = $evals->sum($weight);

                return [
                    'score' => round($weighted / max(0.01, $totalWeight), 2),
                    'count' => $evals->count(),
                    'experts' => $evals->where('is_expert', true)->count(),
                ];
            });
    }
}
