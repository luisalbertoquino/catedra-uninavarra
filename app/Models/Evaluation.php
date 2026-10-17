<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Evaluation extends Model
{
    protected $fillable = ['edition_id', 'venture_id', 'user_id', 'total_score', 'comment'];

    protected $casts = ['total_score' => 'decimal:2'];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }

    public function jury(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    /**
     * Puntaje 0-100 de una evaluación: promedio ponderado de (puntaje / máximo) según el peso de cada criterio.
     *
     * @param  Collection<int, RubricCriterion>  $criteria
     * @param  array<int, int>  $scores  [criterio_id => puntaje]
     */
    public static function computeTotal(Collection $criteria, array $scores): float
    {
        $totalWeight = $criteria->sum('weight') ?: 1;
        $sum = $criteria->sum(fn ($c) => (($scores[$c->id] ?? 0) / max(1, $c->max_score)) * $c->weight);

        return round($sum / $totalWeight * 100, 2);
    }
}
