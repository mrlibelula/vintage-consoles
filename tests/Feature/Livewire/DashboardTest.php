<?php

use App\Livewire\Dashboard;
use App\Service\GameSession;
use App\Service\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function getMockCompleteConsoleData() {
    return [
        'consoles' => [
            [
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
                    'graphics' => '256x240 resolution'
                ],
                'community_links' => [
                    [
                        'community_name' => 'NES Community',
                        'url' => 'https://nes.community',
                        'description' => 'NES enthusiasts community'
                    ]
                ],
                'games' => [
                    [
                        'id' => 1,
                        'title' => 'Super Mario Bros.',
                        'slug' => 'super-mario-bros',
                        'box' => 'images/games/mario.jpg',
                        'poster' => 'images/games/mario-poster.jpg',
                        'description' => 'Classic platformer',
                        'release_year' => '1985',
                        'rating' => '0.89',
                        'rom' => 'mario.nes',
                        'publisher' => 'Nintendo'
                    ],
                    [
                        'id' => 2,
                        'title' => 'The Legend of Zelda',
                        'slug' => 'legend-of-zelda',
                        'box' => 'images/games/zelda.jpg',
                        'poster' => 'images/games/zelda-poster.jpg',
                        'description' => 'Adventure game',
                        'release_year' => '1986',
                        'rating' => '0.91',
                        'rom' => 'zelda.nes',
                        'publisher' => 'Nintendo'
                    ]
                ]
            ],
            [
                'id' => 2,
                'short_name' => 'SNES',
                'name' => 'Super Nintendo Entertainment System',
                'long_name' => 'Super Nintendo Entertainment System',
                'description' => '16-bit gaming console from Nintendo',
                'console_logo' => 'path/to/snes_logo.png',
                'console_icon' => 'path/to/snes_icon.png',
                'console_bgs' => ['path/to/snes_bg.jpg'],
                'release_date' => '1991',
                'release_year' => '1991',
                'manufacturer' => 'Nintendo',
                'emulator' => [
                    'name' => 'EmulatorJS',
                    'version' => '4.0.7'
                ],
                'specs' => [
                    'cpu' => '16-bit Ricoh 5A22',
                    'memory' => '128KB RAM',
                    'graphics' => '256x224 resolution'
                ],
                'community_links' => [
                    [
                        'community_name' => 'SNES Community',
                        'url' => 'https://snes.community',
                        'description' => 'SNES enthusiasts community'
                    ]
                ],
                'games' => [
                    [
                        'id' => 3,
                        'title' => 'Super Mario World',
                        'slug' => 'super-mario-world',
                        'box' => 'images/games/mario-world.jpg',
                        'poster' => 'images/games/mario-world-poster.jpg',
                        'description' => 'Platform adventure',
                        'release_year' => '1991',
                        'rating' => '0.95',
                        'rom' => 'mario-world.smc',
                        'publisher' => 'Nintendo'
                    ]
                ]
            ],
            [
                'id' => 3,
                'short_name' => 'Arcade',
                'name' => 'Arcade Games',
                'long_name' => 'Arcade Games',
                'description' => 'Classic arcade gaming machines',
                'console_logo' => 'path/to/arcade_logo.png',
                'console_icon' => 'path/to/arcade_icon.png',
                'console_bgs' => ['path/to/arcade_bg.jpg'],
                'release_date' => '1970s',
                'release_year' => '1970s',
                'manufacturer' => 'Various',
                'emulator' => [
                    'name' => 'EmulatorJS',
                    'version' => '4.0.7'
                ],
                'specs' => [
                    'cpu' => 'Various processors',
                    'memory' => 'Varies by machine',
                    'graphics' => 'CRT displays'
                ],
                'community_links' => [
                    [
                        'community_name' => 'Arcade Community',
                        'url' => 'https://arcade.community',
                        'description' => 'Arcade gaming enthusiasts'
                    ]
                ],
                'games' => [
                    [
                        'id' => 4,
                        'title' => 'Pac-Man',
                        'slug' => 'pac-man',
                        'box' => 'images/games/pacman.jpg',
                        'poster' => 'images/games/pacman-poster.jpg',
                        'description' => 'Classic maze game',
                        'release_year' => '1980',
                        'rating' => '0.88',
                        'rom' => 'pacman.zip',
                        'publisher' => 'Namco'
                    ]
                ]
            ],
            [
                'id' => 4,
                'short_name' => 'Atari2600',
                'name' => 'Atari 2600',
                'long_name' => 'Atari 2600',
                'description' => 'Classic Atari gaming console',
                'console_logo' => 'path/to/atari_logo.png',
                'console_icon' => 'path/to/atari_icon.png',
                'console_bgs' => ['path/to/atari_bg.jpg'],
                'release_date' => '1977',
                'release_year' => '1977',
                'manufacturer' => 'Atari',
                'emulator' => [
                    'name' => 'EmulatorJS',
                    'version' => '4.0.7'
                ],
                'specs' => [
                    'cpu' => 'MOS 6507',
                    'memory' => '128 bytes RAM',
                    'graphics' => '160x192 resolution'
                ],
                'community_links' => [
                    [
                        'community_name' => 'Atari Community',
                        'url' => 'https://atari.community',
                        'description' => 'Atari 2600 enthusiasts'
                    ]
                ],
                'games' => [
                    [
                        'id' => 5,
                        'title' => 'Space Invaders',
                        'slug' => 'space-invaders',
                        'box' => 'images/games/space-invaders.jpg',
                        'poster' => 'images/games/space-invaders-poster.jpg',
                        'description' => 'Classic shooter',
                        'release_year' => '1978',
                        'rating' => '0.85',
                        'rom' => 'spaceinv.bin',
                        'publisher' => 'Atari'
                    ]
                ]
            ],
            [
                'id' => 5,
                'short_name' => 'PC',
                'name' => 'Personal Computer',
                'long_name' => 'Personal Computer',
                'description' => 'DOS and early PC gaming',
                'console_logo' => 'path/to/pc_logo.png',
                'console_icon' => 'path/to/pc_icon.png',
                'console_bgs' => ['path/to/pc_bg.jpg'],
                'release_date' => '1980s',
                'release_year' => '1980s',
                'manufacturer' => 'Various',
                'emulator' => [
                    'name' => 'DOSBox',
                    'version' => '0.74'
                ],
                'specs' => [
                    'cpu' => 'Intel 8086/8088',
                    'memory' => '640KB RAM',
                    'graphics' => 'VGA 320x200'
                ],
                'community_links' => [
                    [
                        'community_name' => 'DOS Gaming Community',
                        'url' => 'https://dosgaming.community',
                        'description' => 'DOS gaming enthusiasts'
                    ]
                ],
                'games' => [
                    [
                        'id' => 6,
                        'title' => 'Doom',
                        'slug' => 'doom',
                        'box' => 'images/games/doom.jpg',
                        'poster' => 'images/games/doom-poster.jpg',
                        'description' => 'Classic FPS',
                        'release_year' => '1993',
                        'rating' => '0.92',
                        'rom' => 'doom.zip',
                        'publisher' => 'id Software'
                    ]
                ]
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
    
    Storage::fake('data');
    Storage::disk('data')->put('vintage-consoles.json', json_encode(getMockCompleteConsoleData()));
});

describe('Dashboard Component Initialization', function () {
    it('can be rendered', function () {
        Livewire::test(Dashboard::class)
            ->assertStatus(200);
    });

    it('initializes with default values', function () {
        $component = Livewire::test(Dashboard::class);
        
        // The component loads consoles on mount, so they won't be empty
        $component->assertSet('selected_console', function ($console) {
                     return is_array($console) && isset($console['short_name']);
                 })
                 ->assertSet('selected_console_id', function ($id) {
                     return is_int($id) && $id > 0;
                 })
                 ->assertSet('show_hero', false)
                 ->assertSet('ob', 'group');
    });

    it('loads console data on mount', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->assertSet('consoles', function ($consoles) {
            return count($consoles) === 5 && 
                   $consoles[0]['short_name'] === 'NES' &&
                   $consoles[1]['short_name'] === 'SNES';
        });
    });

    it('sets default console on mount', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->assertSet('selected_console_id', 1)
                 ->assertSet('selected_console', function ($console) {
                     return $console['short_name'] === 'NES' && $console['id'] === 1;
                 });
    });

    it('accepts console short name parameter on mount', function () {
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'snes']);
        
        $component->assertSet('selected_console_id', 2)
                 ->assertSet('selected_console', function ($console) {
                     return $console['short_name'] === 'SNES' && $console['id'] === 2;
                 });
    });

    it('defaults to NES for invalid console short name', function () {
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'invalid']);
        
        $component->assertSet('selected_console_id', 1)
                 ->assertSet('selected_console', function ($console) {
                     return $console['short_name'] === 'NES';
                 });
    });
});

