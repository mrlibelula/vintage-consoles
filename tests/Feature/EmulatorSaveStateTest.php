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
    $file = UploadedFile::fake()->createWithContent('megaman-2-slot-1.state', 'binary-state-data');

    $response = $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
        'slot'      => 1,
        'label'     => 'Before boss',
        'state'     => $file,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.slot', 1)
        ->assertJsonPath('data.label', 'Before boss');

    $save = EmulatorSaveState::first();

    expect($save)->not->toBeNull()
        ->and($save->user_id)->toBe($user->id)
        ->and($save->game_slug)->toBe('megaman-2')
        ->and($save->size_bytes)->toBe(strlen('binary-state-data'))
        ->and($save->checksum)->toBe(hash('sha256', 'binary-state-data'));

    expect(Storage::disk('savestates')->exists($save->disk_path))->toBeTrue();

    $expectedPath = "{$user->id}/nes/megaman-2/megaman-2-slot-1.state";
    expect($save->disk_path)->toBe($expectedPath);
});

it('lists only the current users slots for a game', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    EmulatorSaveState::create([
        'user_id'   => $user->id,
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
        'slot'      => 1,
        'disk_path' => 'user-slot.state',
        'size_bytes' => 5,
        'checksum'  => str_repeat('a', 64),
    ]);
    EmulatorSaveState::create([
        'user_id'   => $otherUser->id,
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
        'slot'      => 2,
        'disk_path' => 'other-slot.state',
        'size_bytes' => 5,
        'checksum'  => str_repeat('b', 64),
    ]);

    $this->actingAs($user)->getJson(route('player-data.save-states.index', [
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slot', 1);
});

it('prevents users from downloading another users save state', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $save = EmulatorSaveState::create([
        'user_id'   => $owner->id,
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
        'slot'      => 1,
        'disk_path' => 'private-slot.state',
        'size_bytes' => 5,
        'checksum'  => str_repeat('a', 64),
    ]);

    Storage::disk('savestates')->put($save->disk_path, 'state');

    $this->actingAs($otherUser)
        ->get(route('player-data.save-states.download', $save))
        ->assertForbidden();
});

it('deletes the current users save state and stored file', function () {
    $user = User::factory()->create();
    $save = EmulatorSaveState::create([
        'user_id'   => $user->id,
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
        'slot'      => 1,
        'disk_path' => 'slot-to-delete.state',
        'backup_disk_path' => 'slot-to-delete.state.backup',
        'size_bytes' => 5,
        'checksum'  => str_repeat('a', 64),
        'backup_size_bytes' => 4,
        'backup_checksum' => str_repeat('b', 64),
        'backup_updated_at' => now(),
    ]);

    Storage::disk('savestates')->put($save->disk_path, 'state');
    Storage::disk('savestates')->put($save->backup_disk_path, 'prev');

    $this->actingAs($user)
        ->deleteJson(route('player-data.save-states.destroy', $save))
        ->assertOk()
        ->assertJsonPath('data.deleted', true);

    expect(EmulatorSaveState::query()->whereKey($save->id)->exists())->toBeFalse()
        ->and(Storage::disk('savestates')->exists('slot-to-delete.state'))->toBeFalse()
        ->and(Storage::disk('savestates')->exists('slot-to-delete.state.backup'))->toBeFalse();
});

it('rotates the previous slot state into a backup on subsequent saves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console'   => 'snes',
        'game_slug' => 'dkc',
        'slot'      => 1,
        'state'     => UploadedFile::fake()->createWithContent('dkc-slot-1.state', 'first-state'),
    ])->assertCreated();

    $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console'   => 'snes',
        'game_slug' => 'dkc',
        'slot'      => 1,
        'state'     => UploadedFile::fake()->createWithContent('dkc-slot-1.state', 'second-state'),
    ])->assertCreated();

    $save = EmulatorSaveState::first();
    expect($save)->not->toBeNull()
        ->and($save->disk_path)->toContain('slot-1.state')
        ->and($save->backup_disk_path)->toContain('slot-1.state.backup')
        ->and($save->checksum)->toBe(hash('sha256', 'second-state'))
        ->and($save->backup_checksum)->toBe(hash('sha256', 'first-state'));

    expect(Storage::disk('savestates')->get($save->disk_path))->toBe('second-state')
        ->and(Storage::disk('savestates')->get($save->backup_disk_path))->toBe('first-state');
});

it('restores the previous backup by swapping primary and backup then returns updated metadata', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console'   => 'snes',
        'game_slug' => 'dkc',
        'slot'      => 1,
        'state'     => UploadedFile::fake()->createWithContent('dkc-slot-1.state', 'first-state'),
    ])->assertCreated();

    $this->actingAs($user)->postJson(route('player-data.save-states.store'), [
        'console'   => 'snes',
        'game_slug' => 'dkc',
        'slot'      => 1,
        'state'     => UploadedFile::fake()->createWithContent('dkc-slot-1.state', 'second-state'),
    ])->assertCreated();

    $save = EmulatorSaveState::first();
    $primaryPath = $save->disk_path;
    $backupPath = $save->backup_disk_path;

    $response = $this->actingAs($user)
        ->postJson(route('player-data.save-states.restore-backup', $save));

    $response->assertOk()
        ->assertJsonPath('data.slot', 1)
        ->assertJsonPath('data.has_backup', true)
        ->assertJsonPath('data.checksum', hash('sha256', 'first-state'));

    expect(Storage::disk('savestates')->get($primaryPath))->toBe('first-state')
        ->and(Storage::disk('savestates')->get($backupPath))->toBe('second-state');
});

it('stores per-game control settings for the authenticated user', function () {
    $user = User::factory()->create();
    $settings = [
        'localStorage' => [
            'emulatorjs-controls-nes-1' => '{"a":"BUTTON_1"}',
        ],
    ];

    $this->actingAs($user)->putJson(route('player-data.control-settings.store'), [
        'console'  => 'nes',
        'game_id'  => '1',
        'emulator' => 'emulatorjs',
        'profile'  => 'default',
        'settings' => $settings,
    ])
        ->assertOk()
        ->assertJsonPath('data.settings.localStorage.emulatorjs-controls-nes-1', '{"a":"BUTTON_1"}');

    $this->actingAs($user)->getJson(route('player-data.control-settings.show', [
        'console'  => 'nes',
        'game_id'  => '1',
        'emulator' => 'emulatorjs',
        'profile'  => 'default',
    ]))
        ->assertOk()
        ->assertJsonPath('data.settings.localStorage.emulatorjs-controls-nes-1', '{"a":"BUTTON_1"}');
});

it('requires authentication for save state endpoints', function () {
    $this->getJson(route('player-data.save-states.index', [
        'console'   => 'nes',
        'game_slug' => 'megaman-2',
    ]))->assertUnauthorized();
});

it('builds the correct disk path from a game slug', function () {
    $action = app(\App\Actions\UpsertEmulatorSaveState::class);

    expect($action->diskPath(1, 'nes', 'megaman-2', 1))
        ->toBe('1/nes/megaman-2/megaman-2-slot-1.state');

    expect($action->diskPath(7, 'snes', 'super-mario-world', 3))
        ->toBe('7/snes/super-mario-world/super-mario-world-slot-3.state');
});
