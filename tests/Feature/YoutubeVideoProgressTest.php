<?php

use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use App\Models\YoutubeVideoProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('upserts youtube progress for the authenticated user', function () {
    $user = User::factory()->create();
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    $this->actingAs($user)
        ->putJson(route('player-data.youtube-progress.upsert', $game), [
            'youtube_id' => 'dQw4w9WgXcQ',
            'position_seconds' => 125,
        ])
        ->assertOk()
        ->assertJsonPath('data.youtube_id', 'dQw4w9WgXcQ')
        ->assertJsonPath('data.position_seconds', 125);

    expect(YoutubeVideoProgress::query()->where('user_id', $user->id)->where('game_id', $game->id)->count())->toBe(1);
});

it('clears progress when position is near the start', function () {
    $user = User::factory()->create();
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    YoutubeVideoProgress::create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'youtube_id' => 'dQw4w9WgXcQ',
        'position_seconds' => 40,
    ]);

    $this->actingAs($user)
        ->putJson(route('player-data.youtube-progress.upsert', $game), [
            'youtube_id' => 'dQw4w9WgXcQ',
            'position_seconds' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('data', null);

    expect(YoutubeVideoProgress::query()->count())->toBe(0);
});

it('lists progress for a game', function () {
    $user = User::factory()->create();
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    YoutubeVideoProgress::create([
        'user_id' => $user->id,
        'game_id' => $game->id,
        'youtube_id' => 'dQw4w9WgXcQ',
        'position_seconds' => 88,
    ]);

    $this->actingAs($user)
        ->getJson(route('player-data.youtube-progress.index', $game))
        ->assertOk()
        ->assertJsonPath('data.dQw4w9WgXcQ.position_seconds', 88);
});

it('requires auth for youtube progress endpoints', function () {
    $console = Console::factory()->create();
    $game = Game::factory()->create(['console_id' => $console->id]);

    $this->putJson(route('player-data.youtube-progress.upsert', $game), [
        'youtube_id' => 'dQw4w9WgXcQ',
        'position_seconds' => 10,
    ])->assertUnauthorized();
});
