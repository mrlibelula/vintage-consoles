<?php

namespace App\Livewire;

use App\Actions\UpsertEmulatorSaveState;
use App\Models\Console;
use App\Models\EmulatorSaveState;
use App\Models\Game;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;

class Play extends Component
{
    public Console $console;
    public Game $game;
    public string $game_url = '';
    public string $player_route = '';
    public int $save_slots_used = 0;
    public int $save_slots_total = UpsertEmulatorSaveState::MAX_SLOTS;
    public string $input = '';

    public array $accordion_toggler = [
        'description'  => true,
        'screenshots'  => true,
    ];

    public array $tabs = [
        'info' => true,
        'chat' => false,
    ];

    public array $modals = [
        'screenshots' => false,
    ];

    private int $take = 20;

    public function mount(string $console_short_name, string $game_title_slug): void
    {
        $repo = app(GameRepository::class);

        $game = $repo->getGameBySlug($console_short_name, $game_title_slug);

        if (! $game) {
            abort(404);
        }

        $this->game    = $game;
        $this->console = $game->console;

        $this->hydrateSaveSlots($console_short_name);
        $this->loadGameUrl();

        $this->player_route = route('player', [
            Tool::encode(json_encode($this->game->toPlayerPayload())),
            strtolower($this->console->short_name),
        ]);
    }

    private function hydrateSaveSlots(string $console_short_name): void
    {
        if (! auth()->check()) {
            $this->save_slots_used = 0;
            return;
        }

        if (! $this->game->save_state_support) {
            $this->save_slots_used = 0;
            return;
        }

        $this->save_slots_used = EmulatorSaveState::query()
            ->where('user_id', auth()->id())
            ->where('console', strtolower($console_short_name))
            ->where('game_slug', $this->game->slug)
            ->count();
    }

    public function updatedInput(): void
    {
        if ($this->input) {
            $userId    = auth()->id();
            $message   = $this->input;
            $timestamp = date('Y-m-d H:i:s');
            $messageId = $this->generateNewMessageId();

            $messageObject = [
                'id'        => $messageId,
                'user_id'   => $userId,
                'user_color'=> 'amber',
                'message'   => $message,
                'timestamp' => $timestamp,
                'ip'        => '',
                'is_mobile' => false,
            ];

            $messages   = $this->getMessages();
            $messages[] = $messageObject;
            $this->updateMessagesFile($messages);
            $messages = Tool::sortByDate($messages, 'timestamp');
            $messages = collect($messages)->take($this->take)->toArray();
            $this->dispatch('updateChatMessages', $messages);
            $this->input = '';
        }
    }

    public function updateMessagesFile(array $messages): void
    {
        Storage::disk('data')->put($this->chatFilePath(), json_encode($messages));
    }

    public function chatFilePath(): string
    {
        return 'chat/' . $this->console->id . '.' . $this->game->id . '.json';
    }

    public function getLastInsertedMessageId(): int|string
    {
        $messages = $this->getMessages();
        $messages = Tool::sortBy($messages, 'id', 'asc');

        if (count($messages)) {
            return end($messages)['id'];
        }

        return 0;
    }

    public function getMessages(): array
    {
        if (Storage::disk('data')->exists($this->chatFilePath())) {
            return json_decode(Storage::disk('data')->get($this->chatFilePath()), true) ?? [];
        }

        return [];
    }

    public function generateNewMessageId(): int|string
    {
        $lastId = $this->getLastInsertedMessageId();
        return gettype($lastId) === 'integer' ? $lastId + 1 : Str::random(10);
    }

    public function toggle(string $accordion_name): void
    {
        $key = strtolower($accordion_name);
        $this->accordion_toggler[$key] = ! ($this->accordion_toggler[$key] ?? false);
    }

    public function changeTab(string $tab): void
    {
        $this->tabs = array_map(fn () => false, $this->tabs);
        $this->tabs[$tab] = true;
    }

    public function loadGameUrl(): void
    {
        $shortName = strtolower($this->console->short_name);
        if ($shortName !== 'pc') {
            $this->game_url = route('game.serve', [
                'console'  => $shortName,
                'filename' => $this->game->rom,
            ]);
        }
    }

    public function rendered(): void
    {
        Tool::loadersOff($this);
        $this->dispatch('fixed-modal-loader-off');
    }

    public function render()
    {
        return view('livewire.play');
    }
}
