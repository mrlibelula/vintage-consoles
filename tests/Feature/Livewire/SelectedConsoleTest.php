<?php

use App\Livewire\SelectedConsole;
use App\Models\Console;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Seed helper
// ─────────────────────────────────────────────────────────────────────────────

function makeSelectedConsole(array $attrs = []): Console
{
    return Console::factory()->create(array_merge([
        'id'         => 1,
        'short_name' => 'NES',
        'long_name'  => 'Nintendo Entertainment System',
        'description'=> 'Classic 8-bit gaming console from Nintendo',
    ], $attrs));
}

beforeEach(function () {
    Session::flush();
});

// ─────────────────────────────────────────────────────────────────────────────
// Rendering
// ─────────────────────────────────────────────────────────────────────────────

describe('SelectedConsole rendering', function () {
    it('can be rendered', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertStatus(200);
    });

    it('renders the correct view', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertViewIs('livewire.selected-console');
    });

    it('renders carousel sort controls with icons', function () {
        $console = makeSelectedConsole();
        $console->setRelation('games', collect());

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSee('Sort by title', false)
            ->assertSee('Sort by rating', false)
            ->assertSee('M14 9h7v2h-7zm0-6h7v2h-7', false)
            ->assertSee('M7 1h10v2H7zM5 3h2v2H5', false);
    });

    it('initializes with correct defaults', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('is_selected_tab_first', false)
            ->assertSet('is_selected_tab_last', false)
            ->assertSet('console_data_accordion', true)
            ->assertSet('specs_accordion', false)
            ->assertSet('community_accordion', true)
            ->assertSet('ob', 'group')
            ->assertSet('gameSortField', 'rating')
            ->assertSet('gameSortDirection', 'desc');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Accordion Management
// ─────────────────────────────────────────────────────────────────────────────

describe('Accordion management', function () {
    it('toggles console_data_accordion on and off', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('console_data_accordion', true)
            ->call('toggleAccordion', 'console_data_accordion')
            ->assertSet('console_data_accordion', false)
            ->call('toggleAccordion', 'console_data_accordion')
            ->assertSet('console_data_accordion', true);
    });

    it('toggles specs_accordion on and off', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('specs_accordion', false)
            ->call('toggleAccordion', 'specs_accordion')
            ->assertSet('specs_accordion', true)
            ->call('toggleAccordion', 'specs_accordion')
            ->assertSet('specs_accordion', false);
    });

    it('toggles community_accordion on and off', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('community_accordion', true)
            ->call('toggleAccordion', 'community_accordion')
            ->assertSet('community_accordion', false)
            ->call('toggleAccordion', 'community_accordion')
            ->assertSet('community_accordion', true);
    });

    it('only toggles the specified accordion', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->call('toggleAccordion', 'specs_accordion')
            ->assertSet('specs_accordion', true)
            ->assertSet('console_data_accordion', true)
            ->assertSet('community_accordion', true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Session ob handling
// ─────────────────────────────────────────────────────────────────────────────

describe('Session ob handling', function () {
    it('defaults to "group" when session has no ob', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('ob', 'group');
    });

    it('picks up ob from session', function () {
        $console = makeSelectedConsole();
        Session::put('ob', 'squares');

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('ob', 'squares');
    });

    it('handles all valid ob values from session', function () {
        $console = makeSelectedConsole();

        foreach (['group', 'squares', 'lista'] as $value) {
            Session::put('ob', $value);

            Livewire::test(SelectedConsole::class, ['selected_console' => $console])
                ->assertSet('ob', $value);
        }
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Tab position properties
// ─────────────────────────────────────────────────────────────────────────────

describe('Tab position properties', function () {
    it('accepts is_selected_tab_first on initialisation', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, [
            'selected_console'    => $console,
            'is_selected_tab_first' => true,
            'is_selected_tab_last'  => false,
        ])
        ->assertSet('is_selected_tab_first', true)
        ->assertSet('is_selected_tab_last', false);
    });

    it('can update tab position properties at runtime', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->set('is_selected_tab_first', true)
            ->assertSet('is_selected_tab_first', true)
            ->set('is_selected_tab_last', true)
            ->assertSet('is_selected_tab_last', true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Component properties and data
// ─────────────────────────────────────────────────────────────────────────────

describe('Component properties', function () {
    it('exposes the console model as a component property', function () {
        $console = makeSelectedConsole();

        $component = Livewire::test(SelectedConsole::class, ['selected_console' => $console]);

        $instance = $component->instance();
        expect($instance->selected_console)->toBeInstanceOf(Console::class)
            ->and($instance->selected_console->short_name)->toBe('NES');
    });

    it('passes correct data to the view', function () {
        $console = makeSelectedConsole();
        Session::put('ob', 'squares');

        Livewire::test(SelectedConsole::class, [
            'selected_console'    => $console,
            'is_selected_tab_first' => true,
            'console_data_accordion' => false,
        ])
        ->assertViewHas('is_selected_tab_first', true)
        ->assertViewHas('console_data_accordion', false)
        ->assertViewHas('ob', 'squares');
    });

    it('rendered method can be called without errors', function () {
        $console = makeSelectedConsole();

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->call('rendered');

        expect(true)->toBeTrue();
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Carousel sorting
// ─────────────────────────────────────────────────────────────────────────────

describe('Carousel sorting', function () {
    it('toggles sort direction when the same field is selected twice', function () {
        $console = makeSelectedConsole();
        $console->setRelation('games', collect());

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->assertSet('gameSortField', 'rating')
            ->assertSet('gameSortDirection', 'desc')
            ->call('sortCarouselBy', 'rating')
            ->assertSet('gameSortDirection', 'asc')
            ->call('sortCarouselBy', 'rating')
            ->assertSet('gameSortDirection', 'desc');
    });

    it('uses each field default direction when switching sort fields', function () {
        $console = makeSelectedConsole();
        $console->setRelation('games', collect());

        Livewire::test(SelectedConsole::class, ['selected_console' => $console])
            ->call('sortCarouselBy', 'rating')
            ->assertSet('gameSortField', 'rating')
            ->assertSet('gameSortDirection', 'asc')
            ->call('sortCarouselBy', 'title')
            ->assertSet('gameSortField', 'title')
            ->assertSet('gameSortDirection', 'asc')
            ->call('sortCarouselBy', 'rating')
            ->assertSet('gameSortField', 'rating')
            ->assertSet('gameSortDirection', 'desc');
    });
});
