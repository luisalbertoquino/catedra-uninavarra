<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends Model
{
    protected $fillable = ['venture_id', 'name', 'role', 'academic_program', 'photo', 'linkedin', 'sort'];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return Media::url($this->photo);
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }
}
