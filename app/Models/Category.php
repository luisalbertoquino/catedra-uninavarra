<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'icon'];

    /** Iconos de Font Awesome sugeridos en el admin */
    public const ICONS = [
        'microchip', 'laptop-code', 'robot', 'heart-pulse', 'seedling', 'tractor', 'recycle', 'leaf', 'solar-panel',
        'mug-hot', 'utensils', 'graduation-cap', 'book-open', 'shirt', 'palette', 'mountain-sun', 'plane', 'screwdriver-wrench',
        'briefcase', 'store', 'truck-fast', 'building', 'paw', 'dumbbell', 'music', 'camera', 'coins', 'scale-balanced', 'lightbulb', 'rocket',
    ];

    public function ventures(): HasMany
    {
        return $this->hasMany(Venture::class);
    }
}
