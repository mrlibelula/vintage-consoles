<?php

use App\Livewire\MySaves;
use App\Models\Console;
use App\Models\EmulatorSaveState;
use App\Models\Game;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Seed helpers
// ─────────────────────────────────────────────────────────────────────────────

function seedSaveConsoles(): void
{
    $nes = Console::factory()->create([
        'short_name' => 'nes',
        'long_name'  => 'Nintendo Entertainment System',
    ]);

    Game::factory()->create([
        'console_id'        => $nes->id,
        'title'             => 'Super Mario Bros.',
        'slug'              => 'super-mario-bros',
        'save_state_support'=> true,
    ]);
    Game::factory()->create([
        'console_id'        => $nes->id,
        'title'             => 'Zelda',
        'slug'              => 'zelda',
        'save_state_support'=> true,
    ]);

    $snes = Console::factory()->create([
        'short_name' => 'snes',
        'long_name'  => 'Super Nintendo',
    ]);

    // Force id=10 for the legacy numeric-key back-compat test.
    Game::factory()->create([
        'id'                => 10,
        'console_id'        => $snes->id,
        'title'             => 'Donkey Kong Country',
        'slug'              => 'donkey-kong-country',
        'save_state_support'=> true,
    ]);
}

beforeEach(function () {
    Storage::fake('savestates');
    seedSaveConsoles();
});

it('stores an uploaded save via My Saves flow', function () {
    $user = User::factory()->create();

    EmulatorSaveState::create([
        'user_id' => $user->id,
        'console' => 'nes',
        'game_slug' => 'super-mario-bros',
        'slot' => 1,
        'disk_path' => "{$user->id}/nes/super-mario-bros/super-mario-bros-slot-1.state",
        'label' => 'Existing',
        'size_bytes' => 3,
        'checksum' => hash('sha256', 'old'),
    ]);
    Storage::disk('savestates')->put("{$user->id}/nes/super-mario-bros/super-mario-bros-slot-1.state", 'old');

    $file = UploadedFile::fake()->createWithContent('slot-2.state', 'fresh-upload');

    Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('openUploadModal', 'nes', 'super-mario-bros', 2, 'Super Mario Bros.')
        ->set('uploadStateFile', $file)
        ->call('submitUpload')
        ->assertHasNoErrors();

    $slot2 = EmulatorSaveState::query()
        ->where('user_id', $user->id)
        ->where('console', 'nes')
        ->where('game_slug', 'super-mario-bros')
        ->where('slot', 2)
        ->first();

    expect($slot2)->not->toBeNull()
        ->and($slot2->checksum)->toBe(hash('sha256', 'fresh-upload'))
        ->and(Storage::disk('savestates')->exists($slot2->disk_path))->toBeTrue();

    $slot1 = EmulatorSaveState::query()
        ->where('user_id', $user->id)
        ->where('slot', 1)
        ->first();

    expect($slot1->checksum)->toBe(hash('sha256', 'old'));
});

it('rejects my saves upload without a file', function () {
    $user = User::factory()->create();

    EmulatorSaveState::create([
        'user_id' => $user->id,
        'console' => 'nes',
        'game_slug' => 'super-mario-bros',
        'slot' => 1,
        'disk_path' => 'x.state',
        'size_bytes' => 1,
        'checksum' => 'a',
    ]);

    Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('openUploadModal', 'nes', 'super-mario-bros', 2, 'Super Mario Bros.')
        ->call('submitUpload')
        ->assertHasErrors(['uploadStateFile']);
});

it('populates game options when a console is selected in the global upload modal', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('openGlobalUploadModal')
        ->set('globalConsole', 'nes');

    $component->assertHasNoErrors();
    expect($component->get('globalGameOptions'))->toHaveCount(2);
});

it('uploads a state file for a game not yet in my saves via global upload', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->createWithContent('donkey-kong.state', 'dk-save-data');

    Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('openGlobalUploadModal')
        ->set('globalConsole', 'snes')
        ->set('globalGameSlug', 'donkey-kong-country')
        ->set('globalSlot', 3)
        ->set('globalLabel', 'World 5')
        ->set('globalStateFile', $file)
        ->call('submitGlobalUpload')
        ->assertHasNoErrors();

    $save = EmulatorSaveState::query()
        ->where('user_id', $user->id)
        ->where('console', 'snes')
        ->where('game_slug', 'donkey-kong-country')
        ->where('slot', 3)
        ->first();

    expect($save)->not->toBeNull()
        ->and($save->label)->toBe('World 5')
        ->and($save->checksum)->toBe(hash('sha256', 'dk-save-data'))
        ->and(Storage::disk('savestates')->exists($save->disk_path))->toBeTrue();
});

