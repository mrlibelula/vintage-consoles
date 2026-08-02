<?php

use App\Livewire\Play;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────────────────────
// Seed helpers
// ─────────────────────────────────────────────────────────────────────────────

function playNesConsole(): Console
{
    return Console::factory()->create([
        'id'         => 1,
        'short_name' => 'nes',
        'long_name'  => 'Nintendo Entertainment System',
    ]);
}

function playNesGame(Console $console, array $attrs = []): Game
{
    return Game::factory()->create(array_merge([
        'console_id'        => $console->id,
        'title'             => 'Super Mario Bros.',
        'slug'              => 'super-mario-bros',
        'rom'               => 'mario.nes',
        'save_state_support'=> true,
    ], $attrs));
}

beforeEach(function () {
    Storage::fake('data');
});

// ─────────────────────────────────────────────────────────────────────────────
// Mounting
// ─────────────────────────────────────────────────────────────────────────────

describe('Play component mounting', function () {
    it('mounts successfully with a valid console and game', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])->assertStatus(200);
    });

    it('aborts with 404 for an unknown game slug', function () {
        playNesConsole();

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'non-existent-game',
        ])->assertStatus(404);
    });

    it('renders the play view', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])->assertViewIs('livewire.play');
    });

    it('sets accordion togglers to true by default', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->assertSet('accordion_toggler.description', true)
        ->assertSet('accordion_toggler.screenshots', true);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Chat Message Management
// ─────────────────────────────────────────────────────────────────────────────

describe('Chat message management', function () {
    it('generates the correct chat file path', function () {
        $console = playNesConsole();
        $game    = playNesGame($console);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->instance()->chatFilePath())
            ->toBe("chat/{$console->id}.{$game->id}.json");
    });

    it('returns empty array when no chat file exists', function () {
        $console = playNesConsole();
        playNesGame($console);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->instance()->getMessages())->toEqual([]);
    });

    it('returns messages when chat file exists', function () {
        $console  = playNesConsole();
        $game     = playNesGame($console);
        $messages = [
            ['id' => 1, 'message' => 'Hello!', 'timestamp' => '2024-01-01 10:00:00'],
        ];

        Storage::disk('data')->put("chat/{$console->id}.{$game->id}.json", json_encode($messages));

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        $result = $component->instance()->getMessages();

        expect($result)->toHaveCount(1)
            ->and($result[0]['message'])->toBe('Hello!');
    });

    it('returns 0 for last message ID when no messages exist', function () {
        $console = playNesConsole();
        playNesGame($console);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->instance()->getLastInsertedMessageId())->toEqual(0);
    });

    it('returns the highest message ID from sorted messages', function () {
        $console  = playNesConsole();
        $game     = playNesGame($console);
        $messages = [
            ['id' => 1, 'message' => 'First',  'timestamp' => '2024-01-01'],
            ['id' => 3, 'message' => 'Third',  'timestamp' => '2024-01-01'],
            ['id' => 2, 'message' => 'Second', 'timestamp' => '2024-01-01'],
        ];

        Storage::disk('data')->put("chat/{$console->id}.{$game->id}.json", json_encode($messages));

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->instance()->getLastInsertedMessageId())->toBe(3);
    });

    it('generates next message ID as lastId + 1', function () {
        $console  = playNesConsole();
        $game     = playNesGame($console);
        $messages = [
            ['id' => 1, 'message' => 'First', 'timestamp' => '2024-01-01'],
            ['id' => 2, 'message' => 'Second', 'timestamp' => '2024-01-01'],
        ];

        Storage::disk('data')->put("chat/{$console->id}.{$game->id}.json", json_encode($messages));

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->instance()->generateNewMessageId())->toBe(3);
    });

    it('writes messages to the chat file', function () {
        $console  = playNesConsole();
        $game     = playNesGame($console);
        $messages = [['id' => 1, 'message' => 'Test', 'timestamp' => '2024-01-01 10:00:00']];

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        $component->instance()->updateMessagesFile($messages);

        $path = "chat/{$console->id}.{$game->id}.json";

        expect(Storage::disk('data')->exists($path))->toBeTrue()
            ->and(json_decode(Storage::disk('data')->get($path), true))->toBe($messages);
    });

    it('appends a new message when updatedInput fires', function () {
        $console = playNesConsole();
        playNesGame($console);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Play::class, [
                'console_short_name' => 'nes',
                'game_title_slug'    => 'super-mario-bros',
            ])
            ->set('input', 'Hello world')
            ->assertSet('input', '');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Accordion and Tab Management
// ─────────────────────────────────────────────────────────────────────────────

describe('Accordion management', function () {
    it('toggles an accordion section on and off', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->assertSet('accordion_toggler.description', true)
        ->call('toggle', 'description')
        ->assertSet('accordion_toggler.description', false)
        ->call('toggle', 'description')
        ->assertSet('accordion_toggler.description', true);
    });

    it('handles case-insensitive accordion names', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->call('toggle', 'DESCRIPTION')
        ->assertSet('accordion_toggler.description', false);
    });
});

