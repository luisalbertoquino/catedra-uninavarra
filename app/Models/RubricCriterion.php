<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RubricCriterion extends Model
{
    protected $table = 'rubric_criteria';

    protected $fillable = ['edition_id', 'name', 'description', 'weight', 'max_score', 'sort'];

    /** Rúbrica del video pitch definida por la Cátedra (20 % cada criterio, escala 1 a 5) */
    public const DEFAULTS = [
        ['Innovación', 'La propuesta se soporta en un producto o servicio novedoso que evidencia un claro diferencial ante las ofertas existentes en el mercado.'],
        ['Viabilidad comercial', 'La propuesta soluciona una problemática real y evidente de un segmento de la población que está dispuesto a adoptarla y pagar por ella.'],
        ['Viabilidad técnica', 'La propuesta es posible de llevar a cabo: el producto o servicio es ejecutable y el equipo cuenta con las competencias necesarias.'],
        ['Proyección', 'El modelo de negocio contempla crecimiento y escalabilidad a mediano y largo plazo; el problema es una necesidad replicable en otros mercados.'],
        ['Sostenibilidad', 'El modelo de negocio genera impactos positivos tanto ambientales como sociales.'],
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }
}
