<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['user_id', 'venture_id', 'body', 'rating', 'is_approved'];

    protected $casts = ['is_approved' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venture(): BelongsTo
    {
        return $this->belongsTo(Venture::class);
    }
}