describe('Tab management', function () {
    it('switches to media tab and resets info tab', function () {
        $console = playNesConsole();
        playNesGame($console, [
            'walkthrough_videos' => [
                ['title' => 'Guide', 'youtube_id' => 'dQw4w9WgXcQ'],
            ],
        ]);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->assertSet('tabs.info', true)
        ->assertSet('tabs.media', false)
        ->call('changeTab', 'media')
        ->assertSet('tabs.info', false)
        ->assertSet('tabs.media', true);
    });

    it('can switch back to info tab', function () {
        $console = playNesConsole();
        playNesGame($console, [
            'walkthrough_videos' => [
                ['title' => 'Guide', 'youtube_id' => 'dQw4w9WgXcQ'],
            ],
        ]);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->call('changeTab', 'media')
        ->call('changeTab', 'info')
        ->assertSet('tabs.info', true)
        ->assertSet('tabs.media', false);
    });

    it('ignores the removed chat tab', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
        ->assertSet('tabs.info', true)
        ->call('changeTab', 'chat')
        ->assertSet('tabs.info', true)
        ->assertSet('tabs.media', false);
    });

    it('defaults to info tab even when media exists', function () {
        $console = playNesConsole();
        playNesGame($console, [
            'walkthrough_videos' => [
                ['title' => 'Guide', 'youtube_id' => 'dQw4w9WgXcQ'],
            ],
        ]);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
            ->assertSet('igdb.has_media', true)
            ->assertSet('tabs.info', true)
            ->assertSet('tabs.media', false)
            ->assertSee('Info')
            ->assertSee('Media')
            ->assertSee('live chat room');
    });

    it('shows artworks in the media tab', function () {
        $console = playNesConsole();
        playNesGame($console, [
            'igdb_response' => [
                'artworks' => [
                    ['image_id' => 'ar123'],
                ],
            ],
        ]);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
            ->assertSet('igdb.has_media', true)
            ->assertDontSee('Artworks')
            ->call('changeTab', 'media')
            ->assertSee('Artworks');
    });

    it('renders session bar and hotkeys under the emulator', function () {
        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
            ->assertSee('Hotkeys')
            ->assertSee('nes');
    });

    it('renders arcade controls only for arcade consoles', function () {
        $arcade = Console::factory()->create([
            'short_name' => 'arcade',
            'long_name' => 'Arcade',
        ]);
        Game::factory()->create([
            'console_id' => $arcade->id,
            'title' => 'Galaga',
            'slug' => 'galaga',
            'rom' => 'galaga.zip',
        ]);

        Livewire::test(Play::class, [
            'console_short_name' => 'arcade',
            'game_title_slug' => 'galaga',
        ])
            ->assertSee('Insert Coin')
            ->assertSee('Start')
            ->assertSee('Hotkeys');

        $console = playNesConsole();
        playNesGame($console);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug' => 'super-mario-bros',
        ])->assertDontSee('Insert Coin');
    });

    it('renders live chat below the emulator for guests when messages omit user_id', function () {
        $console  = playNesConsole();
        $game     = playNesGame($console);
        $messages = [
            ['id' => 1, 'message' => 'Hello from legacy chat!', 'timestamp' => '2024-01-01 10:00:00'],
        ];

        Storage::disk('data')->put("chat/{$console->id}.{$game->id}.json", json_encode($messages));

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
            ->assertSee('Hello from legacy chat!')
            ->assertSee('Guest')
            ->assertSee('live chat room');
    });
});

describe('Live chat input', function () {
    it('clears input after sending a message', function () {
        $user    = User::factory()->create();
        $console = playNesConsole();
        playNesGame($console);

        $this->actingAs($user);

        Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ])
            ->set('input', 'Hello from me')
            ->assertSet('input', '');
    });
});
// ─────────────────────────────────────────────────────────────────────────────
// Game URL Loading
// ─────────────────────────────────────────────────────────────────────────────

describe('Game URL loading', function () {
    it('sets game_url for a non-PC console', function () {
        $console = playNesConsole();
        playNesGame($console);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->get('game_url'))->toContain('games/serve/nes/mario.nes');
    });

    it('leaves game_url empty for a PC (ms-dos) game', function () {
        $pcConsole = Console::factory()->create([
            'short_name' => 'pc',
            'long_name'  => 'PC / MS-DOS',
        ]);
        Game::factory()->create([
            'console_id' => $pcConsole->id,
            'title'      => 'Doom',
            'slug'       => 'doom',
            'rom'        => null,
        ]);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'pc',
            'game_title_slug'    => 'doom',
        ]);

        expect($component->get('game_url'))->toBe('');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// Save Slot Counting
// ─────────────────────────────────────────────────────────────────────────────

describe('Save slot counting', function () {
    it('shows 0 save slots used when not authenticated', function () {
        $console = playNesConsole();
        playNesGame($console);

        $component = Livewire::test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->get('save_slots_used'))->toBe(0);
    });

    it('shows 0 save slots used for games without save state support', function () {
        $console = playNesConsole();
        playNesGame($console, ['save_state_support' => false]);
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)->test(Play::class, [
            'console_short_name' => 'nes',
            'game_title_slug'    => 'super-mario-bros',
        ]);

        expect($component->get('save_slots_used'))->toBe(0);
    });
});
