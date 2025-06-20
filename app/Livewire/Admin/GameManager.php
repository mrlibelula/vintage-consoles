<?php

namespace App\Livewire\Admin;

use App\Service\GameManager as GameManagerService;
use App\Service\GameSession;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]

class GameManager extends Component
{
    use WithPagination;

    public $selectedConsole = '';
    public $consoles = [];
    public $games = [];
    public $showModal = false;
    public $modalMode = 'add'; // 'add', 'edit', 'delete'
    public $editingGame = null;
    public $searchTerm = '';
    public $sortField = 'id';
    public $sortDirection = 'asc';

    // Form fields
    public $title = '';
    public $publisher = '';
    public $release_year = '';
    public $description = '';
    public $rating = '0.5';
    public $rom = '';
    public $poster = '';
    public $box = '';
    public $cartridge = '';
    public $multiplayer_support = false;
    public $save_state_support = true;
    public $is_free = false;
    public $genres = [['name' => '', 'description' => '']];
    public $screenshots = [''];

    protected $gameManagerService;

    public function boot(GameManagerService $gameManagerService)
    {
        $this->gameManagerService = $gameManagerService;
    }

    public function mount()
    {
        $this->consoles = $this->gameManagerService->getConsoles();
        
        if (!empty($this->consoles)) {
            $this->selectedConsole = $this->consoles[0]['short_name'];
            $this->loadGames();
        }
    }

    public function updatedSelectedConsole()
    {
        $this->resetPage();
        $this->searchTerm = ''; // Clear search when console changes
        $this->loadGames();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            // Toggle direction if clicking the same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function loadGames()
    {
        if ($this->selectedConsole) {
            $this->games = $this->gameManagerService->getGamesByConsole($this->selectedConsole);
        }
    }

    /**
     * Refresh session data to update search functionality
     */
    protected function refreshSessionData()
    {
        $consolesData = $this->gameManagerService->getConsoles();
        
        // Update session with fresh data from JSON file
        Session::put('consoles', $consolesData);
        
        // Also refresh our local consoles data
        $this->consoles = $consolesData;
    }

    public function getFilteredGamesProperty()
    {
        $games = $this->games;

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $games = array_filter($games, function ($game) {
                return stripos($game['title'], $this->searchTerm) !== false ||
                       stripos($game['publisher'], $this->searchTerm) !== false;
            });
        }

        // Apply sorting
        if (!empty($this->sortField)) {
            usort($games, function ($a, $b) {
                $valueA = $a[$this->sortField] ?? '';
                $valueB = $b[$this->sortField] ?? '';
                
                // Handle numeric sorting for certain fields
                if ($this->sortField === 'release_year' || $this->sortField === 'rating' || $this->sortField === 'id') {
                    $valueA = (float) $valueA;
                    $valueB = (float) $valueB;
                } else {
                    // String comparison
                    $valueA = strtolower($valueA);
                    $valueB = strtolower($valueB);
                }

                if ($this->sortDirection === 'asc') {
                    return $valueA <=> $valueB;
                } else {
                    return $valueB <=> $valueA;
                }
            });
        }

        return $games;
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->modalMode = 'add';
        $this->showModal = true;
    }

    public function openEditModal($gameId)
    {
        $game = $this->gameManagerService->getGame($this->selectedConsole, $gameId);
        
        if ($game) {
            $this->editingGame = $game;
            $this->fillForm($game);
            $this->modalMode = 'edit';
            $this->showModal = true;
        }
    }

