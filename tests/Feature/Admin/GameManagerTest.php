<?php

use App\Livewire\Admin\GameManager;
use App\Models\Console;
use App\Models\Game;
use App\Models\Screenshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');

    Role::firstOrCreate(['name' => 'admin',  'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'user',   'guard_name' => 'web']);

    Console::create([
        'id'            => 1,
        'short_name'    => 'nes',
        'long_name'     => 'Nintendo Entertainment System',
        'description'   => '',
        'emulator_name' => 'EmulatorJS',
        'console_bgs'   => [],
        'specs'         => [],
        'community_links' => [],
        'options'       => [],
    ]);
});

// ─────────────────────────────────────────────────────────────────────────────
// Access control
// ─────────────────────────────────────────────────────────────────────────────

describe('Access control', function () {
    it('redirects guests to login', function () {
        $this->get('/admin/games')->assertRedirect('/login');
    });

    it('forbids non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->actingAs($user)->get('/admin/games')->assertStatus(403);
    });

    it('allows admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get('/admin/games')
            ->assertStatus(200)
            ->assertSeeLivewire('admin.game-manager');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Component mounting
// ─────────────────────────────────────────────────────────────────────────────

describe('GameManager component', function () {
    it('mounts and selects the first console', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $component = Livewire::actingAs($user)->test(GameManager::class);

        expect($component->instance()->selectedConsole)->toBe('nes');
    });

    it('lists games for the selected console', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Game::create([
            'console_id'         => 1,
            'title'              => 'Super Mario Bros',
            'slug'               => 'super-mario-bros',
            'publisher'          => 'Nintendo',
            'release_year'       => '1985',
            'description'        => 'Platformer',
            'rating'             => 0.9,
            'multiplayer_support'=> false,
            'save_state_support' => true,
            'is_free'            => true,
            'needs_igdb_sync'    => false,
        ]);

        $component = Livewire::actingAs($user)->test(GameManager::class);

        expect($component->instance()->games)->toHaveCount(1)
            ->and($component->instance()->games->first()->title)->toBe('Super Mario Bros');
    });

    it('can open the add-game modal', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openAddModal')
            ->assertSet('showModal', true)
            ->assertSet('modalMode', 'add');
    });

    it('can close the modal', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openAddModal')
            ->call('closeModal')
            ->assertSet('showModal', false);
    });

    it('can open the edit modal for an existing game', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $game = Game::create([
            'console_id'         => 1,
            'title'              => 'Zelda',
            'slug'               => 'zelda',
            'publisher'          => 'Nintendo',
            'release_year'       => '1986',
            'description'        => 'Adventure',
            'rating'             => 0.91,
            'multiplayer_support'=> false,
            'save_state_support' => true,
            'is_free'            => true,
            'needs_igdb_sync'    => false,
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openEditModal', $game->id)
            ->assertSet('showModal', true)
            ->assertSet('modalMode', 'edit')
            ->assertSet('title', 'Zelda');
    });

    it('can delete an existing game', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $game = Game::create([
            'console_id'         => 1,
            'title'              => 'Contra',
            'slug'               => 'contra',
            'publisher'          => 'Konami',
            'release_year'       => '1988',
            'description'        => 'Shooter',
            'rating'             => 0.88,
            'multiplayer_support'=> true,
            'save_state_support' => true,
            'is_free'            => true,
            'needs_igdb_sync'    => false,
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openDeleteModal', $game->id)
            ->call('deleteGame');

        expect(Game::find($game->id))->toBeNull();
    });

    it('can add a genre row', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $component = Livewire::actingAs($user)->test(GameManager::class);
        $initial   = count($component->instance()->genres);

        $component->call('addGenre');

        expect(count($component->instance()->genres))->toBe($initial + 1);
    });

    it('can remove a genre row when more than one exists', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('addGenre')
            ->call('addGenre')
            ->call('removeGenre', 0)
            ->assertHasNoErrors();
    });

    it('can add a screenshot row', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $component = Livewire::actingAs($user)->test(GameManager::class);
        $initial = count($component->instance()->screenshots);

        $component->call('addScreenshot');

        expect(count($component->instance()->screenshots))->toBe($initial + 1);
    });

    it('persists manually added screenshots when updating a game', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $game = Game::create([
            'console_id'         => 1,
            'title'              => 'Metroid',
            'slug'               => 'metroid',
            'publisher'          => 'Nintendo',
            'release_year'       => '1986',
            'description'        => 'Adventure',
            'rating'             => 0.9,
            'multiplayer_support'=> false,
            'save_state_support' => true,
            'is_free'            => true,
            'needs_igdb_sync'    => false,
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openEditModal', $game->id)
            ->call('addScreenshot')
            ->set('screenshots.0.thumb_url', 'https://example.com/thumb.jpg')
            ->set('screenshots.0.full_url', 'https://example.com/full.jpg')
            ->call('saveGame')
            ->assertHasNoErrors();

        $game->refresh();

        expect($game->screenshots)->toHaveCount(1)
            ->and($game->screenshots->first()->thumb_url)->toBe('https://example.com/thumb.jpg')
            ->and($game->screenshots->first()->full_url)->toBe('https://example.com/full.jpg');
    });

    it('shows the screenshot gallery markup in the edit modal', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $game = Game::create([
            'console_id'         => 1,
            'title'              => 'Castlevania',
            'slug'               => 'castlevania',
            'publisher'          => 'Konami',
            'release_year'       => '1987',
            'description'        => 'Action',
            'rating'             => 0.88,
            'multiplayer_support'=> false,
            'save_state_support' => true,
            'is_free'            => true,
            'needs_igdb_sync'    => false,
        ]);

        Screenshot::create([
            'game_id'       => $game->id,
            'thumb_url'     => 'https://example.com/thumb.jpg',
            'full_url'      => 'https://example.com/full.jpg',
            'position'      => 0,
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openEditModal', $game->id)
            ->assertSee('aria-label="Close gallery"', false)
            ->assertSee('x-teleport="body"', false)
            ->assertSee('backdrop-blur-[4.5px]', false)
            ->assertSee('<swiper-container', false)
            ->assertSee('fetchpriority="high"', false)
            ->assertSee('@keydown.arrow-left.window.capture', false)
            ->assertSee('@keydown.arrow-right.window.capture', false)
            ->assertSee('claimKeyboard()', false);
    });

    it('can filter games by search term', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        foreach (['Mega Man', 'Contra', 'Castlevania'] as $title) {
            Game::create([
                'console_id'         => 1,
                'title'              => $title,
                'slug'               => \Illuminate\Support\Str::slug($title),
                'publisher'          => 'Konami',
                'release_year'       => '1988',
                'description'        => 'Game',
                'rating'             => 0.8,
                'multiplayer_support'=> false,
                'save_state_support' => true,
                'is_free'            => true,
                'needs_igdb_sync'    => false,
            ]);
        }

        $component = Livewire::actingAs($user)
            ->test(GameManager::class)
            ->set('searchTerm', 'mega');

        $filtered = $component->instance()->filteredGames;

        expect($filtered)->toHaveCount(1)
            ->and($filtered->first()->title)->toBe('Mega Man');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// ROM handling
// ─────────────────────────────────────────────────────────────────────────────

describe('ROM handling', function () {
    it('shows a .jsdos URL field for MS-DOS and hides the file upload', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Console::create([
            'id'            => 5,
            'short_name'    => 'pc',
            'long_name'     => 'MS-DOS',
            'description'   => '',
            'emulator_name' => 'js-dos',
            'console_bgs'   => [],
            'specs'         => [],
            'community_links' => [],
            'options'       => [],
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openAddModal')
            ->set('formConsole', 'pc')
            ->assertSee('rom_url')
            ->assertSee('MS-DOS games require a .jsdos bundle URL')
            ->assertDontSee('id="rom_file"');
    });

    it('shows console-specific ROM extension guidance for cartridge consoles', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Console::create([
            'id'            => 2,
            'short_name'    => 'snes',
            'long_name'     => 'Super Nintendo',
            'description'   => '',
            'emulator_name' => 'EmulatorJS',
            'console_bgs'   => [],
            'specs'         => [],
            'community_links' => [],
            'options'       => [],
        ]);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openAddModal')
            ->set('formConsole', 'snes')
            ->assertSee('SNES: .zip, .7z, .smc')
            ->assertSee('wire:model="romFile"', false)
            ->assertDontSee('rom_url');
    });

    it('stores uploaded ROM files on the data disk when saving a game', function () {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $file = UploadedFile::fake()->create('mario.nes', 100);

        Livewire::actingAs($user)
            ->test(GameManager::class)
            ->call('openAddModal')
            ->set('title', 'Super Mario Bros')
            ->set('publisher', 'Nintendo')
            ->set('release_year', '1985')
            ->set('description', 'Platformer')
            ->set('rating', '0.9')
            ->set('romFile', $file)
            ->call('saveGame')
            ->assertSet('showModal', false);

        Storage::disk('data')->assertExists('games/nes/mario.nes');

        $game = Game::first();

        expect($game)->not->toBeNull()
            ->and($game->rom)->toBe('mario.nes');
    });
});
