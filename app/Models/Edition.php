<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    protected $fillable = [
        'name', 'slug', 'year', 'period', 'event_name', 'tagline', 'description', 'banner', 'promo_video_url',
        'voting_starts_at', 'voting_ends_at', 'final_at', 'final_location',
        'community_weight', 'max_votes_per_user', 'finalists_count', 'voting_email_domain',
        'is_active', 'is_published', 'results_published',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'final_at' => 'datetime',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'results_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function ventures(): HasMany
    {
        return $this->hasMany(Venture::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('sort')->orderBy('starts_at');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** Edición vigente: la marcada como activa o, en su defecto, la más reciente publicada. */
    public static function current(): ?self
    {
        return static::published()->where('is_active', true)->first()
            ?? static::published()->orderByDesc('year')->orderByDesc('period')->first();
    }

    public function isVotingOpen(): bool
    {
        $now = now();

        return $this->voting_starts_at && $this->voting_ends_at
            && $now->between($this->voting_starts_at, $this->voting_ends_at);
    }

    public function votingStatus(): string
    {
        if (! $this->voting_starts_at || ! $this->voting_ends_at) {
            return 'sin_fecha';
        }
        if (now()->lt($this->voting_starts_at)) {
            return 'proxima';
        }

        return now()->gt($this->voting_ends_at) ? 'cerrada' : 'abierta';
    }

    public function getBannerUrlAttribute(): ?string
    {
        return Media::url($this->banner);
    }

    /** ID de YouTube del video promocional, para reproducirlo de fondo. */
    public function getPromoVideoIdAttribute(): ?string
    {
        return Media::youtubeId($this->promo_video_url);
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->period ? "{$this->year}-{$this->period}" : (string) $this->year;
    }
}
