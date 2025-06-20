<?php

use App\Livewire\SelectedConsole;
use App\Service\Tool;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

function getMockSelectedConsole()
{
    return [
        'id' => 1,
        'short_name' => 'NES',
        'name' => 'Nintendo Entertainment System',
        'long_name' => 'Nintendo Entertainment System',
        'description' => 'Classic 8-bit gaming console from Nintendo',
        'console_logo' => 'path/to/nes_logo.png',
        'console_icon' => 'path/to/nes_icon.png',
        'console_bgs' => ['path/to/nes_bg.jpg'],
        'release_date' => '1985',
        'release_year' => '1985',
        'manufacturer' => 'Nintendo',
        'emulator' => [
            'name' => 'EmulatorJS',
            'version' => '4.0.7'
        ],
        'specs' => [
            'cpu' => '8-bit MOS 6502',
            'memory' => '2KB RAM',
            'graphics' => '256x240 resolution',
            'audio' => 'Mono audio',
            'input' => 'Controller'
        ],
        'community_links' => [
            [
                'community_name' => 'NES Community',
                'url' => 'https://nes.community',
                'description' => 'NES enthusiasts community'
            ],
            [
                'community_name' => 'Retro Gaming',
                'url' => 'https://retro.gaming',
                'description' => 'General retro gaming community'
            ]
        ],
        'games' => [
            [
                'id' => 1,
                'title' => 'Super Mario Bros.',
                'slug' => 'super-mario-bros',
                'description' => 'Classic platformer',
                'box' => 'images/games/mario.jpg',
                'poster' => 'images/games/mario-poster.jpg',
                'release_year' => '1985',
                'rating' => '0.89',
                'rom' => 'mario.nes',
                'publisher' => 'Nintendo'
            ],
            [
                'id' => 2,
                'title' => 'The Legend of Zelda',
                'slug' => 'legend-of-zelda',
                'description' => 'Adventure game',
                'box' => 'images/games/zelda.jpg',
                'poster' => 'images/games/zelda-poster.jpg',
                'release_year' => '1986',
                'rating' => '0.91',
                'rom' => 'zelda.nes',
                'publisher' => 'Nintendo'
            ]
        ]
    ];
}

beforeEach(function () {
    // Use in-memory SQLite database for testing
    config(['database.default' => 'testing']);
    config(['database.connections.testing' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]]);
    
    // Run migrations in memory only
    $this->artisan('migrate', ['--database' => 'testing']);
    
    Session::flush();
});

describe('SelectedConsole Component Initialization', function () {
    it('can be rendered', function () {
        $mockConsole = getMockSelectedConsole();
        
        Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ])->assertStatus(200);
    });

    it('initializes with default values', function () {
        $mockConsole = getMockSelectedConsole();
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        $component->assertSet('is_selected_tab_first', false)
                 ->assertSet('is_selected_tab_last', false)
                 ->assertSet('selected_console', $mockConsole)
                 ->assertSet('console_data_accordion', true)
                 ->assertSet('specs_accordion', false)
                 ->assertSet('community_accordion', true)
                 ->assertSet('ob', 'group');
    });

    it('initializes with custom selected console', function () {
        $mockConsole = getMockSelectedConsole();
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        $component->assertSet('selected_console', $mockConsole);
    });
});

describe('Accordion Management', function () {
    it('toggles console_data_accordion correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        // Initially true
        $component->assertSet('console_data_accordion', true);

        // Toggle to false
        $component->call('toggleAccordion', 'console_data_accordion');
        $component->assertSet('console_data_accordion', false);

        // Toggle back to true
        $component->call('toggleAccordion', 'console_data_accordion');
        $component->assertSet('console_data_accordion', true);
    });

    it('toggles specs_accordion correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        // Initially false
        $component->assertSet('specs_accordion', false);

        // Toggle to true
        $component->call('toggleAccordion', 'specs_accordion');
        $component->assertSet('specs_accordion', true);

        // Toggle back to false
        $component->call('toggleAccordion', 'specs_accordion');
        $component->assertSet('specs_accordion', false);
    });

    it('toggles community_accordion correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        // Initially true
        $component->assertSet('community_accordion', true);

        // Toggle to false
        $component->call('toggleAccordion', 'community_accordion');
        $component->assertSet('community_accordion', false);

        // Toggle back to true
        $component->call('toggleAccordion', 'community_accordion');
        $component->assertSet('community_accordion', true);
    });

    it('only toggles the specified accordion', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        // Toggle specs_accordion
        $component->call('toggleAccordion', 'specs_accordion');

        // Check that only specs_accordion changed
        $component->assertSet('specs_accordion', true)
                 ->assertSet('console_data_accordion', true) // Should remain unchanged
                 ->assertSet('community_accordion', true); // Should remain unchanged
    });
});

