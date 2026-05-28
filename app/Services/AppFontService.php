<?php

namespace App\Services;

use App\Models\AppFont;
use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class AppFontService
{
    public const ACTIVE_FONT_SETTING_KEY = 'active_app_font_id';

    private const ALLOWED_EXTENSIONS = ['ttf', 'otf', 'woff', 'woff2'];

    private const MAX_UPLOAD_KILOBYTES = 5120;

    private ?AppFont $resolvedActiveFont = null;

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return AppFont::query()
            ->orderByDesc('is_bundled')
            ->orderBy('label')
            ->get();
    }

    public function active(): AppFont
    {
        if ($this->resolvedActiveFont instanceof AppFont) {
            return $this->resolvedActiveFont;
        }

        $this->resolvedActiveFont = Cache::remember('app_font.active', now()->addHour(), function () {
            $activeId = AppSetting::query()
                ->where('key', self::ACTIVE_FONT_SETTING_KEY)
                ->value('value');

            $font = $activeId
                ? AppFont::query()->find($activeId)
                : null;

            if (! $font || ! $this->fontFileExists($font)) {
                $font = AppFont::query()
                    ->where('family_name', 'VT323')
                    ->first();
            }

            if (! $font) {
                throw new RuntimeException('No application font is configured.');
            }

            return $font;
        });

        return $this->resolvedActiveFont;
    }

    public function activate(AppFont $font): void
    {
        if (! $this->fontFileExists($font)) {
            throw new RuntimeException('The selected font file is missing.');
        }

        AppSetting::query()->updateOrCreate(
            ['key' => self::ACTIVE_FONT_SETTING_KEY],
            ['value' => (string) $font->id],
        );

        $this->forgetActiveCache();
    }

    public function install(UploadedFile $file, string $label, string $familyName): AppFont
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Only .ttf, .otf, .woff, and .woff2 files are allowed.');
        }

        if ($file->getSize() > self::MAX_UPLOAD_KILOBYTES * 1024) {
            throw new InvalidArgumentException('Font files must be 5 MB or smaller.');
        }

        $slug = Str::slug($familyName);

        if ($slug === '') {
            throw new InvalidArgumentException('A valid font family name is required.');
        }

        $relativePath = 'uploads/'.$slug.'.'.$extension;

        if (Storage::disk('fonts')->exists($relativePath)) {
            throw new InvalidArgumentException('A font with this family name already exists.');
        }

        $file->storeAs('uploads', $slug.'.'.$extension, 'fonts');

        return AppFont::query()->create([
            'label' => trim($label),
            'family_name' => trim($familyName),
            'relative_path' => $relativePath,
            'format' => $extension,
            'is_bundled' => false,
        ]);
    }

    public function delete(AppFont $font): void
    {
        if ($font->is_bundled) {
            throw new InvalidArgumentException('Bundled fonts cannot be deleted.');
        }

        if ($font->id === $this->active()->id) {
            throw new InvalidArgumentException('The active font cannot be deleted.');
        }

        if (Storage::disk('fonts')->exists($font->relative_path)) {
            Storage::disk('fonts')->delete($font->relative_path);
        }

        $font->delete();
    }

    public function publicUrl(AppFont $font): string
    {
        return asset('fonts/'.$font->relative_path);
    }

    public function mimeType(AppFont $font): string
    {
        return match ($font->format) {
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            default => 'font/ttf',
        };
    }

    public function cssFormat(AppFont $font): string
    {
        return match ($font->format) {
            'ttf' => 'truetype',
            'otf' => 'opentype',
            'woff' => 'woff',
            'woff2' => 'woff2',
            default => 'truetype',
        };
    }

    public function cssFamily(AppFont $font): string
    {
        return "'".str_replace("'", "\\'", $font->family_name)."', monospace";
    }

    public function guessFamilyName(string $filename): string
    {
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $family = preg_replace('/[-_](regular|bold|italic|medium|light|thin|black)$/i', '', $basename) ?? $basename;

        return trim($family) !== '' ? trim($family) : 'CustomFont';
    }

    public function forgetActiveCache(): void
    {
        Cache::forget('app_font.active');
        $this->resolvedActiveFont = null;
    }

    private function fontFileExists(AppFont $font): bool
    {
        return Storage::disk('fonts')->exists($font->relative_path);
    }
}
