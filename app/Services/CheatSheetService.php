<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores/reads per-game cheat Markdown sheets on the `data` disk, one file
 * per game at cheats/{console}/{slug}/cheats.md.
 */
class CheatSheetService
{
    private const DISK = 'data';

    public function path(Game $game): string
    {
        $console = strtolower($game->console?->short_name ?? 'unknown');

        return "cheats/{$console}/{$game->slug}/cheats.md";
    }

    public function exists(Game $game): bool
    {
        return Storage::disk(self::DISK)->exists($this->path($game));
    }

    public function get(Game $game): ?string
    {
        $path = $this->path($game);

        if (! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->get($path);
    }

    public function put(Game $game, string $markdown): void
    {
        Storage::disk(self::DISK)->put($this->path($game), $markdown);
    }

    public function delete(Game $game): void
    {
        $path = $this->path($game);

        if (Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * Persist trimmed markdown for a game, deleting the file when blank.
     * This is the single write path used by the admin "Update Game" save.
     */
    public function save(Game $game, ?string $markdown): void
    {
        $trimmed = trim((string) $markdown);

        if ($trimmed === '') {
            $this->delete($game);

            return;
        }

        $this->put($game, $trimmed);
    }

    public function toHtml(string $markdown): string
    {
        // UI already shows the game title — don't repeat a leading H1 from the sheet.
        $markdown = $this->stripLeadingTitle($markdown);

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * Remove a single leading ATX H1 (`# Title`) so rendered sheets start at the cheats content.
     */
    public function stripLeadingTitle(string $markdown): string
    {
        return trim((string) preg_replace('/^\s*#\s+[^\n]+\n*/', '', $markdown, 1));
    }
}
