<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class SiteDataRestored extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $backupFilename,
        public readonly bool $includesSavestates,
        public readonly string $adminName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $saveMessage = $this->includesSavestates
            ? 'Your save games may have been replaced.'
            : 'Your save games were not changed.';

        $date = Carbon::now()->format('M j, Y');
        if (preg_match('/backup_(\d{4}-\d{2}-\d{2})/', $this->backupFilename, $m)) {
            $date = Carbon::parse($m[1])->format('M j, Y');
        }

        return [
            'type'               => 'site_data_restored',
            'message'            => "Site catalog and chat were restored from backup dated {$date}. {$saveMessage}",
            'backup_filename'    => $this->backupFilename,
            'includes_savestates' => $this->includesSavestates,
            'admin_name'         => $this->adminName,
            'restored_at'        => now()->toIso8601String(),
        ];
    }
}
