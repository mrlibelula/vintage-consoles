<?php

namespace App\Livewire;

use App\Service\Game;
use App\Service\Tool;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Service\GameSession;
use Illuminate\Support\Facades\Storage;

class Play extends Component
{
    protected string $console_short_name;
    protected string $enc_game_id;
    public array $console;
    public array $game;
    public string $game_url;
    public string $player_route;
    public string $input = '';
    public int $current_screenshot_key = -1;

    public array $accordion_toggler = [
        'description' => true, 
        'genres' => true, 
        'screenshots' => true, 
    ];

    public array $tabs = [
        'info' => true,
        'chat' => false,
    ];

    private int $take = 20;
    public array $modals = [
        'screenshots' => false,
        'genres' => false,
    ];

    protected $listeners = ['fixedModalClosed', 'keydownLeft', 'keydownRight'];

    /**
     * 'game_title' intended for SEO purposes only
     * Irrelevant if present or not
     *
     * @param string $enc_game_id
     * @param string $console_short_name
     * @param string $game_title
     * @return void
     */
    public function mount(string $console_short_name, string $game_title_slug)
    {
        new GameSession;

        // Find the game by slug in the console's games array
        $game_obj = new Game($console_short_name);
        $this->console = $game_obj->getConsole();
        
        // Find the game with matching slug
        $game = collect($this->console['games'])->first(function($game) use ($game_title_slug) {
            return $game['slug'] === $game_title_slug;
        });

        if (!$game) {
            abort(404);
        }

        $this->game = $game;
        $this->loadGameUrl();
        
        $this->player_route = route('player', [
            Tool::encode(json_encode($this->game)),
            strtolower($this->console['short_name']),
        ]);
    }

    public function updatedInput()
    {
        if ($this->input) {
            $user_id = auth()->user() ? auth()->user()->id : null;
            $message = $this->input;
            $timestamp = date('Y-m-d H:i:s');
            $message_id = $this->generateNewMessageId();
    
            $message_object = [
                "id" => $message_id, 
                "user_id" => $user_id,
                "user_color" => "amber",
                "message" => $message,
                "timestamp" => $timestamp,
                "ip" => "",
                "is_mobile" => false
            ];
    
            $messages = $this->getMessages();
            $messages[] = $message_object;
            $this->updateMessagesFile($messages);
            $messages = Tool::sortByDate($messages, 'timestamp');
            $messages = collect($messages)->take($this->take)->toArray();
            $this->dispatch('updateChatMessages', $messages);
            $this->input = '';
        }
    }

    /**
     * Saves json chat data file on storage
     *
     * @return void
     */
    public function updateMessagesFile(array $messages)
    {
        Storage::disk('data')->put($this->chatFilePath(), json_encode($messages));
    }

    public function chatFilePath(): string
    {
        return 'chat/' . $this->console['id'] . '.' . $this->game['id'] . '.json';
    }

    /**
     * Gets last inserted ID in messages array
     *
     * @return integer|string
     */
    public function getLastInsertedMessageId(): int|string
    {
        $messages = $this->getMessages();
        $messages = Tool::sortBy($messages, 'id', 'asc');
        
        // dd($messages);
        
        if (count($messages)) {
            return end($messages)['id'];
        }
        return 0;
    }
    
    public function getMessages(): array
    {
        $messages = [];
        $disk = 'data';
        if (Storage::disk($disk)->exists($this->chatFilePath())) {
            $messages = json_decode(Storage::disk('data')->get($this->chatFilePath()), true);
        }
        return $messages;
    }

    /**
     * Creates a new message ID for message
     *
     * @return integer|string
     */
    public function generateNewMessageId(): int|string
    {
        $last_id = $this->getLastInsertedMessageId();
        return gettype($last_id) === 'integer' ? $last_id + 1 : Str::random(10);
    }

    public function keydownLeft()
    {
        $this->changeScreenShot('left');
    }

    public function keydownRight()
    {
        $this->changeScreenShot('right');
    }
    
    public function changeTab(string $tab)
    {
        $this->tabs = array_map(function ($t) {
            return $t = false;
        }, $this->tabs);
        
        $this->tabs[$tab] = true;
    }

    public function rendered()
    {
        Tool::loadersOff($this);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function changeScreenShot(string $direction)
    {
        $limit_left = 0;
        $limit_right = count($this->game['screenshots']) - 1;
        $current = $this->current_screenshot_key;

        switch ($direction) {
            case 'left':
                $current--;
                $this->current_screenshot_key = $current <= $limit_left ? $limit_left : $current;
                break;
            case 'right':
                $current++;
                $this->current_screenshot_key = $current >= $limit_right ? $limit_right : $current;
                break;
        }
    }

    public function fixedModalClosed()
    {
        $this->current_screenshot_key = -1;
    }

    public function screenshot(int $screenshot_key)
    {
        $this->current_screenshot_key = $screenshot_key;
    }

    public function toggle(string $accordion_name)
    {
        $this->accordion_toggler[strtolower($accordion_name)] = !$this->accordion_toggler[strtolower($accordion_name)];
    }

    /**
     * For all consoles except 'PC'
     *
     * @return void
     */
    public function loadGameUrl()
    {
        $console_short_name = strtolower($this->console['short_name']);
        if ($console_short_name !== 'pc') {
            $this->game_url = 'games/' . $console_short_name . '/' . $this->game['rom'];
        }
    }

    public function render()
    {
        return view('livewire.play');
    }
}
