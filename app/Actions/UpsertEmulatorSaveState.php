<?php

namespace App\Actions;

use App\Models\EmulatorSaveState;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

final class UpsertEmulatorSaveState
{
    public const MAX_SLOTS = 5;

    public function execute(
        User $user,
        string $console,
        string $gameSlug,
        int $slot,
        ?string $label,
        string $binaryContents,
    ): EmulatorSaveState {
        $diskPath = $this->diskPath($user->id, $console, $gameSlug, $slot);
        $backupPath = "{$diskPath}.backup";
        $disk = Storage::disk('savestates');

        $backupMeta = [
            'backup_disk_path' => null,
            'backup_size_bytes' => null,
            'backup_checksum' => null,
            'backup_updated_at' => null,
        ];

        // Rotation: if a primary state exists, copy it to .backup (overwriting old backup),
        // then overwrite primary with the new bytes. This keeps max 2 files per slot.
        if ($disk->exists($diskPath)) {
            $disk->copy($diskPath, $backupPath);
            [$backupChecksum, $backupBytes] = $this->checksumAndSize($disk, $backupPath);

            $backupMeta = [
                'backup_disk_path' => $backupPath,
                'backup_size_bytes' => $backupBytes,
                'backup_checksum' => $backupChecksum,
                'backup_updated_at' => Carbon::now(),
            ];
        }

        $disk->put($diskPath, $binaryContents);

        $save = EmulatorSaveState::updateOrCreate(
            [
                'user_id' => $user->id,
                'console' => $console,
                'game_slug' => $gameSlug,
                'slot' => $slot,
            ],
            [
                'label' => $label,
                'disk_path' => $diskPath,
                'size_bytes' => strlen($binaryContents),
                'checksum' => hash('sha256', $binaryContents),
                ...$backupMeta,
            ],
        );

        return $save->fresh();
    }

    public function diskPath(int $userId, string $console, string $gameSlug, int $slot): string
    {
        $safeSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $gameSlug);

        return "{$userId}/{$console}/{$safeSlug}/{$safeSlug}-slot-{$slot}.state";
    }

    /**
     * @return array{string,int} [checksum, bytes]
     */
    private function checksumAndSize($disk, string $path): array
    {
        $stream = $disk->readStream($path);
        if (! is_resource($stream)) {
            $contents = (string) $disk->get($path);

            return [hash('sha256', $contents), strlen($contents)];
        }

        $hash = hash_init('sha256');
        $bytes = 0;

        while (! feof($stream)) {
            $chunk = fread($stream, 1024 * 1024);
            if ($chunk === false) {
                break;
            }

            $bytes += strlen($chunk);
            hash_update($hash, $chunk);
        }

        fclose($stream);

        return [hash_final($hash), $bytes];
    }
}
