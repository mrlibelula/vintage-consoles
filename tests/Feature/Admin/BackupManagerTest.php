<?php

use App\Livewire\Admin\BackupManager;
use App\Models\Console;
use App\Models\Genre;
use App\Models\User;
use App\Notifications\SiteDataRestored;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function setupBackupTests(): void
{
    Storage::fake('local');
    Storage::fake('data');
    Storage::fake('savestates');

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'user',  'guard_name' => 'web']);

    Console::create([
        'id'              => 1,
        'short_name'      => 'nes',
        'long_name'       => 'Nintendo Entertainment System',
        'description'     => '',
        'emulator_name'   => 'EmulatorJS',
        'console_bgs'     => [],
        'specs'           => [],
        'community_links' => [],
        'options'         => [],
    ]);
}

function makeAdmin(string $password = 'secret'): User
{
    $user = User::factory()->create(['password' => Hash::make($password)]);
    $user->assignRole('admin');
    return $user;
}

beforeEach(fn () => setupBackupTests());

// ─────────────────────────────────────────────────────────────────────────────
// Access control
// ─────────────────────────────────────────────────────────────────────────────

describe('Access control', function () {

    it('redirects guests to login', function () {
        $this->get('/admin/backup')->assertRedirect('/login');
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/admin/backup')->assertStatus(403);
    });

    it('allows admin users', function () {
        $admin = makeAdmin();

        $this->actingAs($admin)
            ->get('/admin/backup')
            ->assertStatus(200)
            ->assertSeeLivewire('admin.backup-manager');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Create backup
// ─────────────────────────────────────────────────────────────────────────────

describe('Create backup', function () {

    it('creates a backup without errors', function () {
        $admin = makeAdmin();

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('createBackup')
            ->assertHasNoErrors();

        expect(app(BackupService::class)->listBackups())->toHaveCount(1);
    });

    it('lists the new backup in component state', function () {
        $admin = makeAdmin();

        $component = Livewire::actingAs($admin)->test(BackupManager::class);
        expect($component->instance()->backups)->toBeEmpty();

        $component->call('createBackup');

        expect($component->instance()->backups)->toHaveCount(1);
    });

    it('creates a no-saves backup when checkbox unchecked', function () {
        $admin = makeAdmin();

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->set('includeSavestates', false)
            ->call('createBackup');

        $backups = app(BackupService::class)->listBackups();
        expect($backups[0]['includes_savestates'])->toBeFalse();
        expect($backups[0]['filename'])->toContain('_no-saves');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Preview
// ─────────────────────────────────────────────────────────────────────────────

describe('Preview modal', function () {

    it('opens preview modal with data', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openPreview', $filename)
            ->assertSet('showPreviewModal', true)
            ->assertSet('previewingFile', $filename);
    });

    it('closes preview modal', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openPreview', $filename)
            ->call('closePreview')
            ->assertSet('showPreviewModal', false)
            ->assertSet('previewingFile', null);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Restore — password gate
// ─────────────────────────────────────────────────────────────────────────────

describe('Restore password gate', function () {

    it('rejects wrong password and does not restore', function () {
        Genre::create(['name' => 'Action', 'description' => '']);
        $admin    = makeAdmin('correct-password');
        $filename = app(BackupService::class)->createBackup(false);

        // Add a genre after backup; after failed restore it should still be there
        Genre::create(['name' => 'Sports', 'description' => '']);
        expect(Genre::count())->toBe(2);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', $filename)
            ->set('restorePassword', 'wrong-password')
            ->call('confirmRestore')
            ->assertSet('restorePasswordError', 'Incorrect password. Restore aborted.');

        // Catalog must remain unchanged
        expect(Genre::count())->toBe(2);
    });

    it('accepts correct password and performs restore', function () {
        Notification::fake();

        Genre::create(['name' => 'Action', 'description' => '']);
        $admin    = makeAdmin('my-secret');
        $filename = app(BackupService::class)->createBackup(false);

        Genre::create(['name' => 'Sports', 'description' => '']);
        expect(Genre::count())->toBe(2);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', $filename)
            ->set('restorePassword', 'my-secret')
            ->call('confirmRestore')
            ->assertSet('showRestoreModal', false)
            ->assertSet('restoringFile', null)
            ->assertDispatched('toast', type: 'success', message: "Restore from {$filename} completed successfully.")
            ->assertDispatched('site-data-restored')
            ->assertHasNoErrors();

        // Catalog restored to backup state
        expect(Genre::count())->toBe(1);
        expect(Genre::first()->name)->toBe('Action');
    });

    it('sends notifications to all users after successful restore', function () {
        Notification::fake();

        $admin   = makeAdmin('pass');
        $regular = User::factory()->create();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', $filename)
            ->set('restorePassword', 'pass')
            ->call('confirmRestore')
            ->assertSet('showRestoreModal', false)
            ->assertDispatched('toast');

        Notification::assertSentTo($admin,   SiteDataRestored::class);
        Notification::assertSentTo($regular, SiteDataRestored::class);
    });

    it('closes restore modal and toasts an error when restore fails', function () {
        $admin = makeAdmin('pass');

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', 'missing-backup_no-saves.zip')
            ->set('restorePassword', 'pass')
            ->call('confirmRestore')
            ->assertSet('showRestoreModal', false)
            ->assertSet('restoringFile', null)
            ->assertDispatched('toast', type: 'error');
    });

    it('clears password field and error on cancel', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', $filename)
            ->set('restorePassword', 'typed-something')
            ->call('closeRestoreModal')
            ->assertSet('restorePassword', '')
            ->assertSet('restorePasswordError', null)
            ->assertSet('showRestoreModal', false);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Upload backup
// ─────────────────────────────────────────────────────────────────────────────

describe('Upload backup', function () {

    it('stores a valid uploaded zip and opens preview', function () {
        $admin = makeAdmin();
        $source = app(BackupService::class)->createBackup(false);
        $contents = Storage::disk('local')->get("backups/{$source}");
        Storage::disk('local')->delete("backups/{$source}");

        $upload = UploadedFile::fake()->createWithContent('restored-from-laptop.zip', $contents);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->set('uploadFile', $upload)
            ->call('uploadBackup')
            ->assertHasNoErrors()
            ->assertSet('showPreviewModal', true)
            ->assertSet('previewingFile', 'restored-from-laptop.zip')
            ->assertDispatched('toast', type: 'success');

        expect(Storage::disk('local')->exists('backups/restored-from-laptop.zip'))->toBeTrue();
        expect(app(BackupService::class)->listBackups())->toHaveCount(1);
    });

    it('rejects a zip that is not a valid backup archive', function () {
        $admin = makeAdmin();

        $tmp = tempnam(sys_get_temp_dir(), 'badbk_');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('readme.txt', 'not a backup');
        $zip->close();
        $contents = file_get_contents($tmp);
        @unlink($tmp);

        $upload = UploadedFile::fake()->createWithContent('fake.zip', $contents);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->set('uploadFile', $upload)
            ->call('uploadBackup')
            ->assertHasErrors(['uploadFile']);

        expect(app(BackupService::class)->listBackups())->toBeEmpty();
    });

    it('rejects non-zip uploads', function () {
        $admin = makeAdmin();
        $upload = UploadedFile::fake()->createWithContent('notes.txt', 'hello');

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->set('uploadFile', $upload)
            ->call('uploadBackup')
            ->assertHasErrors(['uploadFile']);
    });

    it('can restore after uploading a backup zip', function () {
        Notification::fake();

        Genre::create(['name' => 'Action', 'description' => '']);
        $admin = makeAdmin('my-secret');
        $source = app(BackupService::class)->createBackup(false);
        $contents = Storage::disk('local')->get("backups/{$source}");
        Storage::disk('local')->delete("backups/{$source}");

        Genre::create(['name' => 'Sports', 'description' => '']);
        expect(Genre::count())->toBe(2);

        $upload = UploadedFile::fake()->createWithContent('from-other-server.zip', $contents);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->set('uploadFile', $upload)
            ->call('uploadBackup')
            ->call('openRestoreModal', 'from-other-server.zip')
            ->set('restorePassword', 'my-secret')
            ->call('confirmRestore')
            ->assertSet('showRestoreModal', false)
            ->assertHasNoErrors();

        expect(Genre::count())->toBe(1);
        expect(Genre::first()->name)->toBe('Action');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Download backup
// ─────────────────────────────────────────────────────────────────────────────

describe('Download backup', function () {

    it('downloads an existing backup zip', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('downloadBackup', $filename)
            ->assertFileDownloaded($filename);
    });

    it('rejects path traversal filenames without downloading', function () {
        $admin = makeAdmin();
        app(BackupService::class)->createBackup(false);

        expect(fn () => app(BackupService::class)->downloadBackup('../secrets.zip'))
            ->toThrow(InvalidArgumentException::class);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('downloadBackup', '../secrets.zip')
            ->assertNoFileDownloaded();
    });

    it('rejects missing backups without downloading', function () {
        $admin = makeAdmin();

        expect(fn () => app(BackupService::class)->downloadBackup('backup_missing_no-saves.zip'))
            ->toThrow(RuntimeException::class);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('downloadBackup', 'backup_missing_no-saves.zip')
            ->assertNoFileDownloaded();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Delete backup
// ─────────────────────────────────────────────────────────────────────────────

describe('Delete backup', function () {

    it('opens and closes the delete confirmation modal', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openDeleteModal', $filename)
            ->assertSet('showDeleteModal', true)
            ->assertSet('deletingFile', $filename)
            ->assertSee('Delete Backup')
            ->assertSee('permanently remove')
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false)
            ->assertSet('deletingFile', null);

        expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeTrue();
    });

    it('deletes the file and removes it from the list', function () {
        $admin    = makeAdmin();
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openDeleteModal', $filename)
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', false)
            ->assertHasNoErrors();

        expect(Storage::disk('local')->exists("backups/{$filename}"))->toBeFalse();

        $list = app(BackupService::class)->listBackups();
        expect($list)->toBeEmpty();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Savestates not touched when backup has no savestates
// ─────────────────────────────────────────────────────────────────────────────

describe('Savestates isolation via Livewire restore', function () {

    it('does not modify emulator data when backup lacks savestates', function () {
        Notification::fake();

        $user  = User::factory()->create();
        $admin = makeAdmin('pass');

        \App\Models\EmulatorSaveState::create([
            'user_id'   => $user->id,
            'console'   => 'nes',
            'game_slug' => 'mario',
            'slot'      => 0,
            'disk_path' => '1/nes/mario_0.state',
            'checksum'  => 'abc123',
        ]);

        Storage::disk('savestates')->put('1/nes/mario_0.state', 'save-data');

        // Backup without savestates
        $filename = app(BackupService::class)->createBackup(false);

        Livewire::actingAs($admin)
            ->test(BackupManager::class)
            ->call('openRestoreModal', $filename)
            ->set('restorePassword', 'pass')
            ->call('confirmRestore');

        expect(\App\Models\EmulatorSaveState::count())->toBe(1);
        expect(Storage::disk('savestates')->exists('1/nes/mario_0.state'))->toBeTrue();
    });
});
