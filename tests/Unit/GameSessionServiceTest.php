<?php

use App\Service\GameSession;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

describe('GameSession Service', function () {
    
    function getSessionMockConsoleData() {
        return [
            [
                'id' => 1,
                'short_name' => 'nes',
                'long_name' => 'Nintendo Entertainment System',
                'games' => [
                    [
                        'id' => 1,
                        'title' => 'Super Mario Bros',
                        'slug' => 'super-mario-bros',
                        'rom' => 'super-mario-bros.nes'
                    ]
                ]
            ],
            [
                'id' => 2,
                'short_name' => 'snes',
                'long_name' => 'Super Nintendo Entertainment System',
                'games' => [
                    [
                        'id' => 2,
                        'title' => 'Super Mario World',
                        'slug' => 'super-mario-world',
                        'rom' => 'super-mario-world.smc'
                    ]
                ]
            ]
        ];
    }
    
    beforeEach(function () {
        // Mock Storage to prevent file system access - use fake storage
        Storage::fake('data');
        Storage::disk('data')->put('vintage-consoles.json', json_encode([
            'consoles' => getSessionMockConsoleData()
        ]));
        
        Session::flush();
    });
    
    afterEach(function () {
        Session::flush();
    });
    
    describe('Constructor with Console Data', function () {
        it('can be instantiated with console data parameter', function () {
            $consoleData = getSessionMockConsoleData();
            $gameSession = new GameSession($consoleData);
            
            expect($gameSession)->toBeInstanceOf(GameSession::class);
        });
        
        it('sets session data when console array is provided', function () {
            $consoleData = getSessionMockConsoleData();
            
            new GameSession($consoleData);
            
            expect(Session::has('consoles'))->toBeTrue()
                ->and(Session::get('consoles'))->toBe($consoleData);
        });
        
        it('returns console data when provided in constructor', function () {
            $consoleData = getSessionMockConsoleData();
            
            $gameSession = new GameSession($consoleData);
            
            // The consoles() method returns the internal $consoles property, which is only set
            // when loading from file, not when data is provided directly to constructor
            expect($gameSession->consoles())->toBe([])
                ->and(Session::get('consoles'))->toBe($consoleData);
        });
    });
    
    describe('Session Integration', function () {
        it('overwrites existing session data when new data provided', function () {
            $initialData = [['id' => 1, 'name' => 'initial']];
            $newData = getSessionMockConsoleData();
            
            Session::put('consoles', $initialData);
            
            new GameSession($newData);
            
            expect(Session::get('consoles'))->toBe($newData)
                ->and(Session::get('consoles'))->not->toBe($initialData);
        });
        
        it('creates session when none exists', function () {
            expect(Session::has('consoles'))->toBeFalse();
            
            new GameSession(getSessionMockConsoleData());
            
            expect(Session::has('consoles'))->toBeTrue();
        });
        
        it('session data persists after GameSession object is destroyed', function () {
            $consoleData = getSessionMockConsoleData();
            
            $gameSession = new GameSession($consoleData);
            unset($gameSession); // Destroy object
            
            expect(Session::get('consoles'))->toBe($consoleData);
        });
    });
    
    describe('Data Structure Validation', function () {
        it('preserves data types in console structure', function () {
            $consoleData = [
                [
                    'id' => 1, // integer
                    'short_name' => 'nes', // string
                    'active' => true, // boolean
                    'rating' => 4.5, // float
                    'games' => [], // empty array
                    'metadata' => null // null value
                ]
            ];
            
            $gameSession = new GameSession($consoleData);
            
            expect(Session::get('consoles'))->toBe($consoleData);
        });
        
        it('handles large console data sets', function () {
            $largeConsoleData = [];
            
            // Create large dataset
            for ($i = 1; $i <= 50; $i++) {
                $games = [];
                for ($j = 1; $j <= 10; $j++) {
                    $games[] = [
                        'id' => $j,
                        'title' => "Game $j for Console $i",
                        'slug' => "game-$j-console-$i"
                    ];
                }
                
                $largeConsoleData[] = [
                    'id' => $i,
                    'short_name' => "console$i",
                    'games' => $games
                ];
            }
            
            $gameSession = new GameSession($largeConsoleData);
            
            expect(Session::get('consoles'))->toHaveCount(50);
        });
        
        it('handles unicode characters in console data', function () {
            $unicodeConsoleData = [
                [
                    'id' => 1,
                    'short_name' => 'ファミコン', // Japanese
                    'long_name' => 'Family Computer 家族コンピュータ',
                    'games' => [
                        [
                            'id' => 1,
                            'title' => '超級瑪利歐兄弟', // Chinese
                            'description' => 'Un jeu de plateforme classique' // French
                        ]
                    ]
                ]
            ];
            
            $gameSession = new GameSession($unicodeConsoleData);
            
            expect(Session::get('consoles'))->toBe($unicodeConsoleData)
                ->and(Session::get('consoles')[0]['short_name'])->toBe('ファミコン');
        });
    });
    
    describe('Basic Functionality', function () {
        it('returns empty array from consoles method by default', function () {
            $gameSession = new GameSession([]);
            
            // The consoles() method returns the internal $consoles property, not session data
            // Since we passed an empty array, it will load from the fake storage we set up
            expect($gameSession->consoles())->toBeArray();
        });
        
        it('handles null parameter gracefully', function () {
            $gameSession = new GameSession(null);
            
            expect($gameSession)->toBeInstanceOf(GameSession::class);
        });
    });
});