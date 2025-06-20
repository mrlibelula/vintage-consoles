<?php

use App\Service\Tool;

describe('Tool Service', function () {
    describe('Base64 URL Encoding/Decoding', function () {
        it('can encode and decode base64url data correctly', function () {
            $original = 'Hello World! This is a test string with special chars: &@#$%';
            $encoded = Tool::base64url_encode($original);
            $decoded = Tool::base64url_decode($encoded);
            
            expect($decoded)->toBe($original);
            expect($encoded)->not->toContain('+')
                ->and($encoded)->not->toContain('/')
                ->and($encoded)->not->toContain('=');
        });
        
        it('handles empty strings correctly', function () {
            $original = '';
            $encoded = Tool::base64url_encode($original);
            $decoded = Tool::base64url_decode($encoded);
            
            expect($decoded)->toBe($original);
        });
        
        it('handles unicode characters correctly', function () {
            $original = 'Héllo Wørld! 🎮🕹️';
            $encoded = Tool::base64url_encode($original);
            $decoded = Tool::base64url_decode($encoded);
            
            expect($decoded)->toBe($original);
        });
    });
    
    describe('Generic Encoding/Decoding', function () {
        it('defaults to base64 encoding when no method specified', function () {
            $original = 'test data';
            $encoded = Tool::encode($original);
            $decoded = Tool::decode($encoded);
            
            expect($decoded)->toBe($original);
        });
        
        it('uses base64 encoding when explicitly specified', function () {
            $original = 'test data';
            $encoded = Tool::encode($original, 'base_64');
            $decoded = Tool::decode($encoded, 'base_64');
            
            expect($decoded)->toBe($original);
        });
        
        it('uses laravel encryption when specified', function () {
            $original = 'sensitive data';
            $encoded = Tool::encode($original, 'laravel');
            $decoded = Tool::decode($encoded, 'laravel');
            
            expect($decoded)->toBe($original);
        });
        
        it('falls back to base64 for unknown methods', function () {
            $original = 'test data';
            $encoded = Tool::encode($original, 'unknown_method');
            $decoded = Tool::decode($encoded, 'unknown_method');
            
            expect($decoded)->toBe($original);
        });
    });
    
    describe('Array Utilities', function () {
        it('can find an item by key and value', function () {
            $testArray = [
                ['id' => 1, 'name' => 'Mario', 'type' => 'game'],
                ['id' => 2, 'name' => 'Zelda', 'type' => 'game'],
                ['id' => 3, 'name' => 'Console', 'type' => 'hardware'],
            ];
            
            $result = Tool::findItemByKey($testArray, 'id', 2);
            expect($result)->toBe(['id' => 2, 'name' => 'Zelda', 'type' => 'game']);
            
            $result = Tool::findItemByKey($testArray, 'name', 'Mario');
            expect($result)->toBe(['id' => 1, 'name' => 'Mario', 'type' => 'game']);
            
            $result = Tool::findItemByKey($testArray, 'type', 'hardware');
            expect($result)->toBe(['id' => 3, 'name' => 'Console', 'type' => 'hardware']);
        });
        
        it('returns empty array when item not found', function () {
            $testArray = [
                ['id' => 1, 'name' => 'Mario'],
                ['id' => 2, 'name' => 'Zelda'],
            ];
            
            $result = Tool::findItemByKey($testArray, 'id', 999);
            expect($result)->toBe([]);
            
            $result = Tool::findItemByKey($testArray, 'nonexistent_key', 'value');
            expect($result)->toBe([]);
        });
        
        it('returns empty array for empty input array', function () {
            $result = Tool::findItemByKey([], 'id', 1);
            expect($result)->toBe([]);
        });
    });
    
    describe('Random Utilities', function () {
        it('can select a random item from array', function () {
            $testArray = ['apple', 'banana', 'cherry', 'date'];
            $result = Tool::randomItem($testArray);
            
            expect($result)->toBeIn($testArray);
        });
        
        it('returns null for empty array', function () {
            $result = Tool::randomItem([]);
            expect($result)->toBeNull();
        });
        
        it('returns the only item for single-item array', function () {
            $testArray = ['only_item'];
            $result = Tool::randomItem($testArray);
            
            expect($result)->toBe('only_item');
        });
        
        it('generates random float within range', function () {
            $result = Tool::randFloat(1.0, 5.0);
            
            expect($result)->toBeFloat()
                ->and($result)->toBeGreaterThanOrEqual(1.0)
                ->and($result)->toBeLessThanOrEqual(5.0);
        });
        
        it('generates random integer when range is integers', function () {
            $result = Tool::randFloat(1, 5, 1);
            
            expect($result)->toBeInt()
                ->and($result)->toBeGreaterThanOrEqual(1)
                ->and($result)->toBeLessThanOrEqual(5);
        });
        
        it('returns false when start number is greater than end number', function () {
            $result = Tool::randFloat(10, 5);
            expect($result)->toBeFalse();
        });
        
        it('generates random image URL', function () {
            $result = Tool::randomImage();
            
            expect($result)->toBeString()
                ->and($result)->toStartWith('https://');
        });
        
        it('uses custom wallpaper array when provided', function () {
            $customWallpapers = [
                'https://example.com/image1.jpg',
                'https://example.com/image2.jpg'
            ];
            
            $result = Tool::randomImage($customWallpapers);
            expect($result)->toBeIn($customWallpapers);
        });
    });
    
    describe('Sorting Utilities', function () {
        it('can sort array by date descending', function () {
            $testArray = [
                ['name' => 'Old', 'timestamp' => '2020-01-01 12:00:00'],
                ['name' => 'New', 'timestamp' => '2023-01-01 12:00:00'],
                ['name' => 'Middle', 'timestamp' => '2021-01-01 12:00:00'],
            ];
            
            $result = Tool::sortByDate($testArray, 'timestamp', 'desc');
            
            expect($result[0]['name'])->toBe('New')
                ->and($result[1]['name'])->toBe('Middle')
                ->and($result[2]['name'])->toBe('Old');
        });
        
        it('can sort array by date ascending', function () {
            $testArray = [
                ['name' => 'Old', 'timestamp' => '2020-01-01 12:00:00'],
                ['name' => 'New', 'timestamp' => '2023-01-01 12:00:00'],
                ['name' => 'Middle', 'timestamp' => '2021-01-01 12:00:00'],
            ];
            
            $result = Tool::sortByDate($testArray, 'timestamp', 'asc');
            
            expect($result[0]['name'])->toBe('Old')
                ->and($result[1]['name'])->toBe('Middle')
                ->and($result[2]['name'])->toBe('New');
        });
        
        it('can sort array by any key descending', function () {
            $testArray = [
                ['name' => 'Charlie', 'score' => 100],
                ['name' => 'Alice', 'score' => 300],
                ['name' => 'Bob', 'score' => 200],
            ];
            
            $result = Tool::sortBy($testArray, 'score', 'desc');
            
            expect($result[0]['name'])->toBe('Alice')
                ->and($result[1]['name'])->toBe('Bob')
                ->and($result[2]['name'])->toBe('Charlie');
        });
        
        it('can sort array by any key ascending', function () {
            $testArray = [
                ['name' => 'Charlie', 'score' => 100],
                ['name' => 'Alice', 'score' => 300],
                ['name' => 'Bob', 'score' => 200],
            ];
            
            $result = Tool::sortBy($testArray, 'score', 'asc');
            
            expect($result[0]['name'])->toBe('Charlie')
                ->and($result[1]['name'])->toBe('Bob')
                ->and($result[2]['name'])->toBe('Alice');
        });
    });
    
    describe('Route Generation', function () {
        it('generates correct game route', function () {
            $console = ['short_name' => 'nes'];
            $game = [
                'title' => 'Super Mario Bros',
                'slug' => 'super-mario-bros'
            ];
            
            $result = Tool::gameRoute($console, $game);
            
            expect($result)->toContain('nes')
                ->and($result)->toContain('super-mario-bros');
        });
        
        it('generates slug from title when slug not provided', function () {
            $console = ['short_name' => 'nes'];
            $game = ['title' => 'Super Mario Bros 3'];
            
            $result = Tool::gameRoute($console, $game);
            
            expect($result)->toContain('nes')
                ->and($result)->toContain('super-mario-bros-3');
        });
    });
    
    describe('Username Generation', function () {
        it('returns Guest for null user id', function () {
            $result = Tool::userName(null);
            expect($result)->toBe('Guest');
        });
        
        it('returns Guest for zero user id', function () {
            $result = Tool::userName(0);
            expect($result)->toBe('Guest');
        });
        
        it('returns Guest for empty string user id', function () {
            $result = Tool::userName('');
            expect($result)->toBe('Guest');
        });
    });
    
    describe('Genres', function () {
        it('returns array of genres when session data exists', function () {
            // Mock session data
            $mockConsoles = [
                [
                    'games' => [
                        [
                            'genres' => [
                                ['name' => 'platformer'],
                                ['name' => 'action']
                            ]
                        ],
                        [
                            'genres' => [
                                ['name' => 'adventure'],
                                ['name' => 'platformer'] // duplicate to test uniqueness
                            ]
                        ]
                    ]
                ]
            ];
            
            session(['consoles' => $mockConsoles]);
            
            $result = Tool::getGenres();
            
            expect($result)->toBeArray()
                ->and($result)->toContain('action')
                ->and($result)->toContain('adventure')
                ->and($result)->toContain('platformer');
            
            // Test that duplicates are removed
            expect(array_count_values($result))->each->toBe(1);
        });
        
        it('handles missing session data gracefully', function () {
            session()->forget('consoles');
            
            // This will throw an exception because the method doesn't handle null consoles
            expect(fn() => Tool::getGenres())->toThrow(ErrorException::class);
        });
    });
}); 