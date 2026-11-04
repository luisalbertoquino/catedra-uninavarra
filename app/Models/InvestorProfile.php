<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Inversionista invitado al evento, con foto y trayectoria, que se muestra en el sitio. */
class InvestorProfile extends Model
{
    protected $fillable = ['name', 'headline', 'bio', 'expertise', 'photo', 'linkedin', 'sort', 'is_published'];

    protected $casts = ['is_published' => 'boolean'];

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort')->orderBy('name');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return Media::url($this->photo);
    }

    /** @return list<string> */
    public function getExpertiseListAttribute(): array
    {
        return collect(explode(',', (string) $this->expertise))->map(fn ($t) => trim($t))->filter()->values()->all();
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', $this->name))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}