describe('Console Management', function () {
    it('can set console by ID', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->call('setConsole', 3)
                 ->assertSet('selected_console_id', 3)
                 ->assertSet('selected_console', function ($console) {
                     return $console['short_name'] === 'Arcade' && $console['id'] === 3;
                 });
    });

    it('clears selected console before setting new one', function () {
        $component = Livewire::test(Dashboard::class);
        
        // Set initial console
        $component->call('setConsole', 2);
        
        // Set different console
        $component->call('setConsole', 4)
                 ->assertSet('selected_console_id', 4)
                 ->assertSet('selected_console', function ($console) {
                     return $console['short_name'] === 'Atari2600';
                 });
    });
});

describe('Tab Position Detection', function () {
    it('correctly identifies first tab', function () {
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'nes']);
        
        expect($component->instance()->isSelectedTabFirst())->toBeTrue();
        expect($component->instance()->isSelectedTabLast())->toBeFalse();
    });

    it('correctly identifies last tab', function () {
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'pc']);
        
        expect($component->instance()->isSelectedTabFirst())->toBeFalse();
        expect($component->instance()->isSelectedTabLast())->toBeTrue();
    });

    it('correctly identifies middle tab', function () {
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'arcade']);
        
        expect($component->instance()->isSelectedTabFirst())->toBeFalse();
        expect($component->instance()->isSelectedTabLast())->toBeFalse();
    });

    it('returns false for position detection with no selected console', function () {
        $component = Livewire::test(Dashboard::class);
        
        // Clear selected console
        $component->set('selected_console', []);
        
        expect($component->instance()->isSelectedTabFirst())->toBeFalse();
        expect($component->instance()->isSelectedTabLast())->toBeFalse();
    });
});

