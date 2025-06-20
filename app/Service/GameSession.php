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
     * Establish all json 'consoles' data on Session
     */
    public function __construct(array $consoles = null)
    {
        $consoles 
            ? Session::put('consoles', $consoles)
            : $this->createNewSession();
        return $this;
    }

    public function consoles(): array
    {
        return $this->consoles;
    }

    /**
     * Creates a new 'consoles' Session env
     *
     * @return void
     */
    private function createNewSession(): void
    {
        if (Storage::disk($this->disk)->exists($this->json_data_file)) {
            $data = json_decode(Storage::disk($this->disk)->get($this->json_data_file), true);
            $this->consoles = isset($data['consoles']) ? $data['consoles'] : [];
            Session::put('consoles', $this->consoles);
        }
    }
}