<?php

namespace App\Livewire;

use App\Actions\UpsertEmulatorSaveState;
use App\Models\Console;
use App\Models\EmulatorSaveState;
use App\Models\Game;
use App\Models\YoutubeVideoProgress;
use App\Service\Tool;
use App\Services\CheatSheetService;
use App\Services\GameRepository;
use App\Support\GameIgdbPresenter;
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

    /** @var array<int, int> */
    public array $save_slots_occupied = [];
    public string $input = '';

    /** @var array<string, mixed> */
    public array $igdb = [];

    /** @var array<string, int> youtube_id => position_seconds */
    public array $video_progress = [];

    public bool $hasCheats = false;
    public ?string $cheatHtml = null;

    public array $accordion_toggler = [
        'description'  => true,
        'screenshots'  => true,
        'videos'       => true,
        'cheats'       => false,
        'artworks'     => true,
        'similar'      => true,
    ];

    public array $tabs = [
        'info' => true,
        'extras' => false,
    ];

    /** Keep extras DOM (and PiP Alpine state) after first visit so tab switches do not tear down the player. */
    public bool $extras_shell_mounted = false;

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

        $this->loadSaveSlots($console_short_name);
        $this->loadIgdb();
        $this->loadCheatSheet();
        $this->loadVideoProgress();
        $this->loadGameUrl();

        $this->player_route = route('player', [
            Tool::encode(json_encode($this->game->toPlayerPayload())),
            strtolower($this->console->short_name),
        ]);
    }

    private function loadIgdb(): void
    {
        $payload = is_array($this->game->igdb_response) ? $this->game->igdb_response : [];
        $similarIds = collect($payload['similar_games'] ?? [])
            ->map(fn ($item) => is_array($item) ? (int) ($item['id'] ?? 0) : 0)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $localByIgdbId = collect();
        if ($similarIds->isNotEmpty()) {
            $localByIgdbId = Game::query()
                ->whereIn('igdb_id', $similarIds->all())
                ->with('console')
                ->get()
                ->keyBy('igdb_id');
        }

        $this->igdb = app(GameIgdbPresenter::class)->present($this->game, $localByIgdbId);

        $this->tabs = ['info' => true, 'extras' => false];
    }

    private function loadCheatSheet(): void
    {
        $service = app(CheatSheetService::class);
        $markdown = $service->get($this->game);

        $this->hasCheats = $markdown !== null;
        $this->cheatHtml = $this->hasCheats ? $service->toHtml($markdown) : null;

        // Extras tab: media content (videos/artworks/similar) and/or a cheat sheet.
        $this->igdb['has_extras'] = ($this->igdb['has_media'] ?? false) || $this->hasCheats;
    }

    private function loadVideoProgress(): void
    {
        $this->video_progress = [];

        if (! auth()->check()) {
            return;
        }

        $rows = YoutubeVideoProgress::query()
            ->where('user_id', auth()->id())
            ->where('game_id', $this->game->id)
            ->get(['youtube_id', 'position_seconds']);

        foreach ($rows as $row) {
            $this->video_progress[$row->youtube_id] = (int) $row->position_seconds;
        }
    }

    private function loadSaveSlots(string $console_short_name): void
    {
        if (! auth()->check()) {
            $this->save_slots_used = 0;
            $this->save_slots_occupied = [];
            return;
        }

        if (! $this->game->save_state_support) {
            $this->save_slots_used = 0;
            $this->save_slots_occupied = [];
            return;
        }

        $this->save_slots_occupied = EmulatorSaveState::query()
            ->where('user_id', auth()->id())
            ->where('console', strtolower($console_short_name))
            ->where('game_slug', $this->game->slug)
            ->orderBy('slot')
            ->pluck('slot')
            ->map(fn ($slot) => (int) $slot)
            ->values()
            ->all();

        $this->save_slots_used = count($this->save_slots_occupied);
    }

    public function updatedInput(): void
    {
        $this->sendMessage();
    }

    public function sendMessage(): void
    {
        if (! auth()->check()) {
            $this->input = '';

            return;
        }

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
        if (! array_key_exists($tab, $this->tabs)) {
            return;
        }

        if ($tab === 'extras' && ! ($this->igdb['has_extras'] ?? false)) {
            return;
        }

        $this->tabs = array_map(fn () => false, $this->tabs);
        $this->tabs[$tab] = true;

        // Each tab opens with its rose accordion sections expanded.
        if ($tab === 'info') {
            $this->accordion_toggler['screenshots'] = true;
            $this->accordion_toggler['description'] = true;
        }

        if ($tab === 'extras') {
            $this->extras_shell_mounted = true;
            $this->accordion_toggler['videos'] = true;
            $this->accordion_toggler['artworks'] = true;
            $this->accordion_toggler['similar'] = true;
        }
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
