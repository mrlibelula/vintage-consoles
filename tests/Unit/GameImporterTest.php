<?php

use App\Models\Console;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Screenshot;
use App\Services\Igdb\GameImporter;
use App\Services\Igdb\IgdbImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');
});

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function nesConsole(): Console
{
    return Console::firstOrCreate(
        ['id' => 1],
        [
            'short_name'     => 'nes',
            'long_name'      => 'Nintendo Entertainment System',
            'description'    => '',
            'emulator_name'  => 'EmulatorJS',
            'igdb_platform_id' => 18,
            'console_bgs'    => [],
            'specs'          => [],
            'community_links'=> [],
            'options'        => [],
        ]
    );
}

function igdbPayload(array $overrides = []): array
{
    return array_merge([
        'id'                 => 1234,
        'name'               => 'Super Mario Bros.',
        'summary'            => 'Run and jump.',
        'total_rating'       => 89.5,
        'first_release_date' => mktime(0, 0, 0, 10, 18, 1985),
        'cover'              => ['image_id' => 'co8lo8', 'url' => '//images.igdb.com/t_thumb/co8lo8.jpg'],
        'genres'             => [
            ['id' => 8, 'name' => 'Platform'],
            ['id' => 31,'name' => 'Adventure'],
        ],
        'screenshots'        => [
            ['image_id' => 'ss111'],
            ['image_id' => 'ss222'],
        ],
        'involved_companies' => [
            ['publisher' => true, 'developer' => false, 'company' => ['name' => 'Nintendo']],
        ],
    ], $overrides);
}

// ─────────────────────────────────────────────────────────────────────────────
// import() from IGDB payload
// ─────────────────────────────────────────────────────────────────────────────

describe('GameImporter::import', function () {
    it('creates a Game row with correct fields', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(), $console, [
            'slug' => 'super-mario-bros',
            'rom'  => 'mario.nes',
        ]);

        expect($game)->toBeInstanceOf(Game::class)
            ->and($game->title)->toBe('Super Mario Bros.')
            ->and($game->slug)->toBe('super-mario-bros')
            ->and($game->publisher)->toBe('Nintendo')
            ->and($game->release_year)->toBe('1985')
            ->and($game->description)->toBe('Run and jump.')
            ->and($game->rom)->toBe('mario.nes')
            ->and($game->needs_igdb_sync)->toBeFalse()
            ->and($game->igdb_id)->toBe(1234);
    });

    it('normalises total_rating to 0–1 range', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(['total_rating' => 89.5]), $console);

        expect((float) $game->rating)->toBeBetween(0.0, 1.0);
        expect(round((float) $game->rating, 4))->toBe(round(89.5 / 100, 4));
    });

    it('clamps rating to 1.0 if IGDB returns > 100', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(['total_rating' => 105.0]), $console);

        expect((float) $game->rating)->toBe(1.0);
    });

    it('generates correct IGDB poster URL', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(), $console);

        expect($game->poster)
            ->toBe(IgdbImage::url('co8lo8', IgdbImage::COVER_BIG, 'webp'))
            ->and($game->cover_image_id)->toBe('co8lo8');
    });

    it('attaches genres from IGDB payload', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(), $console);
        $game->load('genres');

        expect($game->genres)->toHaveCount(2)
            ->and($game->genres->pluck('name')->toArray())
            ->toContain('platform')
            ->toContain('adventure');
    });

    it('inserts screenshots from IGDB payload', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(), $console);
        $game->load('screenshots');

        expect($game->screenshots)->toHaveCount(2)
            ->and($game->screenshots->first()->igdb_image_id)->toBe('ss111')
            ->and($game->screenshots->first()->thumb_url)
                ->toBe(IgdbImage::screenshotThumb('ss111'))
            ->and($game->screenshots->first()->full_url)
                ->toBe(IgdbImage::fullScreenshot('ss111'));
    });

    it('stores the full raw IGDB response', function () {
        $console  = nesConsole();
        $importer = new GameImporter();
        $payload  = igdbPayload();

        $game = $importer->import($payload, $console);

        expect($game->igdb_response)->toBeArray()
            ->and($game->igdb_response['id'])->toBe(1234);
    });

    it('merges local-only fields (game_preview, cartridge)', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->import(igdbPayload(), $console, [
            'game_preview' => 'https://example.com/mario.gif',
            'cartridge'    => 'https://example.com/cart.png',
        ]);

        expect($game->game_preview)->toBe('https://example.com/mario.gif')
            ->and($game->cartridge)->toBe('https://example.com/cart.png');
    });

    it('is idempotent — re-importing the same slug updates rather than duplicates', function () {
        $console  = nesConsole();
        $importer = new GameImporter();
        $local    = ['slug' => 'super-mario-bros'];

        $importer->import(igdbPayload(), $console, $local);
        $importer->import(igdbPayload(['summary' => 'Updated summary.']), $console, $local);

        expect(Game::where('slug', 'super-mario-bros')->count())->toBe(1)
            ->and(Game::where('slug', 'super-mario-bros')->first()->description)
                ->toBe('Updated summary.');
    });

    it('imports multiple games inside an outer database transaction', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        DB::transaction(function () use ($console, $importer): void {
            $importer->import(igdbPayload(), $console, ['slug' => 'super-mario-bros']);
            $importer->import(igdbPayload(['id' => 2, 'name' => 'Mega Man 2']), $console, ['slug' => 'mega-man-2']);
        });

        expect(Game::count())->toBe(2);
    });

    it('persists long local media URLs from JSON', function () {
        $console  = nesConsole();
        $importer = new GameImporter();
        $longCartridge = 'https://commondatastorage.googleapis.com/images.pricecharting.com/'.str_repeat('a', 300).'/240.jpg';

        $game = $importer->import(igdbPayload(), $console, [
            'slug'      => 'super-mario-bros',
            'cartridge' => $longCartridge,
        ]);

        expect($game->cartridge)->toBe($longCartridge);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// importFromJson() fallback
// ─────────────────────────────────────────────────────────────────────────────

describe('GameImporter::importFromJson', function () {
    it('creates a game marked needs_igdb_sync=true with no poster', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->importFromJson([
            'title'       => 'Kirby\'s Adventure',
            'slug'        => 'kirbys-adventure',
            'publisher'   => 'Nintendo',
            'release_year'=> '1993',
            'description' => 'A classic Kirby game.',
            'rating'      => '0.91',
            'rom'         => 'kirby.nes',
            'box'         => 'https://example.com/kirby.gif',
            'genres'      => [['name' => 'platformer', 'description' => '']],
        ], $console);

        expect($game->needs_igdb_sync)->toBeTrue()
            ->and($game->poster)->toBeNull()
            ->and($game->igdb_response)->toBeNull()
            ->and($game->title)->toBe("Kirby's Adventure")
            ->and($game->game_preview)->toBe('https://example.com/kirby.gif');
    });

    it('attaches genres from JSON data', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->importFromJson([
            'title'  => 'Contra',
            'slug'   => 'contra',
            'genres' => [
                ['name' => 'action',  'description' => ''],
                ['name' => 'shooter', 'description' => ''],
            ],
        ], $console);

        $game->load('genres');
        expect($game->genres)->toHaveCount(2);
    });

    it('clamps JSON rating to 0–1', function () {
        $console  = nesConsole();
        $importer = new GameImporter();

        $game = $importer->importFromJson([
            'title'  => 'Mega Man',
            'slug'   => 'mega-man',
            'rating' => '1.5',
        ], $console);

        expect((float) $game->rating)->toBe(1.0);
    });
});
