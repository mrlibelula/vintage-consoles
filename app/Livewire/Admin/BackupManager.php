<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithToasts;
use App\Models\User;
use App\Notifications\SiteDataRestored;
use App\Services\BackupService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]

class BackupManager extends Component
{
    use WithFileUploads;
    use WithToasts;

    public bool $includeSavestates = false;
    public array $backups = [];
    public bool $isCreating = false;

    /** @var mixed Livewire temporary upload */
    public $uploadFile = null;

    public bool $isUploading = false;

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
            $this->toast('success', "Backup created: {$filename}");
        } catch (\Throwable $e) {
            report($e);
            $this->toast('error', "Failed to create backup: {$e->getMessage()}");
        } finally {
            $this->isCreating = false;
        }
    }

    public function uploadBackup(): void
    {
        $this->validate([
            'uploadFile' => [
                'required',
                File::default()->extensions(['zip'])->max(524288), // 512 MB
            ],
        ]);

        $this->isUploading = true;

        try {
            $filename = app(BackupService::class)->storeUploadedBackup($this->uploadFile);
            $this->reset('uploadFile');
            $this->loadBackups();
            $this->toast('success', "Backup uploaded: {$filename}");
            $this->openPreview($filename);
        } catch (\InvalidArgumentException $e) {
            $this->addError('uploadFile', $e->getMessage());
            $this->toast('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            $this->toast('error', "Failed to upload backup: {$e->getMessage()}");
        } finally {
            $this->isUploading = false;
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
            report($e);
            $this->previewError = "Failed to load preview: {$e->getMessage()}";
            $this->toast('error', $this->previewError);
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
        $this->unlockBodyScroll();
    }

    public function openRestoreModal(string $filename): void
    {
        $this->restoringFile        = $filename;
        $this->restorePassword      = '';
        $this->restorePasswordError = null;
        $this->showRestoreModal     = true;
        $this->showPreviewModal     = false;
        // Keep body locked — restore modal is still open.
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
            $service = app(BackupService::class);
            $preview = $service->previewBackup($filename);
            $service->restoreBackup($filename);

            $includesSavestates = (bool) ($preview['manifest']['includes_savestates'] ?? false);
            $adminName          = auth()->user()->name;

            // Notify separately — restore already succeeded; don't leave the modal open on notify errors.
            try {
                User::query()->each(function (User $user) use ($filename, $includesSavestates, $adminName) {
                    $user->notify(new SiteDataRestored(
                        backupFilename:     $filename,
                        includesSavestates: $includesSavestates,
                        adminName:          $adminName,
                    ));
                });
            } catch (\Throwable $e) {
                report($e);
                $this->finishRestoreUi($filename, notified: false);

                return;
            }

            $this->finishRestoreUi($filename, notified: true);
        } catch (\Throwable $e) {
            report($e);
            $this->closeRestoreModal();
            $this->toast('error', "Restore failed: {$e->getMessage()}");
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
        $this->isRestoring          = false;
        $this->unlockBodyScroll();
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
        $this->unlockBodyScroll();
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
            $this->toast('success', "Backup {$filename} deleted.");
        } catch (\Throwable $e) {
            report($e);
            $this->closeDeleteModal();
            $this->toast('error', "Failed to delete: {$e->getMessage()}");
        }
    }

    public function downloadBackup(string $filename)
    {
        try {
            return app(BackupService::class)->downloadBackup($filename);
        } catch (\Throwable $e) {
            report($e);
            $this->toast('error', "Failed to download: {$e->getMessage()}");

            return null;
        }
    }

    private function finishRestoreUi(string $filename, bool $notified): void
    {
        $this->showRestoreModal     = false;
        $this->restoringFile        = null;
        $this->restorePassword      = '';
        $this->restorePasswordError = null;
        $this->isRestoring          = false;
        $this->showPreviewModal     = false;
        $this->previewingFile       = null;
        $this->previewData          = [];
        $this->previewError         = null;
        $this->isPreviewLoading     = false;
        $this->unlockBodyScroll();
        $this->loadBackups();

        if ($notified) {
            $this->toast('success', "Restore from {$filename} completed successfully.");
        } else {
            $this->toast(
                'warning',
                "Restore from {$filename} completed, but notifying users failed. Check the log."
            );
        }

        // Refresh nav so the site-restored banner can appear for this admin.
        $this->dispatch('site-data-restored');
    }

    /** Livewire often removes modal DOM without Alpine destroy — unlock explicitly. */
    private function unlockBodyScroll(): void
    {
        $this->js('document.body.style.overflow = ""');
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
