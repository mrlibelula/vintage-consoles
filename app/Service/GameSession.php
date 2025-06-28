<?php

namespace App\Service;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class GameSession
{
    protected string $disk = 'data';
    protected string $json_data_file = 'vintage-consoles.json';
    protected array $consoles = [];

    /**
     * Establish minimal console data on Session (without full game details)
     */
    public function __construct(array $consoles = null)
    {
        $consoles 
            ? Session::put('consoles_basic', $consoles)
            : $this->createNewSession();
        return $this;
    }

    public function consoles(): array
    {
        return $this->consoles;
    }

    /**
     * Creates a new session with only basic console info (no full game data)
     *
     * @return void
     */
    private function createNewSession(): void
    {
        if (Storage::disk($this->disk)->exists($this->json_data_file)) {
            $data = json_decode(Storage::disk($this->disk)->get($this->json_data_file), true);
            
            if (isset($data['consoles'])) {
                // Only store basic console info, not full game details
                $basicConsoles = [];
                foreach ($data['consoles'] as $console) {
                    $basicConsoles[] = [
                        'id' => $console['id'],
                        'long_name' => $console['long_name'],
                        'short_name' => $console['short_name'],
                        'description' => $console['description'],
                        'emulator' => $console['emulator'] ?? null,
                        'console_logo' => $console['console_logo'] ?? null,
                        'console_icon' => $console['console_icon'] ?? null,
                        'console_bgs' => $console['console_bgs'] ?? [],
                        'manufacturer' => $console['manufacturer'] ?? null,
                        'release_year' => $console['release_year'] ?? null,
                        'game_count' => count($console['games'] ?? [])
                        // Note: 'games', 'specs', 'community_links' deliberately excluded to save memory
                    ];
                }
                $this->consoles = $basicConsoles;
                Session::put('consoles_basic', $this->consoles);
            }
        }
    }

    /**
     * Get full console data including games (loads from file when needed)
     */
    public function getFullConsoleData(string $consoleShortName = null): array
    {
        if (!Storage::disk($this->disk)->exists($this->json_data_file)) {
            return [];
        }

        // Use cache to avoid repeated file reads
        $cacheKey = $consoleShortName ? "console_data_{$consoleShortName}" : 'all_consoles_data';
        
        return cache()->remember($cacheKey, now()->addMinutes(30), function () use ($consoleShortName) {
            $data = json_decode(Storage::disk($this->disk)->get($this->json_data_file), true);
            
            if ($consoleShortName) {
                // Return specific console with full game data
                foreach ($data['consoles'] ?? [] as $console) {
                    if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                        return $console;
                    }
                }
                return [];
            }
            
            return $data['consoles'] ?? [];
        });
    }

    /**
     * Clear console data cache (call when JSON file is updated)
     */
    public function clearCache(): void
    {
        cache()->forget('all_consoles_data');
        
        // Clear individual console caches
        $basicConsoles = Session::get('consoles_basic', []);
        foreach ($basicConsoles as $console) {
            cache()->forget("console_data_{$console['short_name']}");
        }
    }
}