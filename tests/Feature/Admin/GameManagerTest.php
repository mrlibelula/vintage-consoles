<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Service\GameManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GameManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    public function test_non_admin_user_cannot_access_game_manager()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/admin/games');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_game_manager()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin/games');

        $response->assertStatus(200);
        $response->assertSeeLivewire('admin.game-manager');
    }

    public function test_guest_user_cannot_access_game_manager()
    {
        $response = $this->get('/admin/games');

        $response->assertRedirect('/login');
    }

    public function test_game_manager_service_can_read_consoles_data()
    {
        $gameManager = new GameManager();
        $consoles = $gameManager->getConsoles();

        $this->assertIsArray($consoles);
    }

    public function test_game_manager_service_can_add_game()
    {
        // Create a temporary test data file
        $testDataPath = storage_path('data/test-vintage-consoles.json');
        $testData = [
            'consoles' => [
                [
                    'id' => 1,
                    'short_name' => 'TEST',
                    'long_name' => 'Test Console',
                    'games' => []
                ]
            ]
        ];
        
        File::ensureDirectoryExists(dirname($testDataPath));
        File::put($testDataPath, json_encode($testData, JSON_PRETTY_PRINT));

        // Mock the GameManager to use test data
        $gameManager = new class extends GameManager {
            public function __construct() {
                $this->dataPath = storage_path('data/test-vintage-consoles.json');
            }
        };

        $gameData = [
            'title' => 'Test Game',
            'publisher' => 'Test Publisher',
            'release_year' => '2023',
            'description' => 'A test game',
            'rating' => '0.8',
            'rom' => 'test-game.rom',
            'genres' => [['name' => 'test', 'description' => 'Test genre']],
            'screenshots' => ['https://example.com/screenshot.jpg']
        ];

        $result = $gameManager->addGame('TEST', $gameData);

        $this->assertTrue($result);

        // Verify the game was added
        $games = $gameManager->getGamesByConsole('TEST');
        $this->assertCount(1, $games);
        $this->assertEquals('Test Game', $games[0]['title']);

        // Clean up
        File::delete($testDataPath);
    }
} 