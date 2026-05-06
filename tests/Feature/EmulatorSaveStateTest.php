<?php

use App\Models\EmulatorSaveState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('savestates');
});

it('stores an authenticated user save state on the savestates disk', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->createWithContent('slot-1.state', 'binary-state-data');

    $response = $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console' => 'snes',
        'game_id' => '42',
        'emulator' => 'emulatorjs',
        'slot' => 1,
        'label' => 'Before boss',
        'state' => $file,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slot', 1)
        ->assertJsonPath('data.label', 'Before boss');

    $save = EmulatorSaveState::first();

    expect($save)->not->toBeNull()
        ->and($save->user_id)->toBe($user->id)
        ->and($save->size_bytes)->toBe(strlen('binary-state-data'))
        ->and($save->checksum)->toBe(hash('sha256', 'binary-state-data'));

    expect(Storage::disk('savestates')->exists($save->disk_path))->toBeTrue();
});

it('lists only the current users slots for a game', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    EmulatorSaveState::create([
        'user_id' => $user->id,
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'slot' => 1,
        'disk_path' => 'user-slot.state',
        'size_bytes' => 5,
        'checksum' => str_repeat('a', 64),
    ]);
    EmulatorSaveState::create([
        'user_id' => $otherUser->id,
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'slot' => 2,
        'disk_path' => 'other-slot.state',
        'size_bytes' => 5,
        'checksum' => str_repeat('b', 64),
    ]);

    $this->actingAs($user)->getJson(route('player-data.save-states.index', [
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slot', 1);
});

it('prevents users from downloading another users save state', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $save = EmulatorSaveState::create([
        'user_id' => $owner->id,
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'slot' => 1,
        'disk_path' => 'private-slot.state',
        'size_bytes' => 5,
        'checksum' => str_repeat('a', 64),
    ]);

    Storage::disk('savestates')->put($save->disk_path, 'state');

    $this->actingAs($otherUser)
        ->get(route('player-data.save-states.download', $save))
        ->assertForbidden();
});

it('deletes the current users save state and stored file', function () {
    $user = User::factory()->create();
    $save = EmulatorSaveState::create([
        'user_id' => $user->id,
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'slot' => 1,
        'disk_path' => 'slot-to-delete.state',
        'size_bytes' => 5,
        'checksum' => str_repeat('a', 64),
    ]);

    Storage::disk('savestates')->put($save->disk_path, 'state');

    $this->actingAs($user)
        ->deleteJson(route('player-data.save-states.destroy', $save))
        ->assertOk()
        ->assertJsonPath('data.deleted', true);

    expect(EmulatorSaveState::query()->whereKey($save->id)->exists())->toBeFalse()
        ->and(Storage::disk('savestates')->exists('slot-to-delete.state'))->toBeFalse();
});

it('stores per-game control settings for the authenticated user', function () {
    $user = User::factory()->create();
    $settings = [
        'localStorage' => [
            'emulatorjs-controls-nes-1' => '{"a":"BUTTON_1"}',
        ],
    ];

    $this->actingAs($user)->putJson(route('player-data.control-settings.store'), [
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'profile' => 'default',
        'settings' => $settings,
    ])
        ->assertOk()
        ->assertJsonPath('data.settings.localStorage.emulatorjs-controls-nes-1', '{"a":"BUTTON_1"}');

    $this->actingAs($user)->getJson(route('player-data.control-settings.show', [
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
        'profile' => 'default',
    ]))
        ->assertOk()
        ->assertJsonPath('data.settings.localStorage.emulatorjs-controls-nes-1', '{"a":"BUTTON_1"}');
});

it('requires authentication for save state endpoints', function () {
    $this->getJson(route('player-data.save-states.index', [
        'console' => 'nes',
        'game_id' => '1',
        'emulator' => 'emulatorjs',
    ]))->assertUnauthorized();
});
