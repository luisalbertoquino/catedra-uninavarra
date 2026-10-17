<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricCriterion extends Model
{
    protected $table = 'rubric_criteria';

    protected $fillable = ['edition_id', 'name', 'description', 'weight', 'max_score', 'sort'];

    /** Criterios de evaluación definidos por la Cátedra para el comité técnico */
    public const DEFAULTS = [
        ['Innovación', 'Qué tan novedosa es la propuesta frente a lo que existe en el mercado.'],
        ['Viabilidad comercial', 'Existe un mercado dispuesto a pagar y un modelo de negocio claro.'],
        ['Viabilidad técnica', 'El equipo puede construir y operar la solución con recursos razonables.'],
        ['Proyección', 'Potencial de crecimiento y escalabilidad en la región y fuera de ella.'],
        ['Sostenibilidad', 'Impacto social y ambiental positivo y continuidad en el tiempo.'],
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
