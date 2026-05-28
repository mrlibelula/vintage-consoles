<?php

namespace App\Services\Igdb;

class IgdbImage
{
    const THUMB = 't_thumb';
    const COVER_SMALL = 't_cover_small';
    const COVER_BIG = 't_cover_big';
    const SCREENSHOT_MED = 't_screenshot_med';
    const SCREENSHOT_BIG = 't_screenshot_big';
    const SCREENSHOT_HUGE = 't_screenshot_huge';
    const HD_1080P = 't_1080p';
    const ORIGINAL = 't_original';

    private const BASE = 'https://images.igdb.com/igdb/image/upload';

    private const VALID_PRESETS = [
        self::THUMB,
        self::COVER_SMALL,
        self::COVER_BIG,
        self::SCREENSHOT_MED,
        self::SCREENSHOT_BIG,
        self::SCREENSHOT_HUGE,
        self::HD_1080P,
        self::ORIGINAL,
    ];

    /**
     * Build a canonical IGDB image URL.
     *
     * @param  string  $imageId   The image_id returned by IGDB (e.g. "co8lo8")
     * @param  string  $preset    One of the t_* preset constants
     * @param  string  $ext       File extension: webp (default), jpg, png
     */
    public static function url(
        string $imageId,
        string $preset = self::COVER_BIG,
        string $ext = 'webp'
    ): string {
        if (! in_array($preset, self::VALID_PRESETS, true)) {
            $preset = self::COVER_BIG;
        }

        return sprintf('%s/%s/%s.%s', self::BASE, $preset, $imageId, $ext);
    }

    /**
     * Convenience: cover thumbnail URL (t_cover_small, webp).
     */
    public static function thumb(string $imageId, string $ext = 'webp'): string
    {
        return self::url($imageId, self::COVER_SMALL, $ext);
    }

    /**
     * Convenience: screenshot thumbnail URL (t_screenshot_big, webp).
     */
    public static function screenshotThumb(string $imageId, string $ext = 'webp'): string
    {
        return self::url($imageId, self::SCREENSHOT_BIG, $ext);
    }

    /**
     * Convenience: full screenshot URL (t_original, webp).
     */
    public static function fullScreenshot(string $imageId, string $ext = 'webp'): string
    {
        return self::url($imageId, self::ORIGINAL, $ext);
    }
}