describe('Hero Image Management', function () {
    it('sets hero image from predefined list', function () {
        $component = Livewire::test(Dashboard::class);
        
        $initialImage = $component->get('hero_image');
        $component->call('randomHeroImage');
        $newImage = $component->get('hero_image');
        
        expect($newImage)->not->toBeEmpty();
        expect($newImage)->toBeString();
        // Should be a URL from the predefined list
        expect($newImage)->toContain('http');
    });

    it('hero image changes on multiple calls', function () {
        $component = Livewire::test(Dashboard::class);
        
        $images = [];
        for ($i = 0; $i < 10; $i++) {
            $component->call('randomHeroImage');
            $images[] = $component->get('hero_image');
        }
        
        // Should have at least some variation (not all the same)
        $uniqueImages = array_unique($images);
        expect(count($uniqueImages))->toBeGreaterThan(1);
    });
});

describe('Session and Request Handling', function () {
    it('handles ob parameter from request', function () {
        // Test the request parameter handling indirectly by checking that the component
        // can handle different ob values when they're set in the session
        Session::put('ob', 'lista');
        
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'nes']);
        
        // The component should pick up the session value since request params are hard to test in Livewire
        $component->assertSet('ob', 'lista');
        expect(Session::get('ob'))->toBe('lista');
    });

    it('uses session ob value when request parameter not present', function () {
        Session::put('ob', 'squares');
        
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'nes']);
        
        $component->assertSet('ob', 'squares');
    });

    it('defaults to group when no request parameter or session value', function () {
        Session::forget('ob');
        
        $component = Livewire::test(Dashboard::class, ['console_short_name' => 'nes']);
        
        $component->assertSet('ob', 'group');
    });
});

