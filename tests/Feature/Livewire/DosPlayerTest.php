<?php

use App\Livewire\DosPlayer;
use App\Service\Tool;
use Livewire\Livewire;

function getSimpleDosGameData() {
    return [
        'id' => 10,
        'title' => 'Doom',
        'rom' => 'doom.exe'
    ];
}

function getComplexDosGameData() {
    return [
        'id' => 99,
        'title' => 'Game with "Special" Characters & Symbols!',
        'slug' => 'special-characters-game',
        'description' => 'A game with complex data including unicode: ñáéíóú 中文 🎮',
        'rom' => 'special_game.exe',
        'publisher' => 'Test Publisher & Co.',
        'genres' => ['Strategy', 'Simulation'],
        'system_requirements' => [
            'memory' => '4MB RAM',
            'processor' => '486DX-33'
        ]
    ];
}

describe('DosPlayer Component - Core Functionality', function () {
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
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ])
            ->assertOk()
            ->assertViewIs('livewire.dos-player')
            ->assertViewHas('enc_json_game', $encodedGame)
            ->assertViewHas('console_short_name', 'pc')
            ->assertViewHas('game');
        });

        it('initializes properties correctly', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('enc_json_game'))->toBe($encodedGame)
                ->and($component->get('console_short_name'))->toBe('pc')
                ->and($component->get('game'))->toBeArray()
                ->and($component->get('game')['title'])->toBe('Doom')
                ->and($component->get('game')['id'])->toBe(10);
        });

        it('decodes game data correctly', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $decodedGame = $component->get('game');
            expect($decodedGame['title'])->toBe($gameData['title'])
                ->and($decodedGame['id'])->toBe($gameData['id'])
                ->and($decodedGame['rom'])->toBe($gameData['rom']);
        });

        it('stores original encoded data', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('enc_json_game'))->toBe($encodedGame);
        });
    });

    describe('Data Processing', function () {
        it('handles complex game data structures', function () {
            $gameData = getComplexDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $decodedGame = $component->get('game');
            expect($decodedGame['title'])->toBe('Game with "Special" Characters & Symbols!')
                ->and($decodedGame['publisher'])->toBe('Test Publisher & Co.')
                ->and($decodedGame['genres'])->toContain('Strategy')
                ->and($decodedGame['system_requirements']['memory'])->toBe('4MB RAM');
        });

        it('preserves unicode characters', function () {
            $unicodeGameData = [
                'id' => 88,
                'title' => '游戏标题 Jüego Títûlö 🎮',
                'description' => 'Description with unicode: ñáéíóú 中文字符 🕹️',
                'rom' => 'unicode_game.exe',
                'publisher' => 'Üñicødé Püblisher'
            ];

            $encodedGame = Tool::encode(json_encode($unicodeGameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $decodedGame = $component->get('game');
            expect($decodedGame['title'])->toBe('游戏标题 Jüego Títûlö 🎮')
                ->and($decodedGame['description'])->toBe('Description with unicode: ñáéíóú 中文字符 🕹️')
                ->and($decodedGame['publisher'])->toBe('Üñicødé Püblisher');
        });

        it('handles minimal game data', function () {
            $minimalGameData = [
                'id' => 1,
                'title' => 'Minimal Game',
                'rom' => 'minimal.exe'
            ];

            $encodedGame = Tool::encode(json_encode($minimalGameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $decodedGame = $component->get('game');
            expect($decodedGame['id'])->toBe(1)
                ->and($decodedGame['title'])->toBe('Minimal Game')
                ->and($decodedGame['rom'])->toBe('minimal.exe');
        });

        it('handles nested data structures', function () {
            $nestedGameData = [
                'id' => 1,
                'title' => 'Nested Data Game',
                'rom' => 'nested.exe',
                'metadata' => [
                    'technical' => [
                        'engine' => 'Custom Engine',
                        'graphics' => ['VGA', 'SVGA']
                    ]
                ]
            ];

            $encodedGame = Tool::encode(json_encode($nestedGameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $decodedGame = $component->get('game');
            expect($decodedGame['metadata']['technical']['engine'])->toBe('Custom Engine')
                ->and($decodedGame['metadata']['technical']['graphics'])->toContain('VGA');
        });
    });

    describe('Encoding and Decoding', function () {
        it('uses Tool service for decoding', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('game'))->toBe($gameData);
        });

        it('maintains data integrity through encode/decode cycle', function () {
            $originalData = getComplexDosGameData();
            $encodedData = Tool::encode(json_encode($originalData));
            $decodedData = json_decode(Tool::decode($encodedData), true);

            expect($decodedData)->toBe($originalData);

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedData,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('game'))->toBe($originalData);
        });

        it('handles base64 encoded data', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData), 'base_64');

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('game')['title'])->toBe('Doom');
        });
    });

    describe('Console Name Handling', function () {
        it('handles different console names', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $consoleNames = ['pc', 'dos', 'PC', 'DOS'];

            foreach ($consoleNames as $consoleName) {
                $component = Livewire::test(DosPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => $consoleName
                ]);

                expect($component->get('console_short_name'))->toBe($consoleName);
            }
        });

        it('handles empty console name', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => ''
            ]);

            expect($component->get('console_short_name'))->toBe('');
        });
    });

    describe('Component Properties', function () {
        it('has all expected public properties', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->instance())->toHaveProperty('enc_json_game')
                ->and($component->instance())->toHaveProperty('console_short_name')
                ->and($component->instance())->toHaveProperty('game');
        });

        it('properties are accessible from view', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ])
            ->assertViewHas('enc_json_game')
            ->assertViewHas('console_short_name')
            ->assertViewHas('game');
        });

        it('game property is accessible as array', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            $game = $component->get('game');
            expect($game)->toBeArray()
                ->and(isset($game['title']))->toBeTrue()
                ->and(isset($game['id']))->toBeTrue();
        });
    });

    describe('Real-world Scenarios', function () {
        it('handles typical DOS game structure', function () {
            $typicalDosGame = [
                'id' => 15,
                'title' => 'Commander Keen',
                'slug' => 'commander-keen',
                'description' => 'Platform adventure game',
                'release_year' => '1990',
                'rating' => '0.85',
                'rom' => 'keen.exe',
                'publisher' => 'Apogee Software',
                'genres' => ['Platform', 'Adventure']
            ];

            $encodedGame = Tool::encode(json_encode($typicalDosGame));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'pc'
            ]);

            expect($component->get('game')['title'])->toBe('Commander Keen')
                ->and($component->get('game')['publisher'])->toBe('Apogee Software')
                ->and($component->get('game')['genres'])->toContain('Platform');
        });

        it('works with different DOS game genres', function () {
            $genres = ['Strategy', 'RPG', 'Adventure', 'Action', 'Simulation'];

            foreach ($genres as $genre) {
                $gameData = [
                    'id' => rand(1, 1000),
                    'title' => "{$genre} Game",
                    'rom' => strtolower($genre) . '.exe',
                    'genres' => [$genre]
                ];

                $encodedGame = Tool::encode(json_encode($gameData));

                $component = Livewire::test(DosPlayer::class, [
                    'enc_json_game' => $encodedGame,
                    'console_short_name' => 'pc'
                ]);

                expect($component->get('game')['genres'])->toContain($genre);
            }
        });
    });

    describe('Mount Parameters', function () {
        it('accepts both required parameters', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => 'custom_console'
            ]);

            expect($component->get('enc_json_game'))->toBe($encodedGame)
                ->and($component->get('console_short_name'))->toBe('custom_console');
        });

        it('handles long console names', function () {
            $gameData = getSimpleDosGameData();
            $encodedGame = Tool::encode(json_encode($gameData));
            $longConsoleName = 'very_long_console_name_with_underscores_123';

            $component = Livewire::test(DosPlayer::class, [
                'enc_json_game' => $encodedGame,
                'console_short_name' => $longConsoleName
            ]);

            expect($component->get('console_short_name'))->toBe($longConsoleName);
        });
    });
}); 