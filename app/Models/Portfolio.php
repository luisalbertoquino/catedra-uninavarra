<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Portafolio de una convocatoria: enlace a la página web y/o PDF con el resumen de los emprendimientos. */
class Portfolio extends Model
{
    protected $fillable = ['title', 'period', 'description', 'url', 'pdf', 'is_featured', 'sort', 'is_published'];

    protected $casts = ['is_featured' => 'boolean', 'is_published' => 'boolean'];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderByDesc('is_featured')->orderBy('sort')->orderByDesc('period');
    }

    /** El portafolio del botón flotante (el de la convocatoria actual). */
    public static function featured(): ?self
    {
        return static::published()->where('is_featured', true)->ordered()->first();
    }

    public function getPdfUrlAttribute(): ?string
    {
        return Media::url($this->pdf);
    }

    /** Lo que abre el botón: el PDF si hay, si no la página web. */
    public function getOpenUrlAttribute(): ?string
    {
        return $this->pdf_url ?? $this->url;
    }
}
