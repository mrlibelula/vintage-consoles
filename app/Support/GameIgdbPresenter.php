<?php

namespace App\Support;

use App\Models\Game;
use App\Services\Igdb\IgdbImage;
use Illuminate\Support\Collection;

class GameIgdbPresenter
{
    /**
     * @return array{
     *   videos: array<int, array{title: string, youtube_id: string, source: string, thumb: string}>,
     *   artworks: array<int, array{thumb: string, full: string}>,
     *   similar_games: array<int, array{title: string, url: string, poster: string|null, console: string}>,
     *   has_videos: bool,
     *   has_media: bool
     * }
     */
    public function present(Game $game, ?Collection $localByIgdbId = null): array
    {
        $payload = is_array($game->igdb_response) ? $game->igdb_response : [];
        $localByIgdbId ??= collect();

        $videos = $this->videos($game, $payload);
        $artworks = $this->artworks($payload);
        $similar = $this->similarGames($payload, $localByIgdbId);

        return [
            'videos' => $videos,
            'artworks' => $artworks,
            'similar_games' => $similar,
            'has_videos' => $videos !== [],
            // Media tab: videos, artworks, and/or similar games.
            'has_media' => $videos !== [] || $artworks !== [] || $similar !== [],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{title: string, youtube_id: string, source: string, thumb: string}>
     */
    private function videos(Game $game, array $payload): array
    {
        $merged = [];
        $seen = [];

        $walkthroughs = is_array($game->walkthrough_videos) ? $game->walkthrough_videos : [];
        foreach (YouTubeUrl::normalizeWalkthroughRows($walkthroughs) as $row) {
            $id = $row['youtube_id'];
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $merged[] = [
                'title' => $row['title'],
                'youtube_id' => $id,
                'source' => 'walkthrough',
                'thumb' => YouTubeUrl::thumbnailUrl($id),
            ];
        }

        foreach ($payload['videos'] ?? [] as $video) {
            if (! is_array($video)) {
                continue;
            }
            $id = YouTubeUrl::extractId((string) ($video['video_id'] ?? ''));
            if ($id === null || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $title = trim((string) ($video['name'] ?? ''));
            if ($title === '') {
                $title = 'Trailer';
            }
            $merged[] = [
                'title' => $title,
                'youtube_id' => $id,
                'source' => 'igdb',
                'thumb' => YouTubeUrl::thumbnailUrl($id),
            ];
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{thumb: string, full: string}>
     */
    private function artworks(array $payload): array
    {
        $out = [];
        foreach ($payload['artworks'] ?? [] as $art) {
            if (! is_array($art)) {
                continue;
            }
            $imageId = (string) ($art['image_id'] ?? '');
            if ($imageId === '') {
                continue;
            }
            $out[] = [
                'thumb' => IgdbImage::url($imageId, IgdbImage::SCREENSHOT_MED, 'webp'),
                'full' => IgdbImage::url($imageId, IgdbImage::HD_1080P, 'webp'),
            ];
        }

        return $out;
    }

    /**
     * Only games that exist in our catalog (matched by igdb_id).
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, array{title: string, url: string, poster: string|null, console: string}>
     */
    private function similarGames(array $payload, Collection $localByIgdbId): array
    {
        $out = [];

        foreach ($payload['similar_games'] ?? [] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $igdbId = isset($item['id']) ? (int) $item['id'] : 0;
            if ($igdbId <= 0) {
                continue;
            }

            /** @var Game|null $local */
            $local = $localByIgdbId->get($igdbId);
            if (! $local || ! $local->console) {
                continue;
            }

            $out[] = [
                'title' => $local->title,
                'url' => route('play', [$local->console->short_name, $local->slug]),
                'poster' => $local->coverUrl(IgdbImage::COVER_BIG),
                'console' => strtoupper((string) $local->console->short_name),
            ];
        }

        return array_slice($out, 0, 12);
    }
}
