<?php

namespace App\Livewire\Admin;

use App\Service\GameManager as GameManagerService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
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
    public $sortField = 'title';
    public $sortDirection = 'asc';
    public int $perPage = 4;

    // Form fields
    public $formConsole = '';
    public $originalConsole = '';
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
        $this->perPage = (int) Session::get('admin-game-manager.perPage', 4);
        
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

    public function updatedPerPage($value)
    {
        $this->perPage = max(1, (int) $value);
        Session::put('admin-game-manager.perPage', $this->perPage);
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
        // Use optimized approach - only refresh basic console data
        // Don't load full data into session as it defeats our memory optimization
        $gameSession = new \App\Service\GameSession(); // Constructor creates optimized session
        $gameSession->clearCache(); // Clear cached full console data so fresh data is loaded
        
        // Also refresh our local consoles data for admin use
        $this->consoles = $this->gameManagerService->getConsoles();
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

    public function getPaginatedGamesProperty(): LengthAwarePaginator
    {
        $filtered = $this->filteredGames;
        $total = count($filtered);

        $page = (int) $this->getPage();
        $perPage = max(1, (int) $this->perPage);
        $offset = max(0, ($page - 1) * $perPage);

        return new LengthAwarePaginator(
            array_slice($filtered, $offset, $perPage),
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function openAddModal()
    {
        $this->resetForm();
        $this->modalMode = 'add';
        $this->showModal = true;
        // Default console for the modal is the currently selected console
        $this->formConsole = $this->selectedConsole;
        $this->originalConsole = $this->selectedConsole;
        $this->dispatch('loader-top-off');
    }

    public function openEditModal($gameId)
    {
        $game = $this->gameManagerService->getGame($this->selectedConsole, $gameId);
        
        if ($game) {
            $this->editingGame = $game;
            $this->fillForm($game);
            $this->modalMode = 'edit';
            $this->showModal = true;
            // Track console for move scenarios
            $this->formConsole = $this->selectedConsole;
            $this->originalConsole = $this->selectedConsole;
        }
        $this->dispatch('loader-top-off');
    }

    public function openDeleteModal($gameId)
    {
        $game = $this->gameManagerService->getGame($this->selectedConsole, $gameId);
        
        if ($game) {
            $this->editingGame = $game;
            $this->modalMode = 'delete';
            $this->showModal = true;
        }
        $this->dispatch('loader-top-off');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->editingGame = null;
        $this->js('document.body.style.overflow = "auto"');
        $this->dispatch('loader-top-off');
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

        // Validate ROM file existence against the console selected in the modal
        $consoleForValidation = $this->formConsole ?: $this->selectedConsole;
        $romValidation = $this->validateRomFile($this->rom, $consoleForValidation);
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
            $success = $this->gameManagerService->addGame($this->formConsole, $gameData);
            $message = 'Game added successfully!';
        } else {
            if ($this->formConsole !== $this->originalConsole) {
                // Move game across consoles: preserve existing metadata when possible
                $dataToAdd = array_merge($this->editingGame ?? [], $gameData);
                unset($dataToAdd['id']); // id will be generated in target console
                $deleted = $this->gameManagerService->deleteGame($this->originalConsole, $this->editingGame['id']);
                $added = $this->gameManagerService->addGame($this->formConsole, $dataToAdd);
                $success = $deleted && $added;
                $message = 'Game moved and updated successfully!';
            } else {
                $success = $this->gameManagerService->updateGame(
                    $this->originalConsole,
                    $this->editingGame['id'],
                    $gameData
                );
                $message = 'Game updated successfully!';
            }
        }

        if ($success) {
            // Ensure the main list reflects the console chosen in the modal
            $this->selectedConsole = $this->formConsole ?: $this->selectedConsole;
            $this->loadGames();
            $this->refreshSessionData(); // Refresh session data for search functionality
            $this->closeModal();
            session()->flash('success', $message);
        } else {
            session()->flash('error', 'Failed to save game. Please try again.');
        }
        $this->dispatch('loader-top-off');
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
        // Convert to lowercase to match filesystem directory names (e.g., "nes" not "NES")
        $consoleDir = strtolower($consoleShortName);
        $romPath = storage_path("data/games/{$consoleDir}/{$rom}");
        
        if (!file_exists($romPath)) {
            return [
                'valid' => false,
                'message' => "ROM file '{$rom}' not found in /storage/data/games/{$consoleDir}/ directory. Please ensure the file exists."
            ];
        }
        
        // Additional validation for common ROM file extensions
        $allowedExtensions = [
            'arcade' => ['zip'],
            'nes' => ['nes'],
            'snes' => ['zip', '7z', 'smc'],
            'atari2600' => ['bin', 'a26'],
        ];
        
        if (isset($allowedExtensions[$consoleDir])) {
            $extension = strtolower(pathinfo($rom, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions[$consoleDir])) {
                return [
                    'valid' => false,
                    'message' => "Invalid file extension for {$consoleDir}. Expected: " . 
                               implode(', ', $allowedExtensions[$consoleDir]) . 
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
        $path = trim($path);

        // Check if it's a valid URL
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        // Accept local URIs that start at web root (e.g. /images/games/foo.png)
        return str_starts_with($path, '/');
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
            $consoleKey = $this->formConsole ?: $this->selectedConsole;
            $console = collect($this->consoles)->firstWhere('short_name', $consoleKey);
            $consoleName = $console ? $console['long_name'] : $consoleKey;

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