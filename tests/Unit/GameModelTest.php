<?php

use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds a compact emulator player payload without igdb metadata', function () {
    $console = Console::factory()->create([
        'short_name' => 'arcade',
    ]);

    $game = Game::factory()->create([
        'console_id' => $console->id,
        'title' => 'Arkanoid',
        'slug' => 'arkanoid',
        'rom' => 'arkatayt.zip',
        'save_state_support' => true,
        'igdb_response' => [
            'name' => 'Arkanoid',
            'summary' => str_repeat('x', 40_000),
        ],
    ]);

    $payload = $game->toPlayerPayload();

    expect($payload)->toBe([
        'id' => $game->id,
        'title' => 'Arkanoid',
        'slug' => 'arkanoid',
        'rom' => 'arkatayt.zip',
        'save_state_support' => true,
    ])->and(strlen(json_encode($payload)))->toBeLessThan(500);
});