it('rejects global upload when no file is provided', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('openGlobalUploadModal')
        ->set('globalConsole', 'nes')
        ->set('globalGameSlug', 'super-mario-bros')
        ->set('globalSlot', 1)
        ->call('submitGlobalUpload')
        ->assertHasErrors(['globalStateFile']);
});

it('syncs orphaned savestate files from disk into the database', function () {
    $user = User::factory()->create();

    Storage::disk('savestates')->put("{$user->id}/snes/donkey-kong-country/donkey-kong-country-slot-1.state", 'orphaned-data');

    expect(EmulatorSaveState::query()->where('user_id', $user->id)->count())->toBe(0);

    Livewire::actingAs($user)
        ->test(MySaves::class)
        ->call('syncFromDisk')
        ->assertHasNoErrors();

    $save = EmulatorSaveState::query()
        ->where('user_id', $user->id)
        ->where('console', 'snes')
        ->where('game_slug', 'donkey-kong-country')
        ->where('slot', 1)
        ->first();

    expect($save)->not->toBeNull()
        ->and($save->checksum)->toBe(hash('sha256', 'orphaned-data'))
        ->and($save->size_bytes)->toBe(strlen('orphaned-data'))
        ->and(Storage::disk('savestates')->exists($save->disk_path))->toBeTrue();
});

it('shows the correct game title when legacy numeric ids are stored in game_slug', function () {
    $user = User::factory()->create();

    EmulatorSaveState::create([
        'user_id' => $user->id,
        'console' => 'snes',
        // Legacy production data: numeric ID stored in `game_slug`
        'game_slug' => '10',
        'slot' => 1,
        'disk_path' => "{$user->id}/snes/donkey-kong-country/donkey-kong-country-slot-1.state",
        'label' => null,
        'size_bytes' => 1,
        'checksum' => hash('sha256', 'x'),
    ]);

    $component = Livewire::actingAs($user)->test(MySaves::class);

    $grouped = $component->get('grouped');
    expect($grouped)->toHaveKey('snes');

    $games = $grouped['snes']['games'] ?? [];
    expect($games)->toHaveKey('10');
    expect($games['10']['title'])->toBe('Donkey Kong Country');
    expect($games['10']['slug'])->toBe('donkey-kong-country');
});

it('filters saved games by search term', function () {
    $user = User::factory()->create();

    foreach ([
        ['console' => 'nes', 'game_slug' => 'super-mario-bros', 'slot' => 1],
        ['console' => 'nes', 'game_slug' => 'zelda', 'slot' => 1],
        ['console' => 'snes', 'game_slug' => 'donkey-kong-country', 'slot' => 1],
    ] as $save) {
        EmulatorSaveState::create([
            'user_id' => $user->id,
            'console' => $save['console'],
            'game_slug' => $save['game_slug'],
            'slot' => $save['slot'],
            'disk_path' => "{$user->id}/{$save['console']}/{$save['game_slug']}/{$save['game_slug']}-slot-{$save['slot']}.state",
            'size_bytes' => 1,
            'checksum' => hash('sha256', $save['game_slug']),
        ]);
    }

    $component = Livewire::actingAs($user)->test(MySaves::class);

    $filtered = $component->get('filteredGrouped');
    expect($filtered)->toHaveKeys(['nes', 'snes']);

    $component
        ->set('gameSearch', 'zelda')
        ->assertSee('Zelda')
        ->assertDontSee('Super Mario Bros.');

    $filtered = $component->get('filteredGrouped');
    expect($filtered)->toHaveKey('nes')
        ->and($filtered['nes']['games'])->toHaveKey('zelda')
        ->and($filtered)->not->toHaveKey('snes');

    $component
        ->call('clearGameSearch')
        ->assertSet('gameSearch', '');

    expect($component->get('filteredGrouped'))->toHaveKeys(['nes', 'snes']);
});
