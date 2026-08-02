<?php

use App\Support\YouTubeUrl;

it('extracts youtube ids from common url formats', function (string $input, string $expected) {
    expect(YouTubeUrl::extractId($input))->toBe($expected);
})->with([
    'raw id' => ['dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'watch url' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'short url' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'embed url' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
    'shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
]);

it('returns null for invalid youtube values', function () {
    expect(YouTubeUrl::extractId('not-a-video!!'))->toBeNull()
        ->and(YouTubeUrl::extractId('short'))->toBeNull()
        ->and(YouTubeUrl::extractId(''))->toBeNull()
        ->and(YouTubeUrl::extractId(null))->toBeNull();
});

it('normalizes walkthrough rows and skips invalid ones', function () {
    $rows = YouTubeUrl::normalizeWalkthroughRows([
        ['title' => 'Boss guide', 'youtube_id' => 'https://youtu.be/dQw4w9WgXcQ'],
        ['title' => '', 'url' => 'aaaaaaaaaaa'],
        ['title' => 'Bad', 'youtube_id' => 'nope'],
        'ignore',
    ]);

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe(['title' => 'Boss guide', 'youtube_id' => 'dQw4w9WgXcQ'])
        ->and($rows[1]['title'])->toBe('Walkthrough')
        ->and($rows[1]['youtube_id'])->toBe('aaaaaaaaaaa');
});
