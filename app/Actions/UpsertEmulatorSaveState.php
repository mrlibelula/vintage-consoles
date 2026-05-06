<?php

namespace App\Actions;

use App\Models\EmulatorSaveState;
use App\Models\User;
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

        Storage::disk('savestates')->put($diskPath, $binaryContents);

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
            ],
        );

        return $save->fresh();
    }

    public function diskPath(int $userId, string $console, string $gameSlug, int $slot): string
    {
        $safeSlug = preg_replace('/[^A-Za-z0-9_-]/', '_', $gameSlug);

        return "{$userId}/{$console}/{$safeSlug}/{$safeSlug}-slot-{$slot}.state";
    }
}
