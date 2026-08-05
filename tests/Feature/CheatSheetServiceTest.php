<?php

use App\Models\Console;
use App\Models\Game;
use App\Services\CheatSheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');
});

function cheatServiceGame(array $attrs = []): Game
{
    $console = Console::factory()->create(array_merge(['short_name' => 'nes'], $attrs['console'] ?? []));
    unset($attrs['console']);

    $game = Game::factory()->create(array_merge([
        'console_id' => $console->id,
        'title' => 'Contra',
        'slug' => 'contra',
    ], $attrs));

    $game->setRelation('console', $console);

    return $game;
}

it('builds a nested path from console short_name and game slug', function () {
    $game = cheatServiceGame();

    expect(app(CheatSheetService::class)->path($game))->toBe('cheats/nes/contra/cheats.md');
});

it('reports missing before anything is written', function () {
    $game = cheatServiceGame();
    $service = app(CheatSheetService::class);

    expect($service->exists($game))->toBeFalse()
        ->and($service->get($game))->toBeNull();
});

it('writes and reads back markdown via put/get', function () {
    $game = cheatServiceGame();
    $service = app(CheatSheetService::class);

    $service->put($game, "# Contra\n\n## Cheat Codes\n- 30 Lives Code");

    expect($service->exists($game))->toBeTrue()
        ->and($service->get($game))->toContain('30 Lives Code');
    expect(Storage::disk('data')->exists('cheats/nes/contra/cheats.md'))->toBeTrue();
});

it('deletes the file', function () {
    $game = cheatServiceGame();
    $service = app(CheatSheetService::class);

    $service->put($game, '# Contra');
    $service->delete($game);

    expect($service->exists($game))->toBeFalse();
});

it('deleting a missing file is a no-op', function () {
    $game = cheatServiceGame();

    app(CheatSheetService::class)->delete($game);

    expect(true)->toBeTrue();
});

describe('save()', function () {
    it('writes trimmed markdown when non-blank', function () {
        $game = cheatServiceGame();
        $service = app(CheatSheetService::class);

        $service->save($game, "  # Contra\n\nCheats here  \n");

        expect($service->get($game))->toBe("# Contra\n\nCheats here");
    });

    it('deletes the file when markdown is blank', function () {
        $game = cheatServiceGame();
        $service = app(CheatSheetService::class);

        $service->put($game, '# Contra');
        $service->save($game, "   \n  ");

        expect($service->exists($game))->toBeFalse();
    });

    it('deletes the file when markdown is null', function () {
        $game = cheatServiceGame();
        $service = app(CheatSheetService::class);

        $service->put($game, '# Contra');
        $service->save($game, null);

        expect($service->exists($game))->toBeFalse();
    });
});

it('renders markdown to safe html via toHtml without a leading game-title h1', function () {
    $service = app(CheatSheetService::class);

    $html = $service->toHtml("# Contra\n\n## Cheat Codes\n- Up, Up, Down, Down — 30 lives");

    expect($html)->not->toContain('<h1>')
        ->and($html)->toContain('<h2>Cheat Codes</h2>')
        ->and($html)->toContain('<li>')
        ->and($html)->not->toContain('<script');
});

it('leaves content unchanged when there is no leading h1', function () {
    $service = app(CheatSheetService::class);

    $html = $service->toHtml("## Tips\n- Always pay the cabbie");

    expect($html)->toContain('<h2>Tips</h2>')
        ->and($html)->toContain('cabbie');
});

it('strips raw html input for safety', function () {
    $service = app(CheatSheetService::class);

    $html = $service->toHtml("# Contra\n\n<script>alert(1)</script>");

    expect($html)->not->toContain('<script');
});
