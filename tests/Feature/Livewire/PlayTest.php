<?php

use App\Livewire\Play;
use App\Models\User;
use App\Service\Tool;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function getSimpleMockConsoleData() {
    return [
        'id' => 1,
        'short_name' => 'NES',
        'name' => 'Nintendo Entertainment System',
        'games' => [
            [
                'id' => 1,
                'title' => 'Super Mario Bros.',
                'slug' => 'super-mario-bros',
                'rom' => 'mario.nes',
                'screenshots' => [
                    'images/games/mario-screenshot1.jpg',
                    'images/games/mario-screenshot2.jpg',
                    'images/games/mario-screenshot3.jpg'
                ]
            ]
        ]
    ];
}

describe('Play Component - Core Functionality', function () {
    beforeEach(function () {
        // Use in-memory SQLite database for testing
        config(['database.default' => 'testing']);
        config(['database.connections.testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);
        
        // Run migrations in memory only
        $this->artisan('migrate', ['--database' => 'testing']);
        
        // Create test user in memory database only
        \App\Models\User::factory()->create(['id' => 1, 'name' => 'John Doe']);
        
        // Use fake storage instead of real file operations
        Storage::fake('data');
        
        // Mock session data directly without database operations
        Session::put('consoles', [getSimpleMockConsoleData()]);
    });

    describe('Chat Message Management', function () {
        it('generates correct chat file path', function () {
            // Mock a console and game data
            $console = getSimpleMockConsoleData();
            $game = $console['games'][0];
            
            // Create a Play instance directly to test methods
            $play = new Play();
            $play->console = $console;
            $play->game = $game;
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('chatFilePath');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toBe('chat/1.1.json');
        });

        it('returns empty messages when file does not exist', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            // Ensure the chat file doesn't exist (using fake storage)
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('getMessages');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toEqual([]);
        });

        it('gets messages when file exists', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $messages = [
                ['id' => 1, 'message' => 'Test message', 'timestamp' => '2024-01-01 10:00:00']
            ];
            Storage::disk('data')->put('chat/1.1.json', json_encode($messages));

            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('getMessages');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toHaveCount(1)
                ->and($result[0]['message'])->toBe('Test message');
        });

        it('gets last inserted message ID', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $messages = [
                ['id' => 1, 'message' => 'First'],
                ['id' => 3, 'message' => 'Second'],
                ['id' => 2, 'message' => 'Third']
            ];
            Storage::disk('data')->put('chat/1.1.json', json_encode($messages));

            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('getLastInsertedMessageId');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toBe(3);
        });

        it('returns 0 when no messages exist', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('getLastInsertedMessageId');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toEqual(0);
        });

        it('generates new message ID correctly', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $messages = [
                ['id' => 1, 'message' => 'First'],
                ['id' => 2, 'message' => 'Second']
            ];
            Storage::disk('data')->put('chat/1.1.json', json_encode($messages));

            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('generateNewMessageId');
            $method->setAccessible(true);
            
            $result = $method->invoke($play);
            expect($result)->toBe(3);
        });

        it('updates messages file', function () {
            // Ensure clean fake storage
            Storage::fake('data');
            
            $testMessages = [
                ['id' => 1, 'message' => 'Test message', 'timestamp' => '2024-01-01 10:00:00']
            ];

            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('updateMessagesFile');
            $method->setAccessible(true);
            
            $method->invoke($play, $testMessages);

            expect(Storage::disk('data')->exists('chat/1.1.json'))->toBeTrue()
                ->and(json_decode(Storage::disk('data')->get('chat/1.1.json'), true))->toBe($testMessages);
        });
    });

    describe('Screenshot Navigation', function () {
        it('navigates screenshots correctly', function () {
            $play = new Play();
            $play->game = getSimpleMockConsoleData()['games'][0];
            $play->current_screenshot_key = 1;
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('changeScreenShot');
            $method->setAccessible(true);
            
            // Move left
            $method->invoke($play, 'left');
            expect($play->current_screenshot_key)->toBe(0);
            
            // Move right
            $method->invoke($play, 'right');
            expect($play->current_screenshot_key)->toBe(1);
        });

        it('respects boundaries in screenshot navigation', function () {
            $play = new Play();
            $play->game = getSimpleMockConsoleData()['games'][0];
            $play->current_screenshot_key = 0;
            
            $reflection = new ReflectionClass($play);
            $method = $reflection->getMethod('changeScreenShot');
            $method->setAccessible(true);
            
            // Try to move left from first screenshot
            $method->invoke($play, 'left');
            expect($play->current_screenshot_key)->toBe(0);
            
            // Move to last screenshot
            $play->current_screenshot_key = 2;
            $method->invoke($play, 'right');
            expect($play->current_screenshot_key)->toBe(2);
        });

        it('resets screenshot key on modal close', function () {
            $play = new Play();
            $play->current_screenshot_key = 2;
            
            $play->fixedModalClosed();
            expect($play->current_screenshot_key)->toBe(-1);
        });

        it('sets screenshot key directly', function () {
            $play = new Play();
            
            $play->screenshot(1);
            expect($play->current_screenshot_key)->toBe(1);
        });
    });

    describe('Tab and Accordion Management', function () {
        it('changes tabs correctly', function () {
            $play = new Play();
            $play->tabs = ['info' => true, 'chat' => false];
            
            $play->changeTab('chat');
            
            expect($play->tabs['info'])->toBeFalse()
                ->and($play->tabs['chat'])->toBeTrue();
        });

        it('toggles accordion sections', function () {
            $play = new Play();
            $play->accordion_toggler = ['description' => true];
            
            $play->toggle('description');
            expect($play->accordion_toggler['description'])->toBeFalse();
            
            $play->toggle('description');
            expect($play->accordion_toggler['description'])->toBeTrue();
        });

        it('handles case insensitive accordion names', function () {
            $play = new Play();
            $play->accordion_toggler = ['description' => true];
            
            $play->toggle('DESCRIPTION');
            expect($play->accordion_toggler['description'])->toBeFalse();
        });
    });

    describe('Game URL Loading', function () {
        it('loads game URL for non-PC console', function () {
            $play = new Play();
            $play->console = getSimpleMockConsoleData();
            $play->game = getSimpleMockConsoleData()['games'][0];
            
            $play->loadGameUrl();
            expect($play->game_url)->toContain('/games/serve/nes/mario.nes');
        });

        it('does not set game URL for PC console', function () {
            $play = new Play();
            $play->console = ['short_name' => 'PC'];
            $play->game = ['rom' => 'doom.exe'];
            
            // Initialize the property first
            $reflection = new ReflectionClass($play);
            $property = $reflection->getProperty('game_url');
            $property->setAccessible(true);
            $property->setValue($play, '');
            
            $play->loadGameUrl();
            expect($play->game_url)->toBe('');
        });
    });

    describe('Event Handlers', function () {
        it('handles keyboard events', function () {
            $play = new Play();
            $play->game = getSimpleMockConsoleData()['games'][0];
            $play->current_screenshot_key = 1;
            
            $play->keydownLeft();
            expect($play->current_screenshot_key)->toBe(0);
            
            $play->keydownRight();
            expect($play->current_screenshot_key)->toBe(1);
        });
    });

    describe('Listeners', function () {
        it('has correct event listeners', function () {
            $play = new Play();
            
            // Access the protected listeners property using reflection
            $reflection = new ReflectionClass($play);
            $listenersProperty = $reflection->getProperty('listeners');
            $listenersProperty->setAccessible(true);
            $listeners = $listenersProperty->getValue($play);
            
            expect($listeners)->toContain('fixedModalClosed')
                ->and($listeners)->toContain('keydownLeft')
                ->and($listeners)->toContain('keydownRight');
        });
    });
}); 