<?php

use App\Support\BrowserLabel;

describe('BrowserLabel', function () {
    it('maps chrome user agents', function () {
        expect(BrowserLabel::icon('Mozilla/5.0 Chrome/120.0.0.0 Safari/537.36'))
            ->toBe('browser-chrome');
    });

    it('maps edge before chrome', function () {
        expect(BrowserLabel::icon('Mozilla/5.0 Chrome/120.0.0.0 Edg/120.0.0.0'))
            ->toBe('browser-edge');
    });

    it('maps firefox', function () {
        expect(BrowserLabel::icon('Mozilla/5.0 Firefox/121.0'))
            ->toBe('browser-firefox');
    });

    it('maps safari without chrome', function () {
        expect(BrowserLabel::icon('Mozilla/5.0 Version/17.0 Safari/605.1.15'))
            ->toBe('browser-safari');
    });

    it('falls back to computer for unknown agents', function () {
        expect(BrowserLabel::icon(null))->toBe('computer')
            ->and(BrowserLabel::icon(''))->toBe('computer')
            ->and(BrowserLabel::icon('SomeCustomBot/1.0'))->toBe('computer');
    });
});
