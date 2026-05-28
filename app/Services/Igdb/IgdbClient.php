<?php

namespace App\Services\Igdb;

use MarcReichel\IGDBLaravel\Models\Game as IgdbGame;

class IgdbClient
{
    /**
     * Full list of relations to expand in every IGDB query.
     * This matches the apicalypse "fields" block in the original spec.
     */
    private const RELATIONS = [
        'alternative_names',
        'artworks',
        'age_ratings',
        'collections',
        'cover',
        'game_engines',
        'game_modes',
        'genres',
        'involved_companies',
        'involved_companies.company',
        'keywords',
        'multiplayer_modes',
        'platforms',
        'platforms.platform_logo',
        'player_perspectives',
        'release_dates',
        'screenshots',
        'themes',
        'videos',
        'websites',
        'language_supports',
        'language_supports.language',
        'language_supports.language_support_type',
        'similar_games',
        'remakes',
        'remasters',
    ];

    /**
     * Single-game fuzzy search scoped to one platform.
     * Used by the admin "API Fill" button.
     *
     * @return array<string, mixed>|null  Raw associative payload or null on miss.
     */
    public function fetchGameForConsole(string $title, int $igdbPlatformId): ?array
    {
        // whereIn generates `platforms = (18)` — Apicalypse "contains" syntax.
        // ->where('platforms', 18) generates `platforms = 18` (exact equality),
        // which only matches games released on that single platform with no other ports.
        $result = IgdbGame::with(self::RELATIONS)
            ->search($title)
            ->whereIn('platforms', [$igdbPlatformId])
            ->limit(1)
            ->get();

        if ($result->isEmpty()) {
            return null;
        }

        return $this->toArray($result->first());
    }

    /**
     * Batched fetch — one IGDB call per platform.
     * Returns all games whose exact name matches any of the supplied titles
     * AND belongs to the given platform.
     *
     * Used by the import command to minimise total API calls (5 calls for 5 platforms
     * instead of 70+ individual calls).
     *
     * @param  string[]  $titles
     * @return array<string, array<string, mixed>>  Keyed by lowercased game name.
     */
    public function fetchGamesBatchForPlatform(array $titles, int $igdbPlatformId): array
    {
        if (empty($titles)) {
            return [];
        }

        $results = IgdbGame::with(self::RELATIONS)
            ->whereIn('name', $titles)
            ->whereIn('platforms', [$igdbPlatformId])
            ->limit(500)
            ->get();

        $indexed = [];
        foreach ($results as $game) {
            $key = strtolower((string) ($game->name ?? ''));
            if ($key !== '') {
                $indexed[$key] = $this->toArray($game);
            }
        }

        return $indexed;
    }

    /**
     * Convert an IGDB package model instance to a plain associative array
     * so every consumer stays framework-agnostic.
     *
     * We call the model's own toArray() (which merges $attributes + $relations
     * at the top level) rather than json_encode/decode, because the public
     * $builder property creates a circular reference that makes json_encode
     * return false — collapsing every payload into an empty array.
     *
     * Carbon date fields (e.g. first_release_date) are converted back to Unix
     * timestamps so downstream code can treat them as plain integers.
     */
    private function toArray(mixed $game): array
    {
        if (is_array($game)) {
            return $game;
        }

        return $this->normalizeDates($game->toArray());
    }

    /**
     * Recursively convert Carbon instances to Unix timestamps.
     */
    private function normalizeDates(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof \Carbon\Carbon) {
                $data[$key] = $value->timestamp;
            } elseif (is_array($value)) {
                $data[$key] = $this->normalizeDates($value);
            }
        }

        return $data;
    }
}
