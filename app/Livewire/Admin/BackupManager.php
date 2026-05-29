<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Notifications\SiteDataRestored;
use App\Services\BackupService;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]

class BackupManager extends Component
{
    public bool $includeSavestates = true;
    public array $backups = [];
    public bool $isCreating = false;

    // Preview modal
    public bool $showPreviewModal = false;
    public ?string $previewingFile = null;
    public array $previewData = [];

    // Delete modal
    public bool $showDeleteModal = false;
    public ?string $deletingFile = null;

    // Restore modal
    public bool $showRestoreModal = false;
    public ?string $restoringFile = null;
    public string $restorePassword = '';
    public ?string $restorePasswordError = null;
    public bool $isRestoring = false;

    public bool $isPreviewLoading = false;

    public ?string $previewError = null;

    public function mount(): void
    {
        $this->loadBackups();
    }

    public function createBackup(): void
    {
        $this->isCreating = true;

        try {
            $filename = app(BackupService::class)->createBackup($this->includeSavestates);
            $this->loadBackups();
            session()->flash('success', "Backup created: {$filename}");
        } catch (\Throwable $e) {
            session()->flash('error', "Failed to create backup: {$e->getMessage()}");
        } finally {
            $this->isCreating = false;
        }
    }

    public function openPreview(string $filename): void
    {
        $this->previewError       = null;
        $this->isPreviewLoading   = true;
        $this->showPreviewModal   = true;
        $this->previewingFile     = $filename;
        $this->previewData        = [];

        try {
            $this->previewData = app(BackupService::class)->previewBackup($filename);
        } catch (\Throwable $e) {
            $this->previewError = "Failed to load preview: {$e->getMessage()}";
            session()->flash('error', $this->previewError);
        } finally {
            $this->isPreviewLoading = false;
        }
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewingFile   = null;
        $this->previewData      = [];
        $this->previewError     = null;
        $this->isPreviewLoading = false;
    }

    public function openRestoreModal(string $filename): void
    {
        $this->restoringFile        = $filename;
        $this->restorePassword      = '';
        $this->restorePasswordError = null;
        $this->showRestoreModal     = true;
        $this->showPreviewModal     = false;
    }

    public function confirmRestore(): void
    {
        $this->restorePasswordError = null;

        if (! Hash::check($this->restorePassword, auth()->user()->password)) {
            $this->restorePasswordError = 'Incorrect password. Restore aborted.';
            return;
        }

        $this->isRestoring = true;
        $filename = $this->restoringFile;

        try {
            $service  = app(BackupService::class);
            $preview  = $service->previewBackup($filename);
            $service->restoreBackup($filename);

            $includesSavestates = $preview['manifest']['includes_savestates'];
            $adminName          = auth()->user()->name;

            User::query()->each(function (User $user) use ($filename, $includesSavestates, $adminName) {
                $user->notify(new SiteDataRestored(
                    backupFilename:    $filename,
                    includesSavestates: $includesSavestates,
                    adminName:         $adminName,
                ));
            });

            $this->showRestoreModal = false;
            $this->restoringFile    = null;
            $this->restorePassword  = '';
            $this->loadBackups();
            session()->flash('success', "Restore from {$filename} completed successfully.");
        } catch (\Throwable $e) {
            session()->flash('error', "Restore failed: {$e->getMessage()}");
        } finally {
            $this->isRestoring = false;
        }
    }

    public function closeRestoreModal(): void
    {
        $this->showRestoreModal     = false;
        $this->restoringFile        = null;
        $this->restorePassword      = '';
        $this->restorePasswordError = null;
    }

    public function openDeleteModal(string $filename): void
    {
        $this->deletingFile    = $filename;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingFile    = null;
    }

    public function confirmDelete(): void
    {
        $filename = $this->deletingFile;

        if ($filename === null) {
            return;
        }

        try {
            app(BackupService::class)->deleteBackup($filename);
            $this->closeDeleteModal();
            $this->loadBackups();
            session()->flash('success', "Backup {$filename} deleted.");
        } catch (\Throwable $e) {
            session()->flash('error', "Failed to delete: {$e->getMessage()}");
        }
    }

    private function loadBackups(): void
    {
        $this->backups = app(BackupService::class)->listBackups();
    }

    public function render()
    {
        return view('livewire.admin.backup-manager');
    }
}