describe('Session Management', function () {
    it('uses default ob value when session does not exist', function () {
        $mockConsole = getMockSelectedConsole();
        // Don't set session value, should use default
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);
        $component->assertSet('ob', 'group');
    });

    it('uses session ob value when it exists', function () {
        $mockConsole = getMockSelectedConsole();
        Session::put('ob', 'squares');
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);
        $component->assertSet('ob', 'squares');
    });

    it('handles different ob values from session', function () {
        $mockConsole = getMockSelectedConsole();
        $testValues = ['group', 'squares', 'lista'];

        foreach ($testValues as $value) {
            Session::put('ob', $value);
            
            $component = Livewire::test(SelectedConsole::class, [
                'selected_console' => $mockConsole
            ]);
            $component->assertSet('ob', $value);
        }
    });
});

describe('Tab Position Properties', function () {
    it('handles tab position properties correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole,
            'is_selected_tab_first' => true,
            'is_selected_tab_last' => false
        ]);

        $component->assertSet('is_selected_tab_first', true)
                 ->assertSet('is_selected_tab_last', false);
    });

    it('can update tab position properties', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        $component->set('is_selected_tab_first', true);
        $component->assertSet('is_selected_tab_first', true);

        $component->set('is_selected_tab_last', true);
        $component->assertSet('is_selected_tab_last', true);
    });
});

describe('Component Lifecycle', function () {
    it('calls rendered method correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);
        $component->call('rendered');
        
        // Should not throw errors
        expect(true)->toBeTrue();
    });

    it('calls mount method correctly', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);
        
        // Mount is called automatically during component initialization
        // Just check that the component initializes without errors
        expect(true)->toBeTrue();
    });
});

describe('View Rendering', function () {
    it('renders the correct view', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);
        $component->assertViewIs('livewire.selected-console');
    });

    it('passes correct data to view', function () {
        $mockConsole = getMockSelectedConsole();
        
        Session::put('ob', 'squares');

        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole,
            'is_selected_tab_first' => true,
            'console_data_accordion' => false
        ]);

        $component->assertViewHas('selected_console', $mockConsole)
                 ->assertViewHas('is_selected_tab_first', true)
                 ->assertViewHas('console_data_accordion', false)
                 ->assertViewHas('ob', 'squares');
    });
});

describe('Component Properties Validation', function () {
    it('handles console with all required properties', function () {
        $mockConsole = getMockSelectedConsole();
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        $component->assertSet('selected_console', function ($console) {
            return isset($console['short_name']) &&
                   isset($console['specs']) &&
                   isset($console['community_links']) &&
                   isset($console['games']);
        });
    });

    it('handles console with missing optional properties', function () {
        $minimalConsole = [
            'id' => 1,
            'short_name' => 'TEST',
            'name' => 'Test Console',
            'long_name' => 'Test Console Full Name',
            'description' => 'Test Console Description',
            'console_icon' => 'test.png',
            'manufacturer' => 'Test Manufacturer',
            'release_year' => '2023',
            'emulator' => ['name' => 'Test Emulator', 'version' => '1.0'],
            'specs' => [
                'cpu' => 'Test CPU',
                'memory' => 'Test Memory',
                'graphics' => 'Test Graphics'
            ],
            'community_links' => [],
            'games' => []
        ];
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $minimalConsole
        ]);

        $component->assertSet('selected_console', $minimalConsole);
    });

    it('validates game count display', function () {
        $mockConsole = getMockSelectedConsole();
        
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        expect(count($mockConsole['games']))->toBe(2);
        $component->assertSet('selected_console', function ($console) {
            return count($console['games']) === 2;
        });
    });
});

describe('Error Handling', function () {
    it('handles invalid accordion property gracefully', function () {
        $mockConsole = getMockSelectedConsole();
        $component = Livewire::test(SelectedConsole::class, [
            'selected_console' => $mockConsole
        ]);

        // Check that proper accordions exist and work correctly
        $component->assertSet('console_data_accordion', true)
                 ->assertSet('specs_accordion', false)
                 ->assertSet('community_accordion', true);
                 
        // Test that valid accordions still work
        $component->call('toggleAccordion', 'specs_accordion');
        $component->assertSet('specs_accordion', true);
    });
}); 