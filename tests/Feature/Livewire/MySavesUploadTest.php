<?php

use App\Livewire\MySaves;
use App\Models\EmulatorSaveState;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('savestates');

    Session::put('consoles', [
        [
            'short_name' => 'nes',
            'long_name' => 'Nintendo Entertainment System',
            'console_icon' => null,
            'console_logo' => null,
            'games' => [
                ['id' => 1, 'title' => 'Super Mario Bros.', 'slug' => 'super-mario-bros'],
                ['id' => 2, 'title' => 'Zelda', 'slug' => 'zelda'],
            ],
        ],
        [
            'short_name' => 'snes',
            'long_name' => 'Super Nintendo',
            'console_icon' => null,
            'console_logo' => null,
            'games' => [
                ['id' => 10, 'title' => 'Donkey Kong Country', 'slug' => 'donkey-kong-country'],
            ],
        ],
    ]);
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
