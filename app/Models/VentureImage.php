<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentureImage extends Model
{
    protected $fillable = ['venture_id', 'path', 'caption', 'sort'];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }

    public function getUrlAttribute(): ?string
    {
        return Media::url($this->path);
    }
}
