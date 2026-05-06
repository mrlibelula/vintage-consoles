<?php

namespace App\Livewire;

use App\Actions\SyncEmulatorSaveStatesFromDisk;
use App\Actions\UpsertEmulatorSaveState;
use App\Models\EmulatorSaveState;
use App\Service\GameSession;
use App\Service\Tool;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class MySaves extends Component
{
    use WithFileUploads;

    public array $grouped = [];

    public int $totalSlots = 0;

    public int $totalGames = 0;

    public int $totalConsoles = 0;

    public bool $confirmingDelete = false;

    public ?int $pendingDeleteId = null;

    public string $pendingDeleteGameTitle = '';

    public int $pendingDeleteSlot = 0;

    // ── slot-specific upload (existing game row) ──────────────────────────────
    public bool $showUploadModal = false;

    /** @var array{console:string,game_id:string,emulator:string,slot:int,game_title:string}|null */
    public ?array $uploadTarget = null;

    public $uploadStateFile = null;

    public string $uploadLabel = '';

    // ── global upload (any game / any slot) ───────────────────────────────────
    public bool $showGlobalUploadModal = false;

    /** @var array<string,string> */
    public array $consoleOptions = [];

    public string $globalConsole = '';

    /** @var array<string,string> id => title */
    public array $globalGameOptions = [];

    public string $globalGameId = '';

    public string $globalEmulator = 'emulatorjs';

    public int $globalSlot = 1;

    public string $globalLabel = '';

    public $globalStateFile = null;

    public function mount(): void
    {
        if (auth()->check()) {
            app(SyncEmulatorSaveStatesFromDisk::class)->execute(auth()->user());
        }

        $this->loadSaves();
        $this->loadConsoleOptions();
    }

    public function syncFromDisk(): void
    {
        abort_unless(auth()->check(), 401);

        app(SyncEmulatorSaveStatesFromDisk::class)->execute(auth()->user());

        $this->loadSaves();
    }

    public function confirmDelete(int $id, int $slot, string $gameTitle): void
    {
        $this->pendingDeleteId = $id;
        $this->pendingDeleteSlot = $slot;
        $this->pendingDeleteGameTitle = $gameTitle;
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->pendingDeleteId = null;
        $this->pendingDeleteSlot = 0;
        $this->pendingDeleteGameTitle = '';
    }

    public function deleteConfirmed(): void
    {
        $id = $this->pendingDeleteId;
        abort_unless($id, 400);

        $save = EmulatorSaveState::find($id);
        abort_unless($save && $save->user_id === auth()->id(), 403);

        Storage::disk('savestates')->delete($save->disk_path);
        $save->delete();

        $this->cancelDelete();

        $this->loadSaves();
    }

    public function openUploadModal(string $console, string $gameId, string $emulator, int $slot, string $gameTitle): void
    {
        $this->uploadTarget = [
            'console' => $console,
            'game_id' => $gameId,
            'emulator' => $emulator,
            'slot' => $slot,
            'game_title' => $gameTitle,
        ];
        $this->uploadLabel = '';
        $this->uploadStateFile = null;
        $this->resetErrorBag();
        $this->showUploadModal = true;
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->uploadTarget = null;
        $this->uploadLabel = '';
        $this->uploadStateFile = null;
        $this->resetErrorBag();
    }

    public function submitUpload(): void
    {
        abort_unless(is_array($this->uploadTarget), 400);

        $slot = (int) ($this->uploadTarget['slot'] ?? 0);
        abort_unless($slot >= 1 && $slot <= UpsertEmulatorSaveState::MAX_SLOTS, 400);

        $this->validate([
            'uploadStateFile' => ['required', 'file', 'max:102400'],
            'uploadLabel' => ['nullable', 'string', 'max:80'],
            'uploadTarget.console' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'uploadTarget.game_id' => ['required', 'string', 'max:128'],
            'uploadTarget.emulator' => ['required', 'string', Rule::in(['emulatorjs', 'jsdos'])],
            'uploadTarget.slot' => ['required', 'integer', 'min:1', 'max:'.UpsertEmulatorSaveState::MAX_SLOTS],
        ]);

        $label = trim($this->uploadLabel);

        $contents = file_get_contents($this->uploadStateFile->getRealPath());
        if ($contents === false) {
            $this->addError('uploadStateFile', 'Could not read the uploaded file.');

            return;
        }

        app(UpsertEmulatorSaveState::class)->execute(
            auth()->user(),
            $this->uploadTarget['console'],
            $this->uploadTarget['game_id'],
            $this->uploadTarget['emulator'],
            $slot,
            $label !== '' ? $label : null,
            $contents,
        );

        $this->closeUploadModal();
        $this->loadSaves();
    }

    // ── global upload actions ─────────────────────────────────────────────────

    public function openGlobalUploadModal(): void
    {
        $this->globalConsole = '';
        $this->globalGameId = '';
        $this->globalGameOptions = [];
        $this->globalEmulator = 'emulatorjs';
        $this->globalSlot = 1;
        $this->globalLabel = '';
        $this->globalStateFile = null;
        $this->resetErrorBag();
        $this->showGlobalUploadModal = true;
    }

    public function closeGlobalUploadModal(): void
    {
        $this->showGlobalUploadModal = false;
        $this->globalConsole = '';
        $this->globalGameId = '';
        $this->globalGameOptions = [];
        $this->globalStateFile = null;
        $this->resetErrorBag();
    }

    public function updatedGlobalConsole(): void
    {
        $this->globalGameId = '';
        $this->globalGameOptions = [];
        $this->globalStateFile = null;
        $this->resetErrorBag();

        $this->globalEmulator = strtolower($this->globalConsole) === 'pc' ? 'jsdos' : 'emulatorjs';

        if (! $this->globalConsole) {
            return;
        }

        $gameSession = new GameSession;
        $consoles = $gameSession->getFullConsoleData();

        $options = [];
        foreach ($consoles as $console) {
            if (strtolower((string) ($console['short_name'] ?? '')) !== $this->globalConsole) {
                continue;
            }
            foreach ($console['games'] ?? [] as $game) {
                $id = (string) ($game['id'] ?? '');
                if ($id !== '') {
                    $options[$id] = $game['title'] ?? "Game #{$id}";
                }
            }
            break;
        }

        asort($options);
        $this->globalGameOptions = $options;
    }

    public function submitGlobalUpload(): void
    {
        $this->validate([
            'globalConsole' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'globalGameId' => ['required', 'string', 'max:128'],
            'globalEmulator' => ['required', 'string', Rule::in(['emulatorjs', 'jsdos'])],
            'globalSlot' => ['required', 'integer', 'min:1', 'max:'.UpsertEmulatorSaveState::MAX_SLOTS],
            'globalLabel' => ['nullable', 'string', 'max:80'],
            'globalStateFile' => ['required', 'file', 'max:102400'],
        ]);

        $contents = file_get_contents($this->globalStateFile->getRealPath());
        if ($contents === false) {
            $this->addError('globalStateFile', 'Could not read the uploaded file.');

            return;
        }

        $label = trim($this->globalLabel);

        app(UpsertEmulatorSaveState::class)->execute(
            auth()->user(),
            $this->globalConsole,
            $this->globalGameId,
            $this->globalEmulator,
            $this->globalSlot,
            $label !== '' ? $label : null,
            $contents,
        );

        $this->closeGlobalUploadModal();
        $this->loadSaves();
    }

    private function loadConsoleOptions(): void
    {
        $gameSession = new GameSession;
        $consoles = $gameSession->getFullConsoleData();

        $options = [];
        foreach ($consoles as $console) {
            $short = strtolower((string) ($console['short_name'] ?? ''));
            if ($short !== '') {
                $options[$short] = $console['long_name'] ?? strtoupper($short);
            }
        }

        asort($options);
        $this->consoleOptions = $options;
    }

    private function loadSaves(): void
    {
        $saves = EmulatorSaveState::where('user_id', auth()->id())
            ->orderBy('console')
            ->orderBy('game_id')
            ->orderBy('slot')
            ->get();

        $gameMap = $this->buildGameMap();

        $grouped = [];

        foreach ($saves as $save) {
            $console = strtolower($save->console);
            $gameId = (string) $save->game_id;
            $emulator = $save->emulator;

            if (! isset($grouped[$console])) {
                $consoleMeta = $gameMap['consoles'][$console] ?? [];
                $grouped[$console] = [
                    'long_name' => $consoleMeta['long_name'] ?? strtoupper($console),
                    'console_icon' => $consoleMeta['console_icon'] ?? null,
                    'console_logo' => $consoleMeta['console_logo'] ?? null,
                    'games' => [],
                ];
            }

            $gameKey = "{$gameId}_{$emulator}";

            if (! isset($grouped[$console]['games'][$gameKey])) {
                $gameMeta = $gameMap['games'][$console][$gameId] ?? null;
                $grouped[$console]['games'][$gameKey] = [
                    'game_id' => $gameId,
                    'emulator' => $emulator,
                    'title' => $gameMeta['title'] ?? "Game #{$gameId}",
                    'slug' => $gameMeta['slug'] ?? null,
                    'poster' => $gameMeta['poster'] ?? null,
                    'box' => $gameMeta['box'] ?? null,
                    'slots' => array_fill(1, 5, null),
                ];
            }

            $grouped[$console]['games'][$gameKey]['slots'][$save->slot] = [
                'id' => $save->id,
                'slot' => $save->slot,
                'label' => $save->label,
                'size_bytes' => $save->size_bytes,
                'updated_at' => $save->updated_at?->toISOString(),
                'download_url' => route('player-data.save-states.download', $save),
            ];
        }

        $this->grouped = $grouped;
        $this->totalSlots = $saves->count();
        $this->totalGames = $saves->groupBy(fn ($s) => $s->console.'.'.$s->game_id)->count();
        $this->totalConsoles = $saves->groupBy('console')->count();
    }

    private function buildGameMap(): array
    {
        $gameSession = new GameSession;
        $consoles = $gameSession->getFullConsoleData();

        $map = ['games' => [], 'consoles' => []];

        foreach ($consoles as $console) {
            $short = $console['short_name'] ?? null;
            if (! $short) {
                continue;
            }

            $shortKey = strtolower((string) $short);
            $map['consoles'][$shortKey] = [
                'long_name' => $console['long_name'] ?? strtoupper($short),
                'console_icon' => $console['console_icon'] ?? null,
                'console_logo' => $console['console_logo'] ?? null,
            ];

            foreach ($console['games'] ?? [] as $game) {
                $gameId = (string) ($game['id'] ?? '');
                if ($gameId === '') {
                    continue;
                }

                $map['games'][$shortKey][$gameId] = [
                    'title' => $game['title'] ?? null,
                    'slug' => $game['slug'] ?? null,
                    'poster' => $game['poster'] ?? null,
                    'box' => $game['box'] ?? null,
                ];
            }
        }

        return $map;
    }

    public function gameRoute(string $console, array $game): string
    {
        return Tool::gameRoute(
            ['short_name' => $console],
            ['slug' => $game['slug'], 'title' => $game['title']],
        );
    }

    public function render()
    {
        return view('livewire.my-saves');
    }
}
