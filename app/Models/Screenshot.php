<?php

namespace App\Models;

use App\Services\Igdb\IgdbImage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'igdb_image_id',
        'thumb_url',
        'full_url',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    protected function thumbUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($this->igdb_image_id) {
                    return IgdbImage::screenshotThumb($this->igdb_image_id);
                }

                return $value ?? '';
            },
        );
    }

    protected function fullUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($this->igdb_image_id) {
                    return IgdbImage::fullScreenshot($this->igdb_image_id);
                }

                $full = trim((string) ($value ?? ''));
                if ($full !== '') {
                    return $full;
                }

                return trim((string) ($this->attributes['thumb_url'] ?? ''));
            },
        );
    }
}
