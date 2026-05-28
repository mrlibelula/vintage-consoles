<?php

use App\Livewire\Dashboard;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');
});

// ─────────────────────────────────────────────────────────────────────────────
// Seed helpers
// ─────────────────────────────────────────────────────────────────────────────

function seedDashboardConsoles(): void
{
    foreach ([
        ['id' => 1, 'short_name' => 'nes',      'long_name' => 'Nintendo Entertainment System'],
        ['id' => 2, 'short_name' => 'snes',     'long_name' => 'Super Nintendo'],
        ['id' => 3, 'short_name' => 'arcade',   'long_name' => 'Arcade'],
        ['id' => 4, 'short_name' => 'atari2600','long_name' => 'Atari 2600'],
        ['id' => 5, 'short_name' => 'pc',       'long_name' => 'PC / MS-DOS'],
    ] as $attrs) {
        Console::create(array_merge([
            'description'   => '',
            'emulator_name' => 'EmulatorJS',
            'console_bgs'   => [],
            'specs'         => [],
            'community_links' => [],
            'options'       => [],
        ], $attrs));
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// Rendering
// ─────────────────────────────────────────────────────────────────────────────

describe('Dashboard rendering', function () {
    it('can be rendered with no consoles', function () {
        Livewire::test(Dashboard::class)
            ->assertStatus(200);
    });

    it('renders the dashboard view', function () {
        Livewire::test(Dashboard::class)
            ->assertViewIs('livewire.dashboard');
    });

    it('can be rendered with seeded consoles', function () {
        seedDashboardConsoles();

        Livewire::test(Dashboard::class)
            ->assertStatus(200);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Mount / initialisation
// ─────────────────────────────────────────────────────────────────────────────

describe('Dashboard initialisation', function () {
    it('selects the first console by default', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class);

        // selected_console is a Console model — check its short_name
        expect($component->instance()->selected_console->short_name)->toBe('nes');
    });

    it('can accept a console_short_name parameter on mount', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'snes']);

        expect($component->instance()->selected_console->short_name)->toBe('snes');
    });

    it('falls back to first console for an invalid console_short_name', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'invalid']);

        expect($component->instance()->selected_console->short_name)->toBe('nes');
    });

    it('has show_hero false by default', function () {
        seedDashboardConsoles();

        Livewire::test(Dashboard::class)
            ->assertSet('show_hero', false);
    });

    it('has ob defaulting to group', function () {
        seedDashboardConsoles();
        session()->forget('ob');

        Livewire::test(Dashboard::class)
            ->assertSet('ob', 'group');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Console switching
// ─────────────────────────────────────────────────────────────────────────────

describe('Console switching', function () {
    it('can switch console by ID via setConsole', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class);
        $component->call('setConsole', 3); // arcade

        expect($component->instance()->selected_console->short_name)->toBe('arcade');
    });

    it('can switch console to atari2600', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class);
        $component->call('setConsole', 4);

        expect($component->instance()->selected_console->short_name)->toBe('atari2600');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab position detection
// ─────────────────────────────────────────────────────────────────────────────

describe('Tab position detection', function () {
    it('correctly identifies first tab', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'nes']);
        expect($component->instance()->isSelectedTabFirst())->toBeTrue();
        expect($component->instance()->isSelectedTabLast())->toBeFalse();
    });

    it('correctly identifies last tab', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'pc']);
        expect($component->instance()->isSelectedTabFirst())->toBeFalse();
        expect($component->instance()->isSelectedTabLast())->toBeTrue();
    });

    it('correctly identifies a middle tab', function () {
        seedDashboardConsoles();

        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'arcade']);
        expect($component->instance()->isSelectedTabFirst())->toBeFalse();
        expect($component->instance()->isSelectedTabLast())->toBeFalse();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Session ob value
// ─────────────────────────────────────────────────────────────────────────────

describe('Session ob handling', function () {
    it('picks up ob=lista from session', function () {
        seedDashboardConsoles();
        session(['ob' => 'lista']);

        Livewire::test(Dashboard::class, ['console_short_name' => 'nes'])
            ->assertSet('ob', 'lista');
    });

    it('picks up ob=squares from session', function () {
        seedDashboardConsoles();
        session(['ob' => 'squares']);

        Livewire::test(Dashboard::class, ['console_short_name' => 'nes'])
            ->assertSet('ob', 'squares');
    });
});
