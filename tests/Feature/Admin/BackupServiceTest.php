<?php

use App\Models\Console;
use App\Models\EmulatorControlSetting;
use App\Models\EmulatorSaveState;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\SiteDataRestored;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use ZipArchive as ZipArchiveAlias;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function fakeAllDisks(): void
{
    Storage::fake('local');
    Storage::fake('data');
    Storage::fake('savestates');
}

function seedConsole(): Console
{
    return Console::create([
        'id'               => 1,
        'short_name'       => 'nes',
        'long_name'        => 'Nintendo Entertainment System',
        'description'      => '',
        'emulator_name'    => 'EmulatorJS',
        'console_bgs'      => [],
        'specs'            => [],
        'community_links'  => [],
        'options'          => [],
    ]);
}

function openZipFromLocal(string $filename): ZipArchiveAlias
{
    $content = Storage::disk('local')->get("backups/{$filename}");
    $tmp = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tmp, $content);
    $zip = new ZipArchiveAlias();
    $zip->open($tmp);
    register_shutdown_function(fn () => @unlink($tmp));
    return $zip;
}

beforeEach(function () {
    fakeAllDisks();
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);
    seedConsole();
});

// ─────────────────────────────────────────────────────────────────────────────
// createBackup — with savestates
// ─────────────────────────────────────────────────────────────────────────────

