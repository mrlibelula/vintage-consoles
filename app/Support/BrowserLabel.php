<?php

namespace App\Support;

class BrowserLabel
{
    /**
     * Map a User-Agent string to a pixelarticon name.
     */
    public static function icon(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);

        if ($ua === '') {
            return 'computer';
        }

        // Order matters: Edge/Opera contain "chrome"; Chrome check after them.
        if (str_contains($ua, 'edg/') || str_contains($ua, 'edgios') || str_contains($ua, 'edga')) {
            return 'browser-edge';
        }

        if (str_contains($ua, 'firefox/') || str_contains($ua, 'fxios')) {
            return 'browser-firefox';
        }

        if (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome') && ! str_contains($ua, 'chromium')) {
            return 'browser-safari';
        }

        if (str_contains($ua, 'chrome/') || str_contains($ua, 'crios') || str_contains($ua, 'chromium')) {
            return 'browser-chrome';
        }

        return 'computer';
    }
}
