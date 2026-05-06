<?php

namespace App\Actions;

use App\Models\EmulatorSaveState;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class SyncEmulatorSaveStatesFromDisk
{
    /**
     * Scan `storage/app/savestates/{userId}/.../*.state` and upsert missing DB rows.
     *
     * Expected layout:
     *   {userId}/{console}/{gameSlug}/{gameSlug}-slot-{slot}.state
     */
    public function execute(User $user): int
    {
        $disk = Storage::disk('savestates');
        $prefix = "{$user->id}/";

        $createdOrUpdated = 0;

        foreach ($disk->allFiles($prefix) as $path) {
            if (! str_ends_with(strtolower($path), '.state')) {
                continue;
            }

            $parsed = $this->parseDiskPath($path);
            if (! $parsed) {
                continue;
            }

            [$userId, $console, $gameSlug, $slot] = $parsed;

            if ((int) $userId !== (int) $user->id) {
                continue;
            }

            if ($slot < 1 || $slot > UpsertEmulatorSaveState::MAX_SLOTS) {
                continue;
            }

            if (! $disk->exists($path)) {
                continue;
            }

            [$checksum, $bytes] = $this->checksumAndSize($disk, $path);

            $save = EmulatorSaveState::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'console' => $console,
                    'game_slug' => $gameSlug,
                    'slot' => $slot,
                ],
                [
                    'disk_path' => $path,
                    'size_bytes' => $bytes,
                    'checksum' => $checksum,
                ],
            );

            if ($save->wasRecentlyCreated || $save->wasChanged()) {
                $createdOrUpdated++;
            }
        }

        return $createdOrUpdated;
    }

    /**
     * @return array{string,string,string,int}|null
     */
    private function parseDiskPath(string $path): ?array
    {
        $normalized = str_replace('\\', '/', $path);

        // {userId}/{console}/{gameSlug}/{gameSlug}-slot-{n}.state
        if (! preg_match('#^([^/]+)/([^/]+)/([^/]+)/[^/]+-slot-(\d+)\.state$#i', $normalized, $matches)) {
            return null;
        }

        return [
            $matches[1],
            strtolower($matches[2]),
            (string) $matches[3],
            (int) $matches[4],
        ];
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