describe('createBackup with savestates', function () {

    it('returns a filename and stores the zip', function () {
        $filename = app(BackupService::class)->createBackup(true);

        expect($filename)->toEndWith('.zip');
        expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeTrue();
    });

    it('includes manifest with includes_savestates = true', function () {
        $filename = app(BackupService::class)->createBackup(true);
        $zip = openZipFromLocal($filename);
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $zip->close();

        expect($manifest['includes_savestates'])->toBeTrue();
        expect($manifest['version'])->toBe(1);
    });

    it('includes db/core.json with catalog tables', function () {
        Genre::create(['name' => 'Action', 'description' => '']);

        $filename = app(BackupService::class)->createBackup(true);
        $zip = openZipFromLocal($filename);
        $core = json_decode($zip->getFromName('db/core.json'), true);
        $zip->close();

        expect($core['tables'])->toHaveKeys(['consoles', 'genres', 'games', 'app_fonts', 'app_settings']);
        expect($core['tables']['genres'])->toHaveCount(1);
    });

    it('exports game_genre without ordering by id', function () {
        \Illuminate\Support\Facades\DB::table('game_genre')->insert([
            'game_id'  => \App\Models\Game::create([
                'console_id'         => 1,
                'title'              => 'Test',
                'slug'               => 'test',
                'publisher'          => '',
                'release_year'       => '1985',
                'description'        => '',
                'rating'             => 0.5,
                'multiplayer_support'=> false,
                'save_state_support' => true,
                'is_free'            => true,
                'needs_igdb_sync'    => false,
            ])->id,
            'genre_id' => Genre::create(['name' => 'Action', 'description' => ''])->id,
        ]);

        expect(fn () => app(BackupService::class)->createBackup(false))->not->toThrow(\Throwable::class);
    });

    it('exports app_settings ordered by key without an id column', function () {
        \Illuminate\Support\Facades\DB::table('app_settings')->insert([
            'key'        => 'test_setting',
            'value'      => '42',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filename = app(BackupService::class)->createBackup(false);
        $zip = openZipFromLocal($filename);
        $core = json_decode($zip->getFromName('db/core.json'), true);
        $zip->close();

        $keys = array_column($core['tables']['app_settings'], 'key');
        expect($keys)->toContain('test_setting');
    });

    it('includes db/user_data.json when savestates ON', function () {
        $user = User::factory()->create();
        EmulatorSaveState::create([
            'user_id'   => $user->id,
            'console'   => 'nes',
            'game_slug' => 'super-mario',
            'slot'      => 1,
            'disk_path' => '1/nes/super-mario_1.state',
            'checksum'  => 'abc123',
        ]);

        $filename = app(BackupService::class)->createBackup(true);
        $zip = openZipFromLocal($filename);

        expect($zip->getFromName('db/user_data.json'))->not->toBeFalse();
        $emulator = json_decode($zip->getFromName('db/user_data.json'), true);
        $zip->close();

        expect($emulator['tables']['emulator_save_states'])->toHaveCount(1);
    });

    it('includes savestates files from the savestates disk', function () {
        Storage::disk('savestates')->put('1/nes/super-mario_1.state', 'data');

        $filename = app(BackupService::class)->createBackup(true);
        $zip = openZipFromLocal($filename);

        $found = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->getNameIndex($i), 'savestates/')) {
                $found = true;
                break;
            }
        }
        $zip->close();

        expect($found)->toBeTrue();
    });

    it('includes migration-docs and chat files', function () {
        Storage::disk('local')->put('migration-docs/test.md', '# Test');
        Storage::disk('data')->put('chat/1.1.json', '[]');

        $filename = app(BackupService::class)->createBackup(true);
        $zip = openZipFromLocal($filename);

        expect($zip->getFromName('migration-docs/test.md'))->toBe('# Test');
        expect($zip->getFromName('chat/1.1.json'))->toBe('[]');
        $zip->close();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// createBackup — without savestates
// ─────────────────────────────────────────────────────────────────────────────

describe('createBackup without savestates', function () {

    it('filename ends with _no-saves.zip', function () {
        $filename = app(BackupService::class)->createBackup(false);
        expect($filename)->toContain('_no-saves');
    });

    it('manifest.includes_savestates is false', function () {
        $filename = app(BackupService::class)->createBackup(false);
        $zip = openZipFromLocal($filename);
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $zip->close();

        expect($manifest['includes_savestates'])->toBeFalse();
    });

    it('does NOT include db/user_data.json', function () {
        $filename = app(BackupService::class)->createBackup(false);
        $zip = openZipFromLocal($filename);
        $has = $zip->getFromName('db/user_data.json');
        $zip->close();

        expect($has)->toBeFalse();
    });

    it('does NOT include savestates/ entries', function () {
        Storage::disk('savestates')->put('1/nes/game_1.state', 'data');

        $filename = app(BackupService::class)->createBackup(false);
        $zip = openZipFromLocal($filename);

        $found = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (str_starts_with($zip->getNameIndex($i), 'savestates/')) {
                $found = true;
                break;
            }
        }
        $zip->close();

        expect($found)->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// listBackups
// ─────────────────────────────────────────────────────────────────────────────

describe('listBackups', function () {

    it('returns empty array when no backups exist', function () {
        expect(app(BackupService::class)->listBackups())->toBeArray()->toBeEmpty();
    });

    it('returns metadata for each backup', function () {
        app(BackupService::class)->createBackup(true);
        $list = app(BackupService::class)->listBackups();

        expect($list)->toHaveCount(1);
        expect($list[0])->toHaveKeys(['filename', 'size_human', 'includes_savestates', 'created_at']);
        expect($list[0]['includes_savestates'])->toBeTrue();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// previewBackup
// ─────────────────────────────────────────────────────────────────────────────

describe('previewBackup', function () {

    it('returns manifest, db diff, and file sections', function () {
        Genre::create(['name' => 'RPG', 'description' => '']);
        Storage::disk('data')->put('chat/1.1.json', '[]');

        $filename = app(BackupService::class)->createBackup(false);
        $preview  = app(BackupService::class)->previewBackup($filename);

        expect($preview)->toHaveKeys(['manifest', 'db', 'files']);
        expect($preview['manifest']['includes_savestates'])->toBeFalse();
        expect($preview['db']['genres']['backup_rows'])->toBe(1);
        expect($preview['files']['chat'])->toHaveKeys(['only_in_backup', 'only_on_disk', 'in_both']);
    });

    it('marks emulator tables as not in backup when savestates absent', function () {
        $filename = app(BackupService::class)->createBackup(false);
        $preview  = app(BackupService::class)->previewBackup($filename);

        expect($preview['db']['emulator_save_states']['in_backup'])->toBeFalse();
        expect($preview['db']['emulator_save_states']['backup_rows'])->toBeNull();
        expect($preview['files']['savestates'])->toBeNull();
    });

    it('shows row diff when server has more rows than backup', function () {
        Genre::create(['name' => 'Platformer', 'description' => '']);
        $filename = app(BackupService::class)->createBackup(false);

        // Add more genres after backup was taken
        Genre::create(['name' => 'Shooter', 'description' => '']);
        $preview = app(BackupService::class)->previewBackup($filename);

        expect($preview['db']['genres']['diff'])->toBe(-1);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// restoreBackup — core only (no savestates in backup)
// ─────────────────────────────────────────────────────────────────────────────

describe('restoreBackup without savestates', function () {

    it('restores catalog tables and does NOT touch emulator tables', function () {
        $user = User::factory()->create();

        // Seed a save state BEFORE backup
        $saveState = EmulatorSaveState::create([
            'user_id'   => $user->id,
            'console'   => 'nes',
            'game_slug' => 'zelda',
            'slot'      => 1,
            'disk_path' => '1/nes/zelda_1.state',
            'checksum'  => 'abc123',
        ]);

        // Backup created without savestates
        Genre::create(['name' => 'Adventure', 'description' => '']);
        $filename = app(BackupService::class)->createBackup(false);

        // Mutate the DB after backup
        Genre::create(['name' => 'Sports', 'description' => '']);
        expect(Genre::count())->toBe(2);

        // Restore
        app(BackupService::class)->restoreBackup($filename);

        // Catalog is restored to backup state
        expect(Genre::count())->toBe(1);
        expect(Genre::first()->name)->toBe('Adventure');

        // Emulator save state is UNTOUCHED
        expect(EmulatorSaveState::count())->toBe(1);
        expect(EmulatorSaveState::first()->id)->toBe($saveState->id);
    });

    it('restores migration-docs and chat files, leaves savestates disk alone', function () {
        Storage::disk('local')->put('migration-docs/a.md', '# A');
        Storage::disk('data')->put('chat/1.1.json', '{"messages":[]}');
        Storage::disk('savestates')->put('1/game_1.state', 'savedata');

        $filename = app(BackupService::class)->createBackup(false);

        // Add extra files on disk after backup
        Storage::disk('data')->put('chat/2.2.json', '{}');

        app(BackupService::class)->restoreBackup($filename);

        // chat restored: 2.2.json should be gone
        expect(Storage::disk('data')->exists('chat/1.1.json'))->toBeTrue();
        expect(Storage::disk('data')->exists('chat/2.2.json'))->toBeFalse();

        // savestates disk unchanged
        expect(Storage::disk('savestates')->exists('1/game_1.state'))->toBeTrue();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// restoreBackup — with savestates
// ─────────────────────────────────────────────────────────────────────────────

describe('restoreBackup with savestates', function () {

    it('restores emulator tables and savestates disk', function () {
        $user = User::factory()->create();
        EmulatorSaveState::create([
            'user_id'   => $user->id,
            'console'   => 'nes',
            'game_slug' => 'mario',
            'slot'      => 0,
            'disk_path' => '1/nes/mario_0.state',
            'checksum'  => 'abc000',
        ]);
        Storage::disk('savestates')->put('1/nes/mario_0.state', 'original');

        $filename = app(BackupService::class)->createBackup(true);

        // Mutate after backup
        EmulatorSaveState::create([
            'user_id'   => $user->id,
            'console'   => 'nes',
            'game_slug' => 'mario',
            'slot'      => 2,
            'disk_path' => '1/nes/mario_2.state',
            'checksum'  => 'abc222',
        ]);
        Storage::disk('savestates')->put('1/nes/mario_2.state', 'newer');

        app(BackupService::class)->restoreBackup($filename);

        // Only the state from backup time should exist
        expect(EmulatorSaveState::count())->toBe(1);
        expect(Storage::disk('savestates')->exists('1/nes/mario_0.state'))->toBeTrue();
        expect(Storage::disk('savestates')->exists('1/nes/mario_2.state'))->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Notifications after restore
// ─────────────────────────────────────────────────────────────────────────────

describe('SiteDataRestored notification', function () {

    it('is sent to all users after restore', function () {
        Notification::fake();

        $admin   = User::factory()->create();
        $admin->assignRole('admin');
        $regular = User::factory()->create();

        $filename = app(BackupService::class)->createBackup(false);

        // Simulate what BackupManager::confirmRestore does
        $preview = app(BackupService::class)->previewBackup($filename);
        app(BackupService::class)->restoreBackup($filename);

        $includesSavestates = $preview['manifest']['includes_savestates'];

        User::query()->each(fn (User $u) => $u->notify(
            new SiteDataRestored($filename, $includesSavestates, $admin->name)
        ));

        Notification::assertSentTo($admin, SiteDataRestored::class);
        Notification::assertSentTo($regular, SiteDataRestored::class);
    });

    it('notification message says save games not changed when no savestates', function () {
        Notification::fake();

        $user     = User::factory()->create();
        $filename = app(BackupService::class)->createBackup(false);

        $user->notify(new SiteDataRestored($filename, false, 'Admin'));

        Notification::assertSentTo($user, SiteDataRestored::class, function ($notification) {
            $data = $notification->toDatabase($notification);
            return str_contains($data['message'], 'not changed');
        });
    });

    it('notification message warns saves may be replaced when savestates included', function () {
        Notification::fake();

        $user     = User::factory()->create();
        $filename = app(BackupService::class)->createBackup(true);

        $user->notify(new SiteDataRestored($filename, true, 'Admin'));

        Notification::assertSentTo($user, SiteDataRestored::class, function ($notification) {
            $data = $notification->toDatabase($notification);
            return str_contains($data['message'], 'may have been replaced');
        });
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// deleteBackup
// ─────────────────────────────────────────────────────────────────────────────

describe('deleteBackup', function () {

    it('removes the zip file', function () {
        $filename = app(BackupService::class)->createBackup(false);
        expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeTrue();

        app(BackupService::class)->deleteBackup($filename);

        expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeFalse();
    });
});
