<?php

namespace App\Livewire;

use App\Service\Tool;
use Livewire\Component;

class JsPlayer extends Component
{
    
    public $title;
    public $short_name;
    public $game_url;
    public $game_id;
    public $save_state_config;

    public function mount(string $enc_json_game, string $console_short_name)
    {
        $json_game = Tool::decode($enc_json_game);
        $game_data = json_decode($json_game, true);
        $this->title = $game_data['title'];
        $this->short_name = $console_short_name;
        $this->game_url = route('game.serve', [
            'console' => $console_short_name, 
            'filename' => $game_data['rom']
        ]);
        $this->game_id = $game_data['id'];
        $this->save_state_config = $this->buildSaveStateConfig($game_data, $console_short_name);
    }

    public function render()
    {
        return view('livewire.js-player')
            ->layout('layouts.player');
    }

    private function buildSaveStateConfig(array $game_data, string $console_short_name): array
    {
        $gameSlug = $game_data['slug'] ?? \Illuminate\Support\Str::slug($game_data['title']);

        return [
            'authenticated'     => auth()->check(),
            'console'           => $console_short_name,
            'gameSlug'          => $gameSlug,
            'gameId'            => (string) $game_data['id'],
            'gameTitle'         => $game_data['title'],
            'emulator'          => 'emulatorjs',
            'slots'             => 5,
            'saveStateSupported' => $game_data['save_state_support'] ?? true,
            'csrfToken'         => csrf_token(),
            'endpoints'         => [
                'saveStates'        => route('player-data.save-states.index'),
                'controlSettings'   => route('player-data.control-settings.show'),
                'saveStateTemplate' => route('player-data.save-states.download', ['saveState' => '__SAVE_STATE__']),
            ],
        ];
    }
}
