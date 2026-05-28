<?php

use App\Models\Console;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Screenshot;
use App\Services\GameRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');
});

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function makeConsole(array $overrides = []): Console
{
    static $seq = 1;
    return Console::create(array_merge([
        'id'           => $seq++,
        'long_name'    => 'Nintendo Entertainment System',
        'short_name'   => 'nes',
        'description'  => 'Classic 8-bit console',
        'emulator_name'=> 'EmulatorJS',
        'console_bgs'  => [],
        'specs'        => [],
        'community_links' => [],
        'options'      => [],
    ], $overrides));
}

function makeGame(Console $console, array $overrides = []): Game
{
    static $seq = 1;
    $title = $overrides['title'] ?? "Test Game {$seq}";
    $slug  = \Illuminate\Support\Str::slug($title);
    $seq++;
    return Game::create(array_merge([
        'console_id'         => $console->id,
        'title'              => $title,
        'slug'               => $slug,
        'publisher'          => 'Nintendo',
        'release_year'       => '1985',
        'description'        => 'A classic game',
        'rating'             => 0.89,
        'multiplayer_support'=> false,
        'save_state_support' => true,
        'is_free'            => true,
        'needs_igdb_sync'    => false,
    ], $overrides));
}

// ─────────────────────────────────────────────────────────────────────────────
// getConsoles
// ─────────────────────────────────────────────────────────────────────────────

