<?php

use App\Services\Igdb\IgdbImage;

describe('IgdbImage', function () {

    it('generates a cover_big webp URL by default', function () {
        $url = IgdbImage::url('co8lo8');

        expect($url)
            ->toBe('https://images.igdb.com/igdb/image/upload/t_cover_big/co8lo8.webp');
    });

    it('supports all defined presets', function () {
        $presets = [
            IgdbImage::THUMB,
            IgdbImage::COVER_SMALL,
            IgdbImage::COVER_BIG,
            IgdbImage::SCREENSHOT_MED,
            IgdbImage::SCREENSHOT_BIG,
            IgdbImage::SCREENSHOT_HUGE,
            IgdbImage::HD_1080P,
            IgdbImage::ORIGINAL,
        ];

        foreach ($presets as $preset) {
            $url = IgdbImage::url('co8lo8', $preset);
            expect($url)
                ->toContain("/{$preset}/")
                ->toContain('co8lo8.webp');
        }
    });

    it('allows overriding the extension', function () {
        $url = IgdbImage::url('co8lo8', IgdbImage::COVER_BIG, 'jpg');

        expect($url)->toEndWith('co8lo8.jpg');
    });

    it('thumb() helper generates a t_cover_small webp URL', function () {
        $url = IgdbImage::thumb('co8lo8');

        expect($url)
            ->toBe('https://images.igdb.com/igdb/image/upload/t_cover_small/co8lo8.webp');
    });

    it('screenshotThumb() helper generates a t_screenshot_big webp URL', function () {
        $url = IgdbImage::screenshotThumb('co8lo8');

        expect($url)
            ->toBe('https://images.igdb.com/igdb/image/upload/t_screenshot_big/co8lo8.webp');
    });

    it('fullScreenshot() helper generates a t_original webp URL', function () {
        $url = IgdbImage::fullScreenshot('abc123');

        expect($url)
            ->toBe('https://images.igdb.com/igdb/image/upload/t_original/abc123.webp');
    });

    it('encodes special characters in the image ID', function () {
        $url = IgdbImage::url('some-image-id');

        expect($url)->toContain('some-image-id');
    });
});
