<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Milestone extends Model
{
    protected $fillable = ['edition_id', 'title', 'description', 'starts_at', 'ends_at', 'sort'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function status(): string
    {
        $today = today();
        $end = $this->ends_at ?? $this->starts_at;
        if (! $this->starts_at) {
            return 'pendiente';
        }
        if ($end && $today->gt($end)) {
            return 'completada';
        }

        return $today->gte($this->starts_at) ? 'en_curso' : 'pendiente';
    }

    public function getDateLabelAttribute(): string
    {
        if (! $this->starts_at) {
            return 'Por definir';
        }
        if (! $this->ends_at || $this->ends_at->eq($this->starts_at)) {
            return $this->starts_at->translatedFormat('j \d\e F');
        }
        if ($this->starts_at->month === $this->ends_at->month) {
            return $this->starts_at->format('j').' – '.$this->ends_at->translatedFormat('j \d\e F');
        }

        return $this->starts_at->translatedFormat('j M').' – '.$this->ends_at->translatedFormat('j M');
    }
}