describe('getConsoles', function () {
    it('returns all consoles ordered by id', function () {
        makeConsole(['id' => 10, 'short_name' => 'snes']);
        makeConsole(['id' => 5,  'short_name' => 'nes']);

        $repo     = app(GameRepository::class);
        $consoles = $repo->getConsoles();

        expect($consoles)->toHaveCount(2)
            ->and($consoles->first()->id)->toBe(5);
    });

    it('returns empty collection when no consoles exist', function () {
        $repo = app(GameRepository::class);
        expect($repo->getConsoles())->toHaveCount(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// getConsole
// ─────────────────────────────────────────────────────────────────────────────

describe('getConsole', function () {
    it('returns a console with its games eager-loaded', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        makeGame($console, ['title' => 'Super Mario Bros']);

        $repo   = app(GameRepository::class);
        $result = $repo->getConsole('nes');

        expect($result)->not->toBeNull()
            ->and($result->games)->toHaveCount(1)
            ->and($result->games->first()->title)->toBe('Super Mario Bros');
    });

    it('returns null for unknown short name', function () {
        $repo = app(GameRepository::class);
        expect($repo->getConsole('unknown'))->toBeNull();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// getGamesByConsole
// ─────────────────────────────────────────────────────────────────────────────

describe('getGamesByConsole', function () {
    it('returns games ordered by title', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        makeGame($console, ['title' => 'Zelda']);
        makeGame($console, ['title' => 'Contra',  'slug' => 'contra']);

        $repo  = app(GameRepository::class);
        $games = $repo->getGamesByConsole('nes');

        expect($games->first()->title)->toBe('Contra');
    });

    it('returns an empty collection for unknown console', function () {
        $repo = app(GameRepository::class);
        expect($repo->getGamesByConsole('unknown'))->toHaveCount(0);
    });

    it('does not return games from other consoles', function () {
        $nes  = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $snes = makeConsole(['id' => 2, 'short_name' => 'snes']);
        makeGame($nes,  ['title' => 'Mario NES',  'slug' => 'mario-nes']);
        makeGame($snes, ['title' => 'Mario SNES', 'slug' => 'mario-snes']);

        $repo  = app(GameRepository::class);
        $games = $repo->getGamesByConsole('nes');

        expect($games)->toHaveCount(1)
            ->and($games->first()->title)->toBe('Mario NES');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// getGameBySlug
// ─────────────────────────────────────────────────────────────────────────────

describe('getGameBySlug', function () {
    it('returns the correct game with screenshots eager-loaded', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $game    = makeGame($console, ['title' => 'Super Mario Bros', 'slug' => 'super-mario-bros']);
        Screenshot::create([
            'game_id'       => $game->id,
            'igdb_image_id' => 'ss1',
            'thumb_url'     => 'https://example.com/thumb.webp',
            'full_url'      => 'https://example.com/full.webp',
            'position'      => 0,
        ]);

        $repo   = app(GameRepository::class);
        $result = $repo->getGameBySlug('nes', 'super-mario-bros');

        expect($result)->not->toBeNull()
            ->and($result->title)->toBe('Super Mario Bros')
            ->and($result->screenshots)->toHaveCount(1);
    });

    it('returns null for missing slug', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $repo    = app(GameRepository::class);
        expect($repo->getGameBySlug('nes', 'nonexistent'))->toBeNull();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// addGame / updateGame / deleteGame
// ─────────────────────────────────────────────────────────────────────────────

describe('CRUD operations', function () {
    it('can add a game with genres', function () {
        makeConsole(['id' => 1, 'short_name' => 'nes']);

        $repo    = app(GameRepository::class);
        $newGame = $repo->addGame('nes', [
            'title'       => 'Test Game',
            'publisher'   => 'Nintendo',
            'release_year'=> '1990',
            'description' => 'A test game',
            'rating'      => 0.75,
            'genres'      => [['name' => 'platformer', 'description' => '']],
        ]);

        expect($newGame)->not->toBeFalse()
            ->and(Game::where('title', 'Test Game')->exists())->toBeTrue()
            ->and($newGame->genres)->toHaveCount(1)
            ->and($newGame->genres->first()->name)->toBe('platformer');
    });

    it('can update a game title and regenerates slug', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $game    = makeGame($console, ['title' => 'Old Title', 'slug' => 'old-title']);

        $repo    = app(GameRepository::class);
        $success = $repo->updateGame('nes', $game->id, ['title' => 'New Title']);

        expect($success)->toBeTrue()
            ->and($game->fresh()->title)->toBe('New Title')
            ->and($game->fresh()->slug)->toBe('new-title');
    });

    it('can delete a game', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $game    = makeGame($console);

        $repo    = app(GameRepository::class);
        $success = $repo->deleteGame('nes', $game->id);

        expect($success)->toBeTrue()
            ->and(Game::find($game->id))->toBeNull();
    });

    it('returns false when deleting game from wrong console', function () {
        $nes  = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $snes = makeConsole(['id' => 2, 'short_name' => 'snes']);
        $game = makeGame($nes);

        $repo = app(GameRepository::class);
        expect($repo->deleteGame('snes', $game->id))->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// getAllGenres / getAllPublishers
// ─────────────────────────────────────────────────────────────────────────────

describe('getAllGenres', function () {
    it('returns genres with game count ordered by count desc', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $g1      = Genre::create(['name' => 'platformer']);
        $g2      = Genre::create(['name' => 'action']);
        $game1   = makeGame($console, ['title' => 'Mario', 'slug' => 'mario']);
        $game2   = makeGame($console, ['title' => 'Mega Man', 'slug' => 'mega-man']);
        $game1->genres()->attach([$g1->id, $g2->id]);
        $game2->genres()->attach([$g1->id]);

        $repo   = app(GameRepository::class);
        $genres = $repo->getAllGenres();

        expect($genres->first()->name)->toBe('platformer')
            ->and($genres->first()->games_count)->toBe(2);
    });
});

describe('getAllPublishers', function () {
    it('returns publishers with game counts', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        makeGame($console, ['title' => 'Game A', 'slug' => 'game-a', 'publisher' => 'Nintendo']);
        makeGame($console, ['title' => 'Game B', 'slug' => 'game-b', 'publisher' => 'Nintendo']);
        makeGame($console, ['title' => 'Game C', 'slug' => 'game-c', 'publisher' => 'Capcom']);

        $repo       = app(GameRepository::class);
        $publishers = $repo->getAllPublishers();

        $nintendoEntry = collect($publishers)->firstWhere('name', 'Nintendo');
        $capcomEntry   = collect($publishers)->firstWhere('name', 'Capcom');

        expect($nintendoEntry['games_count'])->toBe(2)
            ->and($capcomEntry['games_count'])->toBe(1);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// searchGames
// ─────────────────────────────────────────────────────────────────────────────

describe('searchGames', function () {
    it('returns matching games by title (case-insensitive partial match)', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        makeGame($console, ['title' => 'Super Mario Bros', 'slug' => 'super-mario-bros']);
        makeGame($console, ['title' => 'Mega Man', 'slug' => 'mega-man']);

        $repo    = app(GameRepository::class);
        $results = $repo->searchGames('mario');

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Super Mario Bros');
    });

    it('respects the limit parameter', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        for ($i = 1; $i <= 5; $i++) {
            makeGame($console, ['title' => "Mario $i", 'slug' => "mario-$i"]);
        }

        $repo    = app(GameRepository::class);
        $results = $repo->searchGames('mario', 3);

        expect($results)->toHaveCount(3);
    });

    it('returns empty collection for no matches', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        makeGame($console, ['title' => 'Zelda', 'slug' => 'zelda']);

        $repo = app(GameRepository::class);
        expect($repo->searchGames('mario'))->toHaveCount(0);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// sortGamesCollection
// ─────────────────────────────────────────────────────────────────────────────

describe('sortGamesCollection', function () {
    it('sorts games by title ascending', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $zebra = makeGame($console, ['title' => 'Zebra', 'slug' => 'zebra', 'rating' => 0.5]);
        $alpha = makeGame($console, ['title' => 'Alpha', 'slug' => 'alpha', 'rating' => 0.9]);

        $repo = app(GameRepository::class);
        $sorted = $repo->sortGamesCollection(collect([$zebra, $alpha]), 'title', 'asc');

        expect($sorted->pluck('title')->all())->toBe(['Alpha', 'Zebra']);
    });

    it('sorts games by rating descending', function () {
        $console = makeConsole(['id' => 1, 'short_name' => 'nes']);
        $low = makeGame($console, ['title' => 'Low', 'slug' => 'low', 'rating' => 0.2]);
        $high = makeGame($console, ['title' => 'High', 'slug' => 'high', 'rating' => 0.9]);

        $repo = app(GameRepository::class);
        $sorted = $repo->sortGamesCollection(collect([$low, $high]), 'rating', 'desc');

        expect($sorted->pluck('title')->all())->toBe(['High', 'Low']);
    });
});
