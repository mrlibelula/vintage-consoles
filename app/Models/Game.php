<?php

namespace App\Models;

use App\Services\Igdb\IgdbImage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'console_id',
        'igdb_id',
        'title',
        'slug',
        'publisher',
        'release_year',
        'description',
        'rating',
        'multiplayer_support',
        'save_state_support',
        'is_free',
        'rom',
        'poster',
        'cover_image_id',
        'game_preview',
        'cartridge',
        'needs_igdb_sync',
        'igdb_response',
        'walkthrough_videos',
    ];

    protected $casts = [
        'igdb_id' => 'integer',
        'rating' => 'decimal:4',
        'multiplayer_support' => 'boolean',
        'save_state_support' => 'boolean',
        'is_free' => 'boolean',
        'needs_igdb_sync' => 'boolean',
        'igdb_response' => 'array',
        'walkthrough_videos' => 'array',
    ];

    public function console(): BelongsTo
    {
        return $this->belongsTo(Console::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class)->orderBy('position');
    }

    /**
     * Generate a cover/poster URL at any IGDB image preset.
     * Requires cover_image_id to be set; falls back to the stored poster URL.
     */
    public function coverUrl(string $preset = IgdbImage::COVER_BIG, string $ext = 'webp'): ?string
    {
        if ($this->cover_image_id) {
            return IgdbImage::url($this->cover_image_id, $preset, $ext);
        }

        return $this->poster;
    }

    /**
     * Minimal game payload for emulator player routes.
     */
    public function toPlayerPayload(): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'slug'               => $this->slug,
            'rom'                => $this->rom,
            'save_state_support' => $this->save_state_support,
        ];
    }
}
