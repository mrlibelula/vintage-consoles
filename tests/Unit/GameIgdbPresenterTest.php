<?php

use App\Models\Console;
use App\Models\Game;
use App\Support\GameIgdbPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('merges walkthrough videos before igdb videos and dedupes by id', function () {
    $console = Console::factory()->create(['short_name' => 'nes']);
    $game = Game::factory()->create([
        'console_id' => $console->id,
        'walkthrough_videos' => [
            ['title' => 'Full walkthrough', 'youtube_id' => 'walkthroug1'],
        ],
        'igdb_response' => [
            'videos' => [
                ['name' => 'Trailer', 'video_id' => 'trailer0001'],
                ['name' => 'Dup', 'video_id' => 'walkthroug1'],
            ],
            'artworks' => [
                ['image_id' => 'ar123'],
            ],
            'similar_games' => [
                ['id' => 999, 'name' => 'Other Game', 'slug' => 'other-game'],
            ],
        ],
    ]);

    $presented = app(GameIgdbPresenter::class)->present($game);

    expect($presented['has_videos'])->toBeTrue()
        ->and($presented['videos'])->toHaveCount(2)
        ->and($presented['videos'][0]['source'])->toBe('walkthrough')
        ->and($presented['videos'][0]['youtube_id'])->toBe('walkthroug1')
        ->and($presented['videos'][1]['youtube_id'])->toBe('trailer0001')
        ->and($presented['artworks'])->toHaveCount(1)
        ->and($presented['similar_games'])->toHaveCount(0)
        ->and($presented['has_media'])->toBeTrue();
});

it('keeps only similar games that exist in the local catalog', function () {
    $console = Console::factory()->create(['short_name' => 'nes']);
    $local = Game::factory()->create([
        'console_id' => $console->id,
        'igdb_id' => 4242,
        'title' => 'Local Twin',
        'slug' => 'local-twin',
    ]);
    $local->setRelation('console', $console);

    $game = Game::factory()->create([
        'console_id' => $console->id,
        'igdb_response' => [
            'similar_games' => [
                ['id' => 4242, 'name' => 'Ignored Name', 'slug' => 'ignored'],
                ['id' => 111, 'name' => 'Missing Everywhere', 'slug' => 'missing'],
            ],
        ],
    ]);

    $presented = app(GameIgdbPresenter::class)->present($game, collect([4242 => $local]));

    expect($presented['similar_games'])->toHaveCount(1)
        ->and($presented['similar_games'][0]['title'])->toBe('Local Twin')
        ->and($presented['similar_games'][0]['url'])->toContain('/emulator/nes/local-twin')
        ->and($presented['similar_games'][0]['console'])->toBe('NES');
});

it('returns empty media flags when nothing useful exists', function () {
    $console = Console::factory()->create();
    $game = Game::factory()->create([
        'console_id' => $console->id,
        'igdb_response' => [
            'themes' => [['name' => 'Action']],
            'keywords' => [['name' => 'robots']],
        ],
        'walkthrough_videos' => null,
        'game_preview' => 'https://example.com/preview.gif',
    ]);

    $presented = app(GameIgdbPresenter::class)->present($game);

    expect($presented['has_videos'])->toBeFalse()
        ->and($presented['has_media'])->toBeFalse()
        ->and($presented)->not->toHaveKey('has_info_meta');
});

it('enables the media tab when only artworks exist', function () {
    $console = Console::factory()->create();
    $game = Game::factory()->create([
        'console_id' => $console->id,
        'igdb_response' => [
            'artworks' => [
                ['image_id' => 'ar999'],
            ],
        ],
        'walkthrough_videos' => null,
    ]);

    $presented = app(GameIgdbPresenter::class)->present($game);

    expect($presented['artworks'])->toHaveCount(1)
        ->and($presented['has_videos'])->toBeFalse()
        ->and($presented['has_media'])->toBeTrue();
});
