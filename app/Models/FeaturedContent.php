<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedContent extends Model
{
    public const PLACEMENTS = [
        'weekly' => 'Sélection de la semaine',
        'new' => 'Nouveautés',
        'recommended' => 'Recommandés pour vous',
    ];

    protected $fillable = [
        'tmdb_id',
        'media_type',
        'placement',
        'title',
        'poster_url',
        'genres',
        'release_year',
        'position',
        'active',
        'created_by',
    ];

    protected $casts = [
        'tmdb_id' => 'integer',
        'position' => 'integer',
        'active' => 'boolean',
        'genres' => 'array',
    ];

    public function getPlacementLabelAttribute(): string
    {
        return self::PLACEMENTS[$this->placement] ?? $this->placement;
    }
}
