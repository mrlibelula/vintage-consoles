<?php

use App\Livewire\JsPlayer;
use App\Service\Tool;
use Livewire\Livewire;

function getSimpleGameData() {
    return [
        'id' => 1,
        'title' => 'Super Mario Bros.',
        'rom' => 'mario.nes'
    ];
}

function getComplexGameData() {
    return [
        'id' => 99,
        'title' => 'Game with "Special" Characters & Symbols!',
        'slug' => 'special-game',
        'description' => 'A game with complex data including unicode: ñáéíóú 中文 🎮',
        'rom' => 'special_game.nes',
        'publisher' => 'Test Publisher & Co.',
        'genres' => ['Strategy', 'Simulation']
    ];
}

describe('JsPlayer Component - Core Functionality', function () {
    beforeEach(function () {
        // Use in-memory SQLite database for testing
        config(['database.default' => 'testing']);
        config(['database.connections.testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
        
        // Run migrations in memory only
        $this->artisan('migrate', ['--database' => 'testing']);
    });
    
    describe('Component Initialization', function () {
        it('can be rendered with valid game data', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ])
            ->assertOk()
            ->assertViewIs('livewire.js-player')
            ->assertViewHas('title', 'Super Mario Bros.')
            ->assertViewHas('short_name', 'nes')
            ->assertViewHas('game_id', 1);
        });

        it('initializes properties correctly', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('title'))->toBe('Super Mario Bros.')
                ->and($component->get('short_name'))->toBe('nes')
                ->and($component->get('game_id'))->toBe(1)
                ->and($component->get('game_url'))->toContain('games/nes/mario.nes');
        });

        it('generates correct game URLs for different consoles', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $consoles = ['nes', 'snes', 'arcade', 'atari2600'];

            foreach ($consoles as $console) {
                $component = Livewire::test(JsPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => $console
                ]);

                expect($component->get('short_name'))->toBe($console)
                    ->and($component->get('game_url'))->toContain("games/{$console}/");
            }
        });
    });

    describe('Data Processing', function () {
        it('correctly decodes game data', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('title'))->toBe($gameData['title'])
                ->and($component->get('game_id'))->toBe($gameData['id']);
        });

        it('handles complex game data with special characters', function () {
            $gameData = getComplexGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('title'))->toBe('Game with "Special" Characters & Symbols!')
                ->and($component->get('game_id'))->toBe(99);
        });

        it('handles unicode characters correctly', function () {
            $unicodeGameData = [
                'id' => 42,
                'title' => '游戏标题 Jüego Títûlö 🎮',
                'rom' => 'unicode-game.nes'
            ];

            $encodedGame = Tool::encode(json_encode($unicodeGameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('title'))->toBe('游戏标题 Jüego Títûlö 🎮')
                ->and($component->get('game_id'))->toBe(42);
        });
    });

    describe('URL Generation', function () {
        it('generates correct URLs for different file types', function () {
            $fileTypes = [
                'mario.nes' => 'nes',
                'zelda.smc' => 'snes',
                'pacman.zip' => 'arcade',
                'asteroid.bin' => 'atari2600'
            ];

            foreach ($fileTypes as $rom => $console) {
                $gameData = [
                    'id' => 1,
                    'title' => 'Test Game',
                    'rom' => $rom
                ];

                $encodedGame = Tool::encode(json_encode($gameData));

                $component = Livewire::test(JsPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => $console
                ]);

                expect($component->get('game_url'))->toContain("games/{$console}/{$rom}");
            }
        });

        it('handles ROM files with special names', function () {
            $gameData = [
                'id' => 1,
                'title' => 'Special ROM',
                'rom' => 'Game_With-Special.Characters.nes'
            ];

            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('game_url'))->toContain('Game_With-Special.Characters.nes');
        });
    });

    describe('Component Properties', function () {
        it('has all expected public properties', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->instance())->toHaveProperty('title')
                ->and($component->instance())->toHaveProperty('short_name')
                ->and($component->instance())->toHaveProperty('game_url')
                ->and($component->instance())->toHaveProperty('game_id');
        });

        it('properties are accessible from view', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ])
            ->assertViewHas('title')
            ->assertViewHas('short_name')
            ->assertViewHas('game_url')
            ->assertViewHas('game_id');
        });
    });

    describe('Encoding/Decoding', function () {
        it('maintains data integrity through encode/decode cycle', function () {
            $originalData = getComplexGameData();
            $encodedData = Tool::encode(json_encode($originalData));
            $decodedData = json_decode(Tool::decode($encodedData), true);

            expect($decodedData)->toBe($originalData);
        });

        it('works with different encoding methods', function () {
            $gameData = getSimpleGameData();
            
            // Test base64 encoding (default)
            $encodedGame = Tool::encode(json_encode($gameData), 'base_64');

            $component = Livewire::test(JsPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'nes'
            ]);

            expect($component->get('title'))->toBe('Super Mario Bros.');
        });
    });

    describe('Mount Parameters', function () {
        it('handles different console names', function () {
            $gameData = getSimpleGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $consoleNames = ['nes', 'snes', 'arcade', 'atari2600', 'pc'];

            foreach ($consoleNames as $consoleName) {
                $component = Livewire::test(JsPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => $consoleName
                ]);

                expect($component->get('short_name'))->toBe($consoleName);
            }
        });

        it('handles string and numeric IDs', function () {
            $gameDataNumeric = ['id' => 123, 'title' => 'Numeric ID', 'rom' => 'numeric.nes'];
            $gameDataString = ['id' => "456", 'title' => 'String ID', 'rom' => 'string.nes'];

            foreach ([$gameDataNumeric, $gameDataString] as $gameData) {
                $encodedGame = Tool::encode(json_encode($gameData));

                $component = Livewire::test(JsPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => 'nes'
                ]);

                expect($component->get('game_id'))->toBe($gameData['id']);
            }
        });
    });
}); 