    public function openDeleteModal($gameId)
    {
        $game = $this->gameManagerService->getGame($this->selectedConsole, $gameId);
        
        if ($game) {
            $this->editingGame = $game;
            $this->modalMode = 'delete';
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingGame = null;
        $this->js('document.body.style.overflow = "auto"');
    }

    public function saveGame()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'publisher' => 'required|string|max:255',
            'release_year' => 'required|numeric|min:1970|max:' . (date('Y') + 5),
            'description' => 'required|string',
            'rating' => 'required|numeric|min:0|max:1',
            'rom' => 'required|string|max:255',
            'poster' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidImagePath($value)) {
                    $fail('The ' . $attribute . ' must be a valid URL or a path starting with "/".');
                }
            }],
            'box' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidImagePath($value)) {
                    $fail('The ' . $attribute . ' must be a valid URL or a path starting with "/".');
                }
            }],
            'cartridge' => ['nullable', 'string', 'max:500', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->isValidImagePath($value)) {
                    $fail('The ' . $attribute . ' must be a valid URL or a path starting with "/".');
                }
            }],
        ]);

        // Validate ROM file existence
        $romValidation = $this->validateRomFile($this->rom, $this->selectedConsole);
        if (!$romValidation['valid']) {
            // Dispatch ROM error event for in-modal message
            $this->js('window.dispatchEvent(new CustomEvent("rom-error", { detail: { message: "' . addslashes($romValidation['message']) . '" } }))');
            $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
            return;
        }

        // Process genres to ensure names are slugged
        $processedGenres = array_filter($this->genres, function($genre) {
            return !empty($genre['name']);
        });
        
        foreach ($processedGenres as &$genre) {
            // Convert genre name to slug format
            $genre['name'] = \Illuminate\Support\Str::slug($genre['name']);
        }

        // Validate screenshots (filter out empty ones and validate paths)
        $validScreenshots = [];
        foreach ($this->screenshots as $screenshot) {
            if (!empty($screenshot)) {
                if (!$this->isValidImagePath($screenshot)) {
                    // Dispatch error for invalid screenshot
                    $this->js('window.dispatchEvent(new CustomEvent("rom-error", { detail: { message: "Invalid screenshot URL or path: ' . addslashes($screenshot) . '. Must be a valid URL or path starting with \"/\"." } }))');
                    $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
                    return;
                }
                $validScreenshots[] = $screenshot;
            }
        }

        $gameData = [
            'title' => $this->title,
            'publisher' => $this->publisher,
            'release_year' => $this->release_year,
            'description' => $this->description,
            'rating' => (string) $this->rating,
            'rom' => $this->rom,
            'poster' => $this->poster ?: 'https://via.placeholder.com/300x400?text=' . urlencode($this->title),
            'box' => $this->box ?: '',
            'cartridge' => $this->cartridge ?: '',
            'multiplayer_support' => $this->multiplayer_support,
            'save_state_support' => $this->save_state_support,
            'is_free' => $this->is_free,
            'genres' => $processedGenres,
            'screenshots' => $validScreenshots,
        ];

        if ($this->modalMode === 'add') {
            $success = $this->gameManagerService->addGame($this->selectedConsole, $gameData);
            $message = 'Game added successfully!';
        } else {
            $success = $this->gameManagerService->updateGame(
                $this->selectedConsole, 
                $this->editingGame['id'], 
                $gameData
            );
            $message = 'Game updated successfully!';
        }

        if ($success) {
            $this->loadGames();
            $this->refreshSessionData(); // Refresh session data for search functionality
            $this->closeModal();
            session()->flash('success', $message);
        } else {
            session()->flash('error', 'Failed to save game. Please try again.');
        }
        
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
    }

    /**
     * Validate ROM file existence based on console type
     */
    private function validateRomFile(string $rom, string $consoleShortName): array
    {
        // For PC/MS-DOS games, validate .jsdos URL
        if (strtolower($consoleShortName) === 'pc') {
            if (!filter_var($rom, FILTER_VALIDATE_URL)) {
                return [
                    'valid' => false,
                    'message' => 'For PC/MS-DOS games, ROM must be a valid .jsdos URL (e.g., https://play.libe.dev/games/bundles/game.jsdos)'
                ];
            }
            
            if (!str_ends_with(strtolower($rom), '.jsdos')) {
                return [
                    'valid' => false,
                    'message' => 'PC/MS-DOS games must use .jsdos file format'
                ];
            }
            
            // Check if URL is accessible
            try {
                $headers = get_headers($rom, 1);
                if (!$headers || !str_contains($headers[0], '200')) {
                    return [
                        'valid' => false,
                        'message' => 'The .jsdos URL is not accessible. Please verify the URL exists.'
                    ];
                }
            } catch (\Exception $e) {
                return [
                    'valid' => false,
                    'message' => 'Unable to verify .jsdos URL: ' . $e->getMessage()
                ];
            }
            
            return ['valid' => true];
        }
        
        // For other consoles, check local storage
        $romPath = public_path("games/{$consoleShortName}/{$rom}");
        
        if (!file_exists($romPath)) {
            return [
                'valid' => false,
                'message' => "ROM file '{$rom}' not found in /public/games/{$consoleShortName}/ directory. Please ensure the file exists."
            ];
        }
        
        // Additional validation for common ROM file extensions
        $allowedExtensions = [
            'arcade' => ['zip'],
            'nes' => ['nes'],
            'snes' => ['zip', '7z', 'smc'],
            'atari2600' => ['bin', 'a26'],
        ];
        
        if (isset($allowedExtensions[$consoleShortName])) {
            $extension = strtolower(pathinfo($rom, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions[$consoleShortName])) {
                return [
                    'valid' => false,
                    'message' => "Invalid file extension for {$consoleShortName}. Expected: " . 
                               implode(', ', $allowedExtensions[$consoleShortName]) . 
                               ". Got: {$extension}"
                ];
            }
        }
        
        return ['valid' => true];
    }

    /**
     * Validate image path (URL or relative path starting with /)
     */
    private function isValidImagePath(string $path): bool
    {
        // Check if it's a valid URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        // Check if it's a relative path starting with /
        if (str_starts_with($path, '/')) {
            // Optional: Check if the local file exists
            $localPath = public_path($path);
            return file_exists($localPath);
        }
        
        return false;
    }

    public function deleteGame()
    {
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-on"))');
        
        if ($this->editingGame) {
            $success = $this->gameManagerService->deleteGame(
                $this->selectedConsole, 
                $this->editingGame['id']
            );

            if ($success) {
                $this->loadGames();
                $this->refreshSessionData(); // Refresh session data for search functionality
                $this->closeModal();
                session()->flash('success', 'Game deleted successfully!');
            } else {
                session()->flash('error', 'Failed to delete game. Please try again.');
            }
        }
        
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
    }

    public function addGenre()
    {
        $this->genres[] = ['name' => '', 'description' => ''];
    }

    public function removeGenre($index)
    {
        if (count($this->genres) > 1) {
            unset($this->genres[$index]);
            $this->genres = array_values($this->genres);
        }
    }

    public function addScreenshot()
    {
        $this->screenshots[] = '';
    }

    public function removeScreenshot($index)
    {
        if (count($this->screenshots) > 1) {
            unset($this->screenshots[$index]);
            $this->screenshots = array_values($this->screenshots);
        }
    }

    public function fetchGameDataFromAI()
    {
        // Validate that title is provided
        if (empty($this->title) || trim($this->title) === '') {
            // Don't show error message since button should be disabled in UI
            $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
            return;
        }

        try {
            // Get console long name for better AI context
            $console = collect($this->consoles)->firstWhere('short_name', $this->selectedConsole);
            $consoleName = $console ? $console['long_name'] : $this->selectedConsole;

            $prompt = "You are a video game database expert. Please provide detailed information about the following video game in JSON format.

Game Title: \"{$this->title}\"
Console/Platform: \"{$consoleName}\"

Please return ONLY a valid JSON object with the following structure (no additional text, markdown, or explanation):
{
  \"publisher\": \"Publisher name\",
  \"release_year\": \"Year as number (e.g., 1985)\",
  \"rating\": \"Rating as decimal between 0 and 1 (e.g., 0.85 for 85%)\",
  \"description\": \"Detailed game description (2-3 sentences)\",
  \"genres\": [
    {\"name\": \"genre-slug\", \"description\": \"Genre description\"},
    {\"name\": \"another-genre\", \"description\": \"Another genre description\"}
  ]
}

CRITICAL REQUIREMENTS for release_year:
- For ARCADE games: Use the ARCADE release year, NOT the original concept/character creation year
- For console ports: Use the year when THIS SPECIFIC version was released on THIS SPECIFIC platform
- Examples: Superman (Arcade) = 1988, Pac-Man (Arcade) = 1980, Donkey Kong (Arcade) = 1981
- DO NOT use character creation dates, movie release dates, or other unrelated years
- Research the actual video game release date for the specified platform

Additional requirements:
- Genre names should be lowercase with hyphens (e.g., \"action-adventure\", \"role-playing\")
- Rating should be a decimal between 0 and 1 representing the game's quality/reception
- Publisher should be the actual game publisher/developer, not the license holder
- Description should focus on gameplay and game-specific details
- If uncertain about any data, research video game databases and historical records";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
                'max_tokens' => 1000,
            ]);

            $aiResponse = $response->choices[0]->message->content;
            
            // Try to parse JSON response
            $gameData = json_decode($aiResponse, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($gameData)) {
                throw new \Exception('Invalid JSON response from AI');
            }

            // Fill form fields with AI data
            if (isset($gameData['publisher'])) {
                $this->publisher = $gameData['publisher'];
            }
            
            if (isset($gameData['release_year'])) {
                $this->release_year = (string) $gameData['release_year'];
            }
            
            if (isset($gameData['rating'])) {
                $this->rating = (string) $gameData['rating'];
            }
            
            if (isset($gameData['description'])) {
                $this->description = $gameData['description'];
            }
            
            if (isset($gameData['genres']) && is_array($gameData['genres'])) {
                $this->genres = $gameData['genres'];
            }

            // Dispatch success event for in-modal message
            $this->js('window.dispatchEvent(new CustomEvent("ai-success"))');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to fetch game data from AI: ' . $e->getMessage());
        } finally {
            // Turn off loader when done (success or error) - using JS to trigger window event
            $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
        }
    }

    private function resetForm()
    {
        $this->title = '';
        $this->publisher = '';
        $this->release_year = '';
        $this->description = '';
        $this->rating = '0.5';
        $this->rom = '';
        $this->poster = '';
        $this->box = '';
        $this->cartridge = '';
        $this->multiplayer_support = false;
        $this->save_state_support = true;
        $this->is_free = false;
        $this->genres = [['name' => '', 'description' => '']];
        $this->screenshots = [''];
    }

    private function fillForm($game)
    {
        $this->title = $game['title'];
        $this->publisher = $game['publisher'];
        $this->release_year = $game['release_year'];
        $this->description = $game['description'];
        $this->rating = $game['rating'];
        $this->rom = $game['rom'];
        $this->poster = $game['poster'] ?? '';
        $this->box = $game['box'] ?? '';
        $this->cartridge = $game['cartridge'] ?? '';
        $this->multiplayer_support = $game['multiplayer_support'] ?? false;
        $this->save_state_support = $game['save_state_support'] ?? true;
        $this->is_free = $game['is_free'] ?? false;
        $this->genres = !empty($game['genres']) ? $game['genres'] : [['name' => '', 'description' => '']];
        $this->screenshots = !empty($game['screenshots']) ? $game['screenshots'] : [''];
    }

    public function render()
    {
        return view('livewire.admin.game-manager');
    }
} 