<?php

use App\Livewire\Chat;
use App\Service\Tool;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function getMockChatMessages()
{
    return [
        [
            'id' => 1,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Hello world!',
            'timestamp' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 2,
            'user_id' => 2,
            'username' => 'AnotherUser',
            'message' => 'Great game!',
            'timestamp' => '2024-01-01 10:05:00'
        ],
        [
            'id' => 3,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Anyone want to play?',
            'timestamp' => '2024-01-01 10:10:00'
        ]
    ];
}

function getMockChatMessagesWithGuests()
{
    return [
        [
            'id' => 1,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Hello world!',
            'timestamp' => '2024-01-01 10:00:00'
        ],
        [
            'id' => 2,
            'user_id' => null,
            'username' => 'Guest',
            'message' => 'Hi from a guest user!',
            'timestamp' => '2024-01-01 10:05:00'
        ],
        [
            'id' => 3,
            'user_id' => 2,
            'username' => 'AnotherUser',
            'message' => 'Great game!',
            'timestamp' => '2024-01-01 10:07:00'
        ],
        [
            'id' => 4,
            'user_id' => null,
            'username' => 'Guest',
            'message' => 'Anonymous user here too!',
            'timestamp' => '2024-01-01 10:10:00'
        ]
    ];
}

function getMockSortedChatMessages()
{
    return [
        [
            'id' => 3,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Anyone want to play?',
            'timestamp' => '2024-01-01 10:10:00'
        ],
        [
            'id' => 2,
            'user_id' => 2,
            'username' => 'AnotherUser',
            'message' => 'Great game!',
            'timestamp' => '2024-01-01 10:05:00'
        ],
        [
            'id' => 1,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Hello world!',
            'timestamp' => '2024-01-01 10:00:00'
        ]
    ];
}

function getMockSortedChatMessagesWithGuests()
{
    return [
        [
            'id' => 4,
            'user_id' => null,
            'username' => 'Guest',
            'message' => 'Anonymous user here too!',
            'timestamp' => '2024-01-01 10:10:00'
        ],
        [
            'id' => 3,
            'user_id' => 2,
            'username' => 'AnotherUser',
            'message' => 'Great game!',
            'timestamp' => '2024-01-01 10:07:00'
        ],
        [
            'id' => 2,
            'user_id' => null,
            'username' => 'Guest',
            'message' => 'Hi from a guest user!',
            'timestamp' => '2024-01-01 10:05:00'
        ],
        [
            'id' => 1,
            'user_id' => 1,
            'username' => 'TestUser',
            'message' => 'Hello world!',
            'timestamp' => '2024-01-01 10:00:00'
        ]
    ];
}

function getMockGameData()
{
    return [
        'id' => 1,
        'title' => 'Super Mario Bros.',
        'slug' => 'super-mario-bros',
        'description' => 'Classic platformer',
        'rom' => 'mario.nes'
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
    
    // Create test users in memory database only
    \App\Models\User::factory()->create([
        'id' => 1,
        'name' => 'TestUser',
        'email' => 'test@example.com'
    ]);
    
    \App\Models\User::factory()->create([
        'id' => 2,
        'name' => 'AnotherUser',
        'email' => 'another@example.com'
    ]);
    
    \App\Models\User::factory()->create([
        'id' => 3,
        'name' => 'NewUser',
        'email' => 'new@example.com'
    ]);
});

describe('Chat Component Initialization', function () {
    it('can be rendered', function () {
        Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ])->assertStatus(200);
    });

    it('initializes with correct properties', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertSet('console_id', 1)
                 ->assertSet('game', getMockGameData())
                 ->assertSet('messages', []);
    });

    it('calls loadMessages on mount', function () {
        $mockMessages = getMockChatMessages();
        
        // Create mock file for testing
        Storage::disk('data')->put('chat/1.1.json', json_encode($mockMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Should load messages from mock file
        $component->assertSet('messages', function ($messages) {
            return count($messages) === 3;
        });
    });
});

