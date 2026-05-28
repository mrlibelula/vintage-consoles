<?php

use App\Models\Console;
use App\Models\Game;
use App\Models\Screenshot;
use App\Services\Igdb\IgdbImage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('uses IGDB original URLs for full-size screenshots', function () {
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    $screenshot = Screenshot::factory()->create([
        'game_id' => $game->id,
        'igdb_image_id' => 'ss111',
        'thumb_url' => 'https://example.com/thumb.jpg',
        'full_url' => 'https://example.com/full.jpg',
    ]);

    expect($screenshot->full_url)
        ->toBe(IgdbImage::fullScreenshot('ss111'))
        ->and($screenshot->thumb_url)
        ->toBe(IgdbImage::screenshotThumb('ss111'));
});

it('falls back to the thumbnail URL when a manual screenshot has no full-size URL', function () {
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    $screenshot = Screenshot::create([
        'game_id' => $game->id,
        'thumb_url' => 'https://example.com/thumb-only.jpg',
        'full_url' => '',
        'position' => 0,
    ]);

    expect($screenshot->full_url)->toBe('https://example.com/thumb-only.jpg');
});
