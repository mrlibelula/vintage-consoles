<?php

namespace App\Services;

use App\Models\Console;
use App\Models\Game;
use App\Models\Genre;
use App\Services\Igdb\GameImporter;
use App\Services\Igdb\IgdbClient;
use App\Services\Igdb\IgdbImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class GameRepository
{
    /**
     * Return all consoles (no games eager-loaded — use for nav/tabs).
     *
     * @return Collection<Console>
     */
    public function getConsoles(): Collection
    {
        return Console::orderBy('id')->get();
    }

    /**
     * Return one console with its games, genres, and screenshots eager-loaded.
     */
    public function getConsole(string $shortName): ?Console
    {
        return Console::where('short_name', $shortName)
            ->with(['games' => function ($q) {
                $q->with(['genres', 'screenshots' => fn ($q2) => $q2->orderBy('position')])
                  ->orderBy('title');
            }])
            ->first();
    }

    /**
     * Return a console with games pre-loaded (and their relations).
     */
    public function getConsoleWithGames(string $shortName): ?Console
    {
        return $this->getConsole($shortName);
    }

    /**
     * Return all games for a console short_name, with relations.
     *
     * @return Collection<Game>
     */
    public function getGamesByConsole(string $shortName): Collection
    {
        $console = Console::where('short_name', $shortName)->first();
        if (! $console) {
            return new Collection();
        }

        return Game::where('console_id', $console->id)
            ->with(['genres', 'screenshots' => fn ($q) => $q->orderBy('position'), 'console'])
            ->orderBy('title')
            ->get();
    }

    /**
     * Return a single game by console short_name + game id.
     */
    public function getGame(string $shortName, int $gameId): ?Game
    {
        $console = Console::where('short_name', $shortName)->first();
        if (! $console) {
            return null;
        }

        return Game::where('console_id', $console->id)
            ->where('id', $gameId)
            ->with(['genres', 'screenshots' => fn ($q) => $q->orderBy('position'), 'console'])
            ->first();
    }

    /**
     * Return a single game by console short_name + slug.
     */
    public function getGameBySlug(string $shortName, string $slug): ?Game
    {
        $console = Console::where('short_name', $shortName)->first();
        if (! $console) {
            return null;
        }

        return Game::where('console_id', $console->id)
            ->where('slug', $slug)
            ->with(['genres', 'screenshots' => fn ($q) => $q->orderBy('position'), 'console'])
            ->first();
    }

    /**
     * Add a new game to a console.
     */
    public function addGame(string $consoleShortName, array $gameData): Game|false
    {
        $console = Console::where('short_name', $consoleShortName)->first();
        if (! $console) {
            return false;
        }

        $title = $gameData['title'] ?? '';
        $slug  = Str::slug($title);

        // Ensure unique slug within the console
        $base = $slug;
        $i    = 1;
        while (Game::where('console_id', $console->id)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $game = Game::create([
            'console_id'          => $console->id,
            'igdb_id'             => $gameData['igdb_id'] ?? null,
            'title'               => $title,
            'slug'                => $slug,
            'publisher'           => $gameData['publisher'] ?? null,
            'release_year'        => isset($gameData['release_year']) ? (string) $gameData['release_year'] : null,
            'description'         => $gameData['description'] ?? null,
            'rating'              => isset($gameData['rating']) ? (float) $gameData['rating'] : null,
            'multiplayer_support' => (bool) ($gameData['multiplayer_support'] ?? false),
            'save_state_support'  => (bool) ($gameData['save_state_support'] ?? true),
            'is_free'             => (bool) ($gameData['is_free'] ?? true),
            'rom'                 => $gameData['rom'] ?? null,
            'poster'              => $gameData['poster'] ?? null,
            'cover_image_id'      => $gameData['cover_image_id'] ?? null,
            'game_preview'        => $gameData['game_preview'] ?? null,
            'cartridge'           => $gameData['cartridge'] ?? null,
            'needs_igdb_sync'     => (bool) ($gameData['needs_igdb_sync'] ?? true),
            'igdb_response'       => $gameData['igdb_response'] ?? null,
            'walkthrough_videos'  => $gameData['walkthrough_videos'] ?? null,
        ]);

        $this->syncGenres($game, $gameData['genres'] ?? []);

        return $game;
    }

    /**
     * Update an existing game by ID.
     */
    public function updateGame(string $consoleShortName, int $gameId, array $gameData): bool
    {
        $console = Console::where('short_name', $consoleShortName)->first();
        if (! $console) {
            return false;
        }

        $game = Game::where('console_id', $console->id)->where('id', $gameId)->first();
        if (! $game) {
            return false;
        }

        // Update slug if title changed
        if (isset($gameData['title']) && $gameData['title'] !== $game->title) {
            $slug = Str::slug($gameData['title']);
            $base = $slug;
            $i    = 1;
            while (
                Game::where('console_id', $console->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $gameId)
                    ->exists()
            ) {
                $slug = $base . '-' . $i++;
            }
            $gameData['slug'] = $slug;
        }

        // Handle cross-console move
        if (isset($gameData['console_id'])) {
            $targetConsole = Console::where('short_name', $gameData['console_short_name'] ?? '')->first();
            if ($targetConsole && $targetConsole->id !== $console->id) {
                $gameData['console_id'] = $targetConsole->id;
            }
        }

        $game->fill(array_intersect_key($gameData, array_flip($game->getFillable())));
        $game->save();

        if (array_key_exists('genres', $gameData)) {
            $this->syncGenres($game, $gameData['genres']);
        }

        if (array_key_exists('igdb_response', $gameData) && is_array($gameData['igdb_response'])) {
            $this->syncScreenshotsFromIgdb($game, $gameData['igdb_response']);
        }

        return true;
    }

    /**
     * Delete a game by console short_name + game ID.
     */
    public function deleteGame(string $consoleShortName, int $gameId): bool
    {
        $console = Console::where('short_name', $consoleShortName)->first();
        if (! $console) {
            return false;
        }

        return (bool) Game::where('console_id', $console->id)
            ->where('id', $gameId)
            ->delete();
    }

    /**
     * Return all unique genres across all games, sorted by game count desc.
     *
     * @return Collection<Genre>  Each Genre has an appended `games_count` attribute.
     */
    public function getAllGenres(): Collection
    {
        return Genre::withCount('games')
            ->orderByDesc('games_count')
            ->get();
    }

    /**
     * Return all unique publishers with game counts, sorted alphabetically.
     * Each item: ['name' => string, 'games_count' => int]
     *
     * @return array<array{name: string, games_count: int}>
     */
    public function getAllPublishers(): array
    {
        return Game::whereNotNull('publisher')
            ->selectRaw('publisher, COUNT(*) as games_count')
            ->groupBy('publisher')
            ->orderBy('publisher')
            ->get()
            ->map(fn ($row) => ['name' => $row->publisher, 'games_count' => (int) $row->games_count])
            ->toArray();
    }

    /**
     * Return all games that belong to a given genre slug, with relations.
     *
     * @return Collection<Game>
     */
    public function getGamesByGenre(string $genreSlug): Collection
    {
        $genre = Genre::where('name', $genreSlug)->first();
        if (! $genre) {
            return new Collection();
        }

        return $genre->games()
            ->with(['genres', 'screenshots' => fn ($q) => $q->orderBy('position'), 'console'])
            ->orderBy('title')
            ->get();
    }

    /**
     * Return all games by a given publisher string, with relations.
     *
     * @return Collection<Game>
     */
    public function getGamesByPublisher(string $publisher): Collection
    {
        return Game::where('publisher', $publisher)
            ->with(['genres', 'screenshots' => fn ($q) => $q->orderBy('position'), 'console'])
            ->orderBy('title')
            ->get();
    }

    /**
     * Search games by title (for the navigation search bar).
     *
     * @return Collection<Game>
     */
    public function searchGames(string $query, int $limit = 10): Collection
    {
        return Game::where('title', 'LIKE', '%' . $query . '%')
            ->with(['console'])
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    // -------------------------------------------------------------------------
    // Sorting
    // -------------------------------------------------------------------------

    /**
     * Sort an in-memory game collection for carousel views.
     *
     * @param  Collection<int, Game>  $games
     * @return Collection<int, Game>
     */
    public function sortGamesCollection(\Illuminate\Support\Collection $games, string $field, string $direction): \Illuminate\Support\Collection
    {
        if (! in_array($field, ['title', 'rating'], true)) {
            $field = 'title';
        }

        $desc = strtolower($direction) === 'desc';

        return $games->sortBy(
            fn (Game $game) => $field === 'rating'
                ? (float) ($game->rating ?? 0)
                : strtolower((string) ($game->title ?? '')),
            SORT_REGULAR,
            $desc
        )->values();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function syncGenres(Game $game, array $genresData): void
    {
        if (empty($genresData)) {
            $game->genres()->detach();
            return;
        }

        $genreIds = [];
        foreach ($genresData as $genreData) {
            if (is_string($genreData)) {
                $name = Str::slug($genreData);
            } elseif (is_array($genreData)) {
                $name = Str::slug($genreData['name'] ?? '');
            } else {
                continue;
            }
            if ($name === '') {
                continue;
            }
            $genre = Genre::firstOrCreate(
                ['name' => $name],
                ['description' => is_array($genreData) ? ($genreData['description'] ?? null) : null]
            );
            $genreIds[] = $genre->id;
        }

        $game->genres()->sync($genreIds);
    }

    private function syncScreenshotsFromIgdb(Game $game, array $igdbPayload): void
    {
        $screenshots = $igdbPayload['screenshots'] ?? [];
        if (empty($screenshots)) {
            return;
        }

        $game->screenshots()->delete();

        $rows = [];
        foreach ($screenshots as $index => $shot) {
            if (! is_array($shot) || empty($shot['image_id'])) {
                continue;
            }
            $imageId = $shot['image_id'];
            $rows[] = [
                'game_id'       => $game->id,
                'igdb_image_id' => $imageId,
                'thumb_url'     => IgdbImage::screenshotThumb($imageId),
                'full_url'      => IgdbImage::fullScreenshot($imageId),
                'position'      => $index,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (! empty($rows)) {
            \App\Models\Screenshot::insert($rows);
        }
    }
}
