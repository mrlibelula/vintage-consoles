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
     *   {userId}/{console}/{gameId}/{emulator}/slot-{slot}.state
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

            [$userId, $console, $gameId, $emulator, $slot] = $parsed;

            if ((int) $userId !== (int) $user->id) {
                continue;
            }

            if (! in_array($emulator, ['emulatorjs', 'jsdos'], true)) {
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
                    'game_id' => $gameId,
                    'emulator' => $emulator,
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
     * @return array{string,string,string,string,int}|null
     */
    private function parseDiskPath(string $path): ?array
    {
        $normalized = str_replace('\\', '/', $path);

        if (! preg_match('#^([^/]+)/([^/]+)/([^/]+)/([^/]+)/slot-(\d+)\.state$#i', $normalized, $matches)) {
            return null;
        }

        return [
            $matches[1],
            strtolower($matches[2]),
            (string) $matches[3],
            strtolower($matches[4]),
            (int) $matches[5],
        ];
    }

    /**
     * @return array{string,int} [checksum, bytes]
     */
    private function checksumAndSize($disk, string $path): array
    {
        $stream = $disk->readStream($path);
        if (! is_resource($stream)) {
            // Fall back to a full read if a stream isn't available.
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