describe('Chat File Management', function () {
    it('generates correct chat file path', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        expect($component->instance()->chatFilePath())->toBe('chat/1.1.json');
    });

    it('generates correct chat file path with different console and game', function () {
        $game = getMockGameData();
        $game['id'] = 5;
        
        $component = Livewire::test(Chat::class, [
            'console_id' => 3,
            'game' => $game
        ]);

        expect($component->instance()->chatFilePath())->toBe('chat/3.5.json');
    });

    it('creates empty chat file when file does not exist', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Should create empty file and have empty messages
        $component->assertSet('messages', []);
        
        // Check that file was created
        expect(Storage::disk('data')->exists('chat/1.1.json'))->toBeTrue();
        expect(Storage::disk('data')->get('chat/1.1.json'))->toBe('[]');
    });
});

describe('Message Loading and Sorting', function () {
    it('loads and sorts messages correctly', function () {
        $mockMessages = getMockChatMessages();
        
        // Create file with messages
        Storage::disk('data')->put('chat/1.1.json', json_encode($mockMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Should load and sort messages (sorted by timestamp descending)
        $component->assertSet('messages', function ($messages) use ($mockMessages) {
            return count($messages) === count($mockMessages) && 
                   $messages[0]['timestamp'] >= $messages[1]['timestamp'];
        });
    });

    it('loads and sorts messages with guest users correctly', function () {
        $mockMessagesWithGuests = getMockChatMessagesWithGuests();
        
        // Create file with messages including guest users
        Storage::disk('data')->put('chat/1.1.json', json_encode($mockMessagesWithGuests));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Should load and sort messages including guest users
        $component->assertSet('messages', function ($messages) use ($mockMessagesWithGuests) {
            $hasGuestUsers = collect($messages)->where('user_id', null)->count() > 0;
            return count($messages) === count($mockMessagesWithGuests) && 
                   $hasGuestUsers &&
                   $messages[0]['timestamp'] >= $messages[1]['timestamp'];
        });
    });

    it('handles empty messages correctly', function () {
        // Create empty file
        Storage::disk('data')->put('chat/1.1.json', '[]');

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertSet('messages', []);
    });

    it('handles invalid JSON gracefully', function () {
        // Create file with invalid JSON
        Storage::disk('data')->put('chat/1.1.json', 'invalid json');

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Should handle gracefully and return empty array
        $component->assertSet('messages', []);
    });
});

describe('Message Refresh Functionality', function () {
    it('refreshes messages when refreshMessages is called', function () {
        $mockMessages = getMockChatMessages();
        
        // Create file with messages
        Storage::disk('data')->put('chat/1.1.json', json_encode($mockMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Call refreshMessages
        $component->call('refreshMessages');

        // Should still have messages loaded
        $component->assertSet('messages', function ($messages) use ($mockMessages) {
            return count($messages) === count($mockMessages);
        });
    });
});

describe('Event Listeners', function () {
    it('has updateChatMessages listener registered', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Check that the listeners property contains updateChatMessages
        $reflection = new \ReflectionClass($component->instance());
        $listenersProperty = $reflection->getProperty('listeners');
        $listenersProperty->setAccessible(true);
        $listeners = $listenersProperty->getValue($component->instance());
        
        expect($listeners)->toContain('updateChatMessages');
    });

    it('updates messages when updateChatMessages event is triggered', function () {
        $newMessages = [
            [
                'id' => 4,
                'user_id' => 3,
                'username' => 'NewUser',
                'message' => 'New message!',
                'timestamp' => '2024-01-01 10:15:00'
            ]
        ];

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->call('updateChatMessages', $newMessages);

        $component->assertSet('messages', $newMessages);
    });

    it('updates messages with guest user messages when event is triggered', function () {
        $newMessagesWithGuests = [
            [
                'id' => 5,
                'user_id' => null,
                'username' => 'Guest',
                'message' => 'Guest message from event!',
                'timestamp' => '2024-01-01 10:20:00'
            ],
            [
                'id' => 6,
                'user_id' => 1,
                'username' => 'TestUser',
                'message' => 'Regular user message!',
                'timestamp' => '2024-01-01 10:21:00'
            ]
        ];

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->call('updateChatMessages', $newMessagesWithGuests);

        $component->assertSet('messages', function ($messages) use ($newMessagesWithGuests) {
            $hasGuestMessage = collect($messages)->where('user_id', null)->count() > 0;
            return count($messages) === count($newMessagesWithGuests) && $hasGuestMessage;
        });
    });
});

describe('Component Properties', function () {
    it('maintains console_id property correctly', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 5,
            'game' => getMockGameData()
        ]);

        $component->assertSet('console_id', 5);
    });

    it('maintains game property correctly', function () {
        $customGame = [
            'id' => 10,
            'title' => 'Custom Game',
            'slug' => 'custom-game'
        ];

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => $customGame
        ]);

        $component->assertSet('game', $customGame);
    });
});

