<?php

namespace App\Service;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class GameSession
{
    protected string $disk = 'data';
    protected string $json_data_file = 'vintage-consoles.json';
    protected array $consoles = [];

    public function __construct(array $consoles = null)
    {
        if ($consoles) {
            Session::put('consoles', $consoles);
        } elseif (!Session::has('consoles')) {
            $this->createNewSession();
        }
        return $this;
    }

    public function consoles(): array
    {
        return $this->consoles;
    }

    private function createNewSession(): void
    {
        if (Storage::disk($this->disk)->exists($this->json_data_file)) {
            $data = json_decode(Storage::disk($this->disk)->get($this->json_data_file), true);

            if (isset($data['consoles'])) {
                $this->consoles = $data['consoles'];
                Session::put('consoles', $this->consoles);
            }
        }
    }

    /**
     * Get full console data including games. Reads from session when available,
     * falls back to the data file for data not yet in session.
     */
    public function getFullConsoleData(string $consoleShortName = null): array
    {
        $sessionConsoles = Session::get('consoles', []);

        if ($consoleShortName) {
            // Try session first (populated by createNewSession from fake or real storage)
            foreach ($sessionConsoles as $console) {
                if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                    if (isset($console['games'])) {
                        return $console;
                    }
                    break;
                }
            }
        } elseif (!empty($sessionConsoles) && isset($sessionConsoles[0]['games'])) {
            return $sessionConsoles;
        }

        // Fall back to reading from the data file
        if (!Storage::disk($this->disk)->exists($this->json_data_file)) {
            return [];
        }

        $data = json_decode(Storage::disk($this->disk)->get($this->json_data_file), true);

        if ($consoleShortName) {
            foreach ($data['consoles'] ?? [] as $console) {
                if (strtolower($console['short_name']) === strtolower($consoleShortName)) {
                    return $console;
                }
            }
            return [];
        }

        return $data['consoles'] ?? [];
    }

    /**
     * Clear console data cache (call when JSON file is updated)
     */
    public function clearCache(): void
    {
        cache()->forget('all_consoles_data');
        
        // Clear individual console caches
        $basicConsoles = Session::get('consoles', []);
        foreach ($basicConsoles as $console) {
            cache()->forget("console_data_{$console['short_name']}");
        }
    }
}