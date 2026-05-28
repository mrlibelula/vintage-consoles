<?php

namespace App\Services\Igdb;

use App\Models\Console;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Screenshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GameImporter
{
    /**
     * Persist a single IGDB game payload into the database.
     *
     * Fields that IGDB does NOT own (rom, game_preview, cartridge, etc.)
     * should be passed via $localData so they are merged into the row.
     *
     * Runs inside its own DB transaction; the caller may wrap multiple
     * imports in a single outer transaction.
     *
     * @param  array<string, mixed>  $igdbPayload  Raw IGDB API response for one game
     * @param  Console               $console       The target Console model
     * @param  array<string, mixed>  $localData     Fields from JSON (rom, game_preview, slug, etc.)
     * @return Game
     */
    public function import(array $igdbPayload, Console $console, array $localData = []): Game
    {
        return $this->transaction(function () use ($igdbPayload, $console, $localData): Game {
            $title = $igdbPayload['name'] ?? ($localData['title'] ?? 'Unknown');
            $slug  = $localData['slug'] ?? Str::slug($title);

            $rating = null;
            if (isset($igdbPayload['total_rating'])) {
                $rating = round((float) $igdbPayload['total_rating'] / 100, 4);
                $rating = max(0, min(1, $rating));
            }

            $releaseYear = null;
            if (isset($igdbPayload['first_release_date'])) {
                $releaseYear = (string) date('Y', (int) $igdbPayload['first_release_date']);
            }

            $publisher = $this->extractPublisher($igdbPayload);

            $poster = null;
            $coverImageId = null;
            $cover = $igdbPayload['cover'] ?? null;
            if (is_array($cover) && ! empty($cover['image_id'])) {
                $coverImageId = $cover['image_id'];
                $poster = IgdbImage::url($coverImageId, IgdbImage::COVER_BIG, 'webp');
            }

            $game = Game::updateOrCreate(
                ['console_id' => $console->id, 'slug' => $slug],
                array_merge([
                    'igdb_id'             => $igdbPayload['id'] ?? null,
                    'title'               => $title,
                    'slug'                => $slug,
                    'publisher'           => $publisher,
                    'release_year'        => $releaseYear,
                    'description'         => $igdbPayload['summary'] ?? null,
                    'rating'              => $rating,
                    'poster'              => $poster,
                    'cover_image_id'      => $coverImageId,
                    'needs_igdb_sync'     => false,
                    'igdb_response'       => $igdbPayload,
                    // local-only fields (IGDB doesn't provide these)
                    'rom'                 => $localData['rom'] ?? null,
                    'game_preview'        => $localData['game_preview'] ?? ($localData['box'] ?? null),
                    'cartridge'           => $localData['cartridge'] ?? null,
                    'multiplayer_support' => $localData['multiplayer_support'] ?? false,
                    'save_state_support'  => $localData['save_state_support'] ?? true,
                    'is_free'             => $localData['is_free'] ?? true,
                ], array_filter([
                    'console_id' => $console->id,
                ]))
            );

            $this->syncGenres($game, $igdbPayload);
            $this->syncScreenshots($game, $igdbPayload);

            return $game;
        });
    }

    /**
     * Insert a game row from JSON data only (no IGDB match found).
     * Screenshots and poster are intentionally left empty.
     */
    public function importFromJson(array $jsonGame, Console $console): Game
    {
        return $this->transaction(function () use ($jsonGame, $console): Game {
            $title = $jsonGame['title'] ?? 'Unknown';
            $slug  = $jsonGame['slug'] ?? Str::slug($title);

            $game = Game::updateOrCreate(
                ['console_id' => $console->id, 'slug' => $slug],
                [
                    'igdb_id'             => null,
                    'title'               => $title,
                    'slug'                => $slug,
                    'publisher'           => $jsonGame['publisher'] ?? null,
                    'release_year'        => isset($jsonGame['release_year'])
                                                ? (string) $jsonGame['release_year']
                                                : null,
                    'description'         => $jsonGame['description'] ?? null,
                    'rating'              => isset($jsonGame['rating'])
                                                ? max(0, min(1, (float) $jsonGame['rating']))
                                                : null,
                    'poster'              => null,
                    'cover_image_id'      => null,
                    'rom'                 => $jsonGame['rom'] ?? null,
                    'game_preview'        => $jsonGame['box'] ?? null,
                    'cartridge'           => $jsonGame['cartridge'] ?? null,
                    'multiplayer_support' => (bool) ($jsonGame['multiplayer_support'] ?? false),
                    'save_state_support'  => (bool) ($jsonGame['save_state_support'] ?? true),
                    'is_free'             => (bool) ($jsonGame['is_free'] ?? true),
                    'needs_igdb_sync'     => true,
                    'igdb_response'       => null,
                ]
            );

            // Attach genres from JSON (name slug + description)
            if (! empty($jsonGame['genres'])) {
                $genreIds = [];
                foreach ($jsonGame['genres'] as $genreData) {
                    $name = Str::slug($genreData['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $genre = Genre::firstOrCreate(
                        ['name' => $name],
                        ['description' => $genreData['description'] ?? null, 'igdb_id' => null]
                    );
                    $genreIds[] = $genre->id;
                }
                $game->genres()->sync($genreIds);
            }

            return $game;
        });
    }

    private function transaction(callable $callback): mixed
    {
        if (DB::transactionLevel() > 0) {
            return $callback();
        }

        return DB::transaction($callback);
    }

    private function extractPublisher(array $payload): ?string
    {
        $companies = $payload['involved_companies'] ?? [];
        if (! is_array($companies)) {
            return null;
        }

        foreach ($companies as $ic) {
            if (! is_array($ic)) {
                continue;
            }
            if (! empty($ic['publisher']) && ! empty($ic['company']['name'])) {
                return $ic['company']['name'];
            }
        }

        // Fallback to first developer
        foreach ($companies as $ic) {
            if (! is_array($ic)) {
                continue;
            }
            if (! empty($ic['developer']) && ! empty($ic['company']['name'])) {
                return $ic['company']['name'];
            }
        }

        return null;
    }

    private function syncGenres(Game $game, array $payload): void
    {
        $genres = $payload['genres'] ?? [];
        if (! is_array($genres) || empty($genres)) {
            return;
        }

        $genreIds = [];
        foreach ($genres as $genreData) {
            if (! is_array($genreData) || empty($genreData['name'])) {
                continue;
            }
            $name = Str::slug((string) $genreData['name']);
            if ($name === '') {
                continue;
            }
            $genre = Genre::firstOrCreate(
                ['name' => $name],
                [
                    'description' => null,
                    'igdb_id'     => $genreData['id'] ?? null,
                ]
            );
            $genreIds[] = $genre->id;
        }

        $game->genres()->sync($genreIds);
    }

    private function syncScreenshots(Game $game, array $payload): void
    {
        $screenshots = $payload['screenshots'] ?? [];
        if (! is_array($screenshots) || empty($screenshots)) {
            return;
        }

        // Remove old screenshots before re-inserting
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
            Screenshot::insert($rows);
        }
    }
}
