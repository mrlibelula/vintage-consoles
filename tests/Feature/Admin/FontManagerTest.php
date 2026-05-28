<?php

use App\Livewire\Admin\FontManager;
use App\Models\AppFont;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\AppFontService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('fonts');
    Storage::disk('fonts')->put('VT323-Regular.ttf', 'vt323-font');
    Storage::disk('fonts')->put('HackerNoonV2-Regular.otf', 'hackernoon-font');

    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
});

describe('Access control', function () {
    it('redirects guests to login', function () {
        $this->get('/admin/fonts')->assertRedirect('/login');
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/admin/fonts')->assertStatus(403);
    });

    it('allows admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get('/admin/fonts')
            ->assertStatus(200)
            ->assertSeeLivewire('admin.font-manager');
    });
});

describe('Font management', function () {
    it('seeds bundled fonts with VT323 active by default', function () {
        $fonts = AppFont::query()->orderBy('id')->get();

        expect($fonts)->toHaveCount(2)
            ->and($fonts->first()->family_name)->toBe('VT323')
            ->and(AppSetting::query()->where('key', AppFontService::ACTIVE_FONT_SETTING_KEY)->value('value'))->toBe('1')
            ->and(app(AppFontService::class)->active()->family_name)->toBe('VT323');
    });

    it('activates a different bundled font globally', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $hackerNoon = AppFont::query()->where('family_name', 'HackerNoonV2')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->call('activate', $hackerNoon->id)
            ->assertHasNoErrors();

        expect(AppSetting::query()->where('key', AppFontService::ACTIVE_FONT_SETTING_KEY)->value('value'))
            ->toBe((string) $hackerNoon->id);

        $this->actingAs($admin)->get('/admin/fonts')
            ->assertOk()
            ->assertDontSee('fonts.googleapis.com', false)
            ->assertSee('--app-font-family', false)
            ->assertSee('HackerNoonV2-Regular.otf', false)
            ->assertSee('fonts-loaded', false)
            ->assertSee('visibility: hidden', false);
    });

    it('installs uploaded fonts on the fonts disk', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $file = UploadedFile::fake()->create('ArcadeClassic-Regular.ttf', 32, 'font/sfnt');

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->set('fontFile', $file)
            ->set('label', 'Arcade Classic')
            ->set('familyName', 'ArcadeClassic')
            ->call('install')
            ->assertHasNoErrors();

        $font = AppFont::query()->where('family_name', 'ArcadeClassic')->first();

        expect($font)->not->toBeNull()
            ->and($font->relative_path)->toBe('uploads/arcadeclassic.ttf')
            ->and(Storage::disk('fonts')->exists('uploads/arcadeclassic.ttf'))->toBeTrue();
    });

    it('accepts font uploads detected as font/sfnt', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $file = UploadedFile::fake()->create('5x7 MT Pixel.ttf', 32, 'font/sfnt');

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->set('fontFile', $file)
            ->set('label', '5x7 MT Pixel')
            ->set('familyName', 'FiveBySevenPixel')
            ->call('install')
            ->assertHasNoErrors();

        expect(AppFont::query()->where('family_name', 'FiveBySevenPixel')->exists())->toBeTrue();
    });

    it('rejects invalid font uploads', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->set('fontFile', UploadedFile::fake()->create('notes.txt', 10, 'text/plain'))
            ->set('label', 'Notes')
            ->set('familyName', 'Notes')
            ->call('install')
            ->assertHasErrors(['fontFile']);
    });

    it('prevents deleting bundled fonts', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $bundled = AppFont::query()->where('family_name', 'VT323')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->call('delete', $bundled->id);

        expect(AppFont::query()->whereKey($bundled->id)->exists())->toBeTrue();
    });

    it('prevents deleting the active uploaded font', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $file = UploadedFile::fake()->create('PixelQuest-Regular.woff2', 32, 'font/woff2');

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->set('fontFile', $file)
            ->set('label', 'Pixel Quest')
            ->set('familyName', 'PixelQuest')
            ->call('install')
            ->assertHasNoErrors();

        $font = AppFont::query()->where('family_name', 'PixelQuest')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(FontManager::class)
            ->call('activate', $font->id)
            ->call('delete', $font->id);

        expect(AppFont::query()->whereKey($font->id)->exists())->toBeTrue();
    });
});
