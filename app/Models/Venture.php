<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venture extends Model
{
    protected $fillable = [
        'edition_id', 'category_id', 'name', 'slug', 'tagline', 'description', 'problem', 'solution',
        'target_market', 'mission', 'vision', 'logo', 'cover', 'video_url', 'academic_program', 'team_group', 'contact_name',
        'contact_email', 'contact_phone', 'website', 'instagram', 'facebook', 'tiktok',
        'funding_goal', 'startup_amount', 'committee_score', 'external_votes', 'is_finalist', 'final_position', 'is_published',
    ];

    protected $casts = [
        'funding_goal' => 'decimal:2',
        'startup_amount' => 'decimal:2',
        'committee_score' => 'decimal:2',
        'external_votes' => 'integer',
        'is_finalist' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(TeamMember::class)->orderBy('sort');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VentureImage::class)->orderBy('sort');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('is_approved', true)->latest();
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(SupportPledge::class);
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    /** Ordena por votos de la comunidad: los de la página + los registrados a mano (YouTube, Facebook…) */
    public function scopeOrderByVotes(Builder $q, string $dir = 'desc'): Builder
    {
        return $q->orderByRaw('((select count(*) from votes where votes.venture_id = ventures.id) + ventures.external_votes) '.($dir === 'asc' ? 'asc' : 'desc'));
    }

    /** Con withCount('votes'): votos en la página + los registrados a mano */
    public function getVotesCountAttribute($value): ?int
    {
        return $value === null ? null : (int) $value + (int) ($this->attributes['external_votes'] ?? 0);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return Media::url($this->logo);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return Media::url($this->cover);
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        return Media::videoEmbed($this->video_url);
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', $this->name))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }

    /** Número de WhatsApp en formato internacional para wa.me */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->contact_phone) {
            return null;
        }
        $digits = preg_replace('/\D/', '', $this->contact_phone);
        if (strlen($digits) === 10) {
            $digits = '57'.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode("Hola, vi {$this->name} en Cátedra UNINAVARRA y me gustaría conocer más.");
    }
}