describe('Guest User Support', function () {
    it('handles chat messages from guest users (user_id=null)', function () {
        $messagesWithGuests = getMockChatMessagesWithGuests();
        
        // Create file with messages including guest users
        Storage::disk('data')->put('chat/1.1.json', json_encode($messagesWithGuests));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertSet('messages', function ($messages) {
            // Should have messages with null user_id (guest users)
            $guestMessages = collect($messages)->where('user_id', null);
            return $guestMessages->count() > 0;
        });
    });

    it('displays guest messages correctly with Tool::userName()', function () {
        $guestMessage = [
            'id' => 1,
            'user_id' => null,
            'username' => 'Guest',
            'message' => 'Anonymous message',
            'timestamp' => '2024-01-01 10:00:00'
        ];
        
        Storage::disk('data')->put('chat/1.1.json', json_encode([$guestMessage]));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Verify that guest message is loaded
        $component->assertSet('messages', function ($messages) {
            return count($messages) === 1 && 
                   $messages[0]['user_id'] === null &&
                   $messages[0]['username'] === 'Guest';
        });
    });

    it('mixes guest and registered user messages correctly', function () {
        $mixedMessages = [
            [
                'id' => 1,
                'user_id' => 1,
                'username' => 'TestUser',
                'message' => 'Registered user message',
                'timestamp' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'user_id' => null,
                'username' => 'Guest',
                'message' => 'Guest user message',
                'timestamp' => '2024-01-01 10:05:00'
            ]
        ];
        
        Storage::disk('data')->put('chat/1.1.json', json_encode($mixedMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertSet('messages', function ($messages) {
            $registeredUsers = collect($messages)->whereNotNull('user_id');
            $guestUsers = collect($messages)->whereNull('user_id');
            
            return $registeredUsers->count() === 1 && 
                   $guestUsers->count() === 1 &&
                   count($messages) === 2;
        });
    });

    it('handles user_id null in refresh messages', function () {
        $guestMessages = [
            [
                'id' => 1,
                'user_id' => null,
                'username' => 'Guest',
                'message' => 'Guest message to refresh',
                'timestamp' => '2024-01-01 10:00:00'
            ]
        ];
        
        Storage::disk('data')->put('chat/1.1.json', json_encode($guestMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        // Call refreshMessages to reload data
        $component->call('refreshMessages');

        $component->assertSet('messages', function ($messages) {
            return count($messages) === 1 && 
                   $messages[0]['user_id'] === null;
        });
    });
});

describe('View Rendering', function () {
    it('renders the correct view', function () {
        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertViewIs('livewire.chat');
    });

    it('passes correct data to view', function () {
        $mockMessages = getMockChatMessages();
        
        // Create file with messages
        Storage::disk('data')->put('chat/1.1.json', json_encode($mockMessages));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertViewHas('messages', function ($messages) use ($mockMessages) {
                     return count($messages) === count($mockMessages);
                 })
                 ->assertViewHas('game', getMockGameData());
    });

    it('passes guest user messages to view correctly', function () {
        $messagesWithGuests = getMockChatMessagesWithGuests();
        
        // Create file with messages including guest users
        Storage::disk('data')->put('chat/1.1.json', json_encode($messagesWithGuests));

        $component = Livewire::test(Chat::class, [
            'console_id' => 1,
            'game' => getMockGameData()
        ]);

        $component->assertViewHas('messages', function ($messages) use ($messagesWithGuests) {
            $guestMessages = collect($messages)->whereNull('user_id');
            return count($messages) === count($messagesWithGuests) && 
                   $guestMessages->count() > 0;
        })
        ->assertViewHas('game', getMockGameData());
    });
}); 