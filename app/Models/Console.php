<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Console extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'long_name',
        'short_name',
        'description',
        'emulator_name',
        'emulator_version',
        'manufacturer',
        'release_year',
        'console_logo',
        'console_icon',
        'igdb_platform_id',
        'console_bgs',
        'specs',
        'community_links',
        'options',
    ];

    protected $casts = [
        'id' => 'integer',
        'igdb_platform_id' => 'integer',
        'console_bgs' => 'array',
        'specs' => 'array',
        'community_links' => 'array',
        'options' => 'array',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
