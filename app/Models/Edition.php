<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    /** Likes/votos mínimos para cada nota (consolidado de calificación video pitch 2026-1) */
    public const DEFAULT_VOTE_RANGES = [
        ['min' => 0, 'grade' => 3],
        ['min' => 15, 'grade' => 3.5],
        ['min' => 50, 'grade' => 4],
        ['min' => 120, 'grade' => 4.5],
        ['min' => 198, 'grade' => 5],
    ];

    protected $fillable = [
        'name', 'slug', 'year', 'period', 'event_name', 'tagline', 'description', 'banner', 'promo_video_url',
        'voting_starts_at', 'voting_ends_at', 'final_at', 'final_location',
        'community_weight', 'jury_weight', 'expert_score_weight', 'committee_mode', 'vote_grade_mode', 'vote_grade_thresholds',
        'max_votes_per_user', 'web_voting', 'finalists_count', 'voting_email_domain', 'expert_title',
        'is_active', 'is_published', 'results_published',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'final_at' => 'datetime',
        'web_voting' => 'boolean',
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
        if ($this->expertCountsAsJury()) {
            return ['community' => (int) $this->community_weight, 'jury' => (int) $this->jury_weight + (int) $this->expert_score_weight, 'expert' => 0];
        }

        return ['community' => (int) $this->community_weight, 'jury' => (int) $this->jury_weight, 'expert' => (int) $this->expert_score_weight];
    }

    /** En modo "promedio" el comité técnico es el promedio de jurados y experto: el experto pesa igual que un jurado. */
    public function expertCountsAsJury(): bool
    {
        return ($this->committee_mode ?? 'promedio') === 'promedio';
    }

    public function committeeLabel(): string
    {
        return $this->expertCountsAsJury() ? 'Comité técnico' : 'Jurados';
    }

    /** Cuánto pesa la evaluación del experto, para mostrarlo a jurados y administradores */
    public function expertWeightText(): string
    {
        return $this->expertCountsAsJury()
            ? 'pesa lo mismo que la de cada jurado dentro del comité técnico ('.$this->gradeWeights()['jury'].'% de la nota final)'
            : 'vale el '.$this->expert_score_weight.'% de la nota final';
    }

    /**
     * Rangos del modo "por rangos": [['min' => votos mínimos, 'grade' => nota], ...] ordenados por votos.
     * Acepta también el formato anterior {"2": 5, "3": 15, ...} (votos mínimos para cada nota entera).
     */
    public function voteRanges(): array
    {
        $t = $this->vote_grade_thresholds ?: self::DEFAULT_VOTE_RANGES;
        if (! array_is_list($t)) {
            $t = collect($t)->map(fn ($min, $grade) => ['min' => (int) $min, 'grade' => (float) $grade])->values()->prepend(['min' => 0, 'grade' => 1])->all();
        }

        return collect($t)
            ->map(fn ($r) => ['min' => (int) $r['min'], 'grade' => (float) $r['grade']])
            ->sortBy('min')->values()->all();
    }

    /**
     * Nota de 1 a 5 según los votos de la comunidad.
     *  - relativo: el emprendimiento más votado obtiene 5 y los demás en proporción (0 votos = 1).
     *  - rangos: la nota del rango más alto alcanzado (por debajo del primer rango = 1).
     */
    public function voteGrade(int $votes, int $maxVotes): float
    {
        if ($this->vote_grade_mode === 'rangos') {
            $grade = 1.0;
            foreach ($this->voteRanges() as $r) {
                if ($votes >= $r['min']) {
                    $grade = $r['grade'];
                }
            }

            return $grade;
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

    /** false = la comunidad vota fuera de la página (YouTube, Facebook…) y un admin registra los votos a mano. */
    public function hasWebVoting(): bool
    {
        return $this->web_voting ?? true;
    }

    public function isVotingOpen(): bool
    {
        $now = now();

        return $this->hasWebVoting() && $this->voting_starts_at && $this->voting_ends_at
            && $now->between($this->voting_starts_at, $this->voting_ends_at);
    }

    public function votingStatus(): string
    {
        if (! $this->hasWebVoting()) {
            return 'redes';
        }
        if (! $this->voting_starts_at || ! $this->voting_ends_at) {
            return 'sin_fecha';
        }
        if (now()->lt($this->voting_starts_at)) {
            return 'proxima';
        }

        return now()->gt($this->voting_ends_at) ? 'cerrada' : 'abierta';
    }

    /** Con withCount('votes'): votos en la página + los registrados a mano en los emprendimientos */
    public function getVotesCountAttribute($value): ?int
    {
        return $value === null ? null : (int) $value + (int) $this->ventures()->sum('external_votes');
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
