<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YoutubeVideoProgress extends Model
{
    protected $table = 'youtube_video_progress';

    protected $fillable = [
        'user_id',
        'game_id',
        'youtube_id',
        'position_seconds',
    ];

    protected $casts = [
        'position_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
