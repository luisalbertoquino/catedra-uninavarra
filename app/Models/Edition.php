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
        'community_weight', 'jury_weight', 'expert_score_weight', 'vote_grade_mode', 'vote_grade_thresholds',
        'max_votes_per_user', 'finalists_count', 'voting_email_domain', 'expert_title',
        'is_active', 'is_published', 'results_published',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'final_at' => 'datetime',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'results_published' => 'boolean',
        'vote_grade_thresholds' => 'array',
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

    public function criteria(): HasMany
    {
        return $this->hasMany(RubricCriterion::class)->orderBy('sort');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /** Pesos de la nota final (comunidad, jurados, experto) en porcentaje */
    public function gradeWeights(): array
    {
        return ['community' => (int) $this->community_weight, 'jury' => (int) $this->jury_weight, 'expert' => (int) $this->expert_score_weight];
    }

    /** Votos mínimos para obtener nota 2, 3, 4 y 5 en el modo "por rangos" */
    public function voteThresholds(): array
    {
        $t = $this->vote_grade_thresholds ?: [];

        return ['2' => (int) ($t['2'] ?? 5), '3' => (int) ($t['3'] ?? 15), '4' => (int) ($t['4'] ?? 30), '5' => (int) ($t['5'] ?? 50)];
    }

    /**
     * Nota de 1 a 5 según los votos de la comunidad.
     *  - relativo: el emprendimiento más votado obtiene 5 y los demás en proporción (0 votos = 1).
     *  - rangos: nota según los votos mínimos configurados para cada nota.
     */
    public function voteGrade(int $votes, int $maxVotes): float
    {
        if ($this->vote_grade_mode === 'rangos') {
            $grade = 1;
            foreach ($this->voteThresholds() as $g => $min) {
                if ($votes >= $min) {
                    $grade = (int) $g;
                }
            }

            return (float) $grade;
        }

        return $maxVotes > 0 ? round(1 + 4 * $votes / $maxVotes, 2) : 1.0;
    }

    /** Los jurados pueden evaluar mientras la edición esté activa y no se hayan publicado resultados. */
    public function isJuryOpen(): bool
    {
        return $this->is_active && ! $this->results_published;
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
