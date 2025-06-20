<?php

namespace App\Service;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GameManager
{
    protected string $dataPath;

    public function __construct()
    {
        $this->dataPath = storage_path('data/vintage-consoles.json');
    }

    /**
     * Get all consoles data
     */
    public function getConsolesData(): array
    {
        if (!File::exists($this->dataPath)) {
            return ['consoles' => []];
        }

        $content = File::get($this->dataPath);
        return json_decode($content, true) ?? ['consoles' => []];
    }

    /**
     * Save consoles data to JSON file
     */
    public function saveConsolesData(array $data): bool
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        // Ensure the directory exists
        $directory = dirname($this->dataPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return File::put($this->dataPath, $jsonContent) !== false;
    }

    /**
     * Get all consoles
     */
    public function getConsoles(): array
    {
        $data = $this->getConsolesData();
        return $data['consoles'] ?? [];
    }

    /**
     * Get console by short name
     */
    public function getConsole(string $shortName): ?array
    {
        $consoles = $this->getConsoles();
        
        foreach ($consoles as $console) {
            if (strtolower($console['short_name']) === strtolower($shortName)) {
                return $console;
            }
        }
        
        return null;
    }

    /**
     * Get all games from a specific console
     */
    public function getGamesByConsole(string $consoleShortName): array
    {
        $console = $this->getConsole($consoleShortName);
        return $console['games'] ?? [];
    }

    /**
     * Get a specific game by console and game ID
     */
    public function getGame(string $consoleShortName, int $gameId): ?array
    {
        $games = $this->getGamesByConsole($consoleShortName);
        
        foreach ($games as $game) {
            if ($game['id'] === $gameId) {
                return $game;
            }
        }
        
        return null;
    }

    /**
     * Add a new game to a console
     */
    public function addGame(string $consoleShortName, array $gameData): bool
    {
        $data = $this->getConsolesData();
        
        foreach ($data['consoles'] as &$console) {
            if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                // Generate new ID
                $maxId = 0;
                foreach ($console['games'] as $game) {
                    $maxId = max($maxId, $game['id']);
                }
                
                $gameData['id'] = $maxId + 1;
                $gameData['slug'] = $this->generateSlug($gameData['title']);
                
                // Set default values if not provided
                $gameData = array_merge([
                    'play_count' => 0,
                    'last_played' => '',
                    'bookmark_status' => false,
                    'metadata' => [],
                    'user_reviews' => [],
                    'additional_notes' => '',
                    'hof' => [],
                    'options' => []
                ], $gameData);
                
                $console['games'][] = $gameData;
                return $this->saveConsolesData($data);
            }
        }
        
        return false;
    }

    /**
     * Update an existing game
     */
    public function updateGame(string $consoleShortName, int $gameId, array $gameData): bool
    {
        $data = $this->getConsolesData();
        
        foreach ($data['consoles'] as &$console) {
            if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                foreach ($console['games'] as &$game) {
                    if ($game['id'] === $gameId) {
                        // Preserve ID and update slug if title changed
                        $gameData['id'] = $gameId;
                        if (isset($gameData['title'])) {
                            $gameData['slug'] = $this->generateSlug($gameData['title']);
                        }
                        
                        // Merge with existing data
                        $game = array_merge($game, $gameData);
                        return $this->saveConsolesData($data);
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Delete a game
     */
    public function deleteGame(string $consoleShortName, int $gameId): bool
    {
        $data = $this->getConsolesData();
        
        foreach ($data['consoles'] as &$console) {
            if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                foreach ($console['games'] as $index => $game) {
                    if ($game['id'] === $gameId) {
                        unset($console['games'][$index]);
                        // Reindex array
                        $console['games'] = array_values($console['games']);
                        return $this->saveConsolesData($data);
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Generate a slug from title
     */
    private function generateSlug(string $title): string
    {
        return Str::slug($title);
    }

    /**
     * Get all unique genres across all games
     */
    public function getAllGenres(): array
    {
        $genres = [];
        $consoles = $this->getConsoles();
        
        foreach ($consoles as $console) {
            foreach ($console['games'] ?? [] as $game) {
                foreach ($game['genres'] ?? [] as $genre) {
                    if (isset($genre['name'])) {
                        $genres[$genre['name']] = $genre;
                    }
                }
            }
        }
        
        return array_values($genres);
    }

    /**
     * Get all unique publishers across all games
     */
    public function getAllPublishers(): array
    {
        $publishers = [];
        $consoles = $this->getConsoles();
        
        foreach ($consoles as $console) {
            foreach ($console['games'] ?? [] as $game) {
                if (isset($game['publisher']) && !in_array($game['publisher'], $publishers)) {
                    $publishers[] = $game['publisher'];
                }
            }
        }
        
        sort($publishers);
        return $publishers;
    }
} 