describe('Data Loading', function () {
    it('loads consoles from storage', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->call('loadConsoles');
        
        $component->assertSet('consoles', function ($consoles) {
            return is_array($consoles) && count($consoles) === 5;
        });
    });

    it('handles missing data file gracefully', function () {
        // Use fake storage without the file instead of deleting real file
        Storage::fake('data');
        
        $component = Livewire::test(Dashboard::class);
        $component->call('loadConsoles');
        
        $component->assertSet('consoles', []);
    });

    it('can load from custom data source', function () {
        $customData = [
            'consoles' => [
                [
                    'id' => 99, 
                    'short_name' => 'Custom',
                    'name' => 'Custom Console',
                    'long_name' => 'Custom Console',
                    'description' => 'A custom console for testing',
                    'console_logo' => 'path/to/custom_logo.png',
                    'console_icon' => 'path/to/custom_icon.png',
                    'console_bgs' => ['path/to/custom_bg.jpg'],
                    'release_date' => '2024',
                    'release_year' => '2024',
                    'manufacturer' => 'Test Corp',
                    'emulator' => [
                        'name' => 'TestEmulator',
                        'version' => '1.0.0'
                    ],
                    'specs' => [
                        'cpu' => 'Test CPU',
                        'memory' => 'Test Memory',
                        'graphics' => 'Test Graphics'
                    ],
                    'community_links' => [],
                    'games' => []
                ]
            ]
        ];
        Storage::disk('data')->put('custom-consoles.json', json_encode($customData));
        
        $component = Livewire::test(Dashboard::class);
        $component->call('loadConsoles', 'custom-consoles.json');
        
        $component->assertSet('consoles', function ($consoles) {
            return count($consoles) === 1 && $consoles[0]['short_name'] === 'Custom';
        });
    });
});

describe('Game Session Integration', function () {
    it('initializes game session environment', function () {
        $component = Livewire::test(Dashboard::class);
        
        // This should not throw any errors
        $component->call('initGameSessionEnviro');
        
        // Verify the component still works after initialization
        $component->assertStatus(200);
    });
});

describe('Component Rendering', function () {
    it('renders the dashboard view', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->assertViewIs('livewire.dashboard');
    });

    it('passes correct data to view', function () {
        $component = Livewire::test(Dashboard::class);
        
        $component->assertViewHas('consoles')
                 ->assertViewHas('selected_console')
                 ->assertViewHas('selected_console_id')
                 ->assertViewHas('show_hero')
                 ->assertViewHas('hero_image')
                 ->assertViewHas('ob');
    });
});

describe('Console Tabs Configuration', function () {
    it('has correct default console tabs configuration', function () {
        $component = Livewire::test(Dashboard::class);
        
        $tabs = $component->get('console_tabs');
        
        expect($tabs)->toBe([
            'nes' => true,
            'snes' => false,
            'arcade' => false,
            'atari2600' => false,
            'pc' => false,
        ]);
    });
});

describe('Error Handling', function () {
    it('handles malformed JSON in console data', function () {
        Storage::disk('data')->put('vintage-consoles.json', 'invalid json');
        
        $component = Livewire::test(Dashboard::class);
        $component->call('loadConsoles');
        
        // Should not crash and should have empty consoles array
        $component->assertSet('consoles', []);
    });
}); 