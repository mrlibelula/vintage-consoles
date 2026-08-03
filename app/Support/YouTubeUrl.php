<?php

namespace App\Support;

class YouTubeUrl
{
    /**
     * Extract a YouTube video ID from a URL or raw ID string.
     */
    public static function extractId(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value) === 1) {
            return $value;
        }

        $patterns = [
            '/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/',
            '/youtube\.com\/watch\?.*\bv=([A-Za-z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Normalize admin walkthrough rows into [{ title, youtube_id }, ...].
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{title: string, youtube_id: string}>
     */
    public static function normalizeWalkthroughRows(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $raw = (string) ($row['youtube_id'] ?? $row['url'] ?? '');
            $id = self::extractId($raw);
            if ($id === null) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                $title = 'Walkthrough';
            }

            $out[] = [
                'title' => $title,
                'youtube_id' => $id,
            ];
        }

        return array_values($out);
    }

    public static function thumbnailUrl(string $youtubeId, string $quality = 'hqdefault'): string
    {
        return "https://i.ytimg.com/vi/{$youtubeId}/{$quality}.jpg";
    }

    public static function embedUrl(string $youtubeId, array $query = []): string
    {
        $defaults = [
            'enablejsapi' => '1',
            'playsinline' => '1',
            'mute' => '1',
            'rel' => '0',
            'modestbranding' => '1',
        ];

        return 'https://www.youtube.com/embed/' . $youtubeId . '?' . http_build_query(array_merge($defaults, $query));
    }
}
