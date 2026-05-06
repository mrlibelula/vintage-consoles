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

    /** @var array{console:string,game_slug:string,slot:int,game_title:string}|null */
    public ?array $uploadTarget = null;

    public $uploadStateFile = null;

    public string $uploadLabel = '';

    // ── global upload (any game / any slot) ───────────────────────────────────
    public bool $showGlobalUploadModal = false;

    /** @var array<string,string> */
    public array $consoleOptions = [];

    public string $globalConsole = '';

    /** @var array<string,string> slug => title */
    public array $globalGameOptions = [];

    public string $globalGameSlug = '';

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
        $requestUserId = (string) auth()->id();
        $ownerUserId = (string) ($save?->user_id ?? '');
        abort_unless($save && $ownerUserId === $requestUserId, 403);

        Storage::disk('savestates')->delete($save->disk_path);
        $save->delete();

        $this->cancelDelete();

        $this->loadSaves();
    }

    public function openUploadModal(string $console, string $gameSlug, int $slot, string $gameTitle): void
    {
        $this->uploadTarget = [
            'console'    => $console,
            'game_slug'  => $gameSlug,
            'slot'       => $slot,
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
            'uploadStateFile'           => ['required', 'file', 'max:102400'],
            'uploadLabel'               => ['nullable', 'string', 'max:80'],
            'uploadTarget.console'      => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'uploadTarget.game_slug'    => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
            'uploadTarget.slot'         => ['required', 'integer', 'min:1', 'max:'.UpsertEmulatorSaveState::MAX_SLOTS],
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
            $this->uploadTarget['game_slug'],
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
        $this->globalGameSlug = '';
        $this->globalGameOptions = [];
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
        $this->globalGameSlug = '';
        $this->globalGameOptions = [];
        $this->globalStateFile = null;
        $this->resetErrorBag();
    }

    public function updatedGlobalConsole(): void
    {
        $this->globalGameSlug = '';
        $this->globalGameOptions = [];
        $this->globalStateFile = null;
        $this->resetErrorBag();

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
                $slug = (string) ($game['slug'] ?? \Illuminate\Support\Str::slug($game['title'] ?? ''));
                if ($slug !== '') {
                    $options[$slug] = $game['title'] ?? $slug;
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
            'globalConsole'   => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_-]+$/'],
            'globalGameSlug'  => ['required', 'string', 'max:128', 'regex:/^[A-Za-z0-9_-]+$/'],
            'globalSlot'      => ['required', 'integer', 'min:1', 'max:'.UpsertEmulatorSaveState::MAX_SLOTS],
            'globalLabel'     => ['nullable', 'string', 'max:80'],
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
            $this->globalGameSlug,
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
            ->orderBy('game_slug')
            ->orderBy('slot')
            ->get();

        $gameMap = $this->buildGameMap();

        $grouped = [];

        foreach ($saves as $save) {
            $console   = strtolower($save->console);
            $gameSlug  = (string) $save->game_slug;

            if (! isset($grouped[$console])) {
                $consoleMeta = $gameMap['consoles'][$console] ?? [];
                $grouped[$console] = [
                    'long_name'     => $consoleMeta['long_name'] ?? strtoupper($console),
                    'console_icon'  => $consoleMeta['console_icon'] ?? null,
                    'console_logo'  => $consoleMeta['console_logo'] ?? null,
                    'games'         => [],
                ];
            }

            if (! isset($grouped[$console]['games'][$gameSlug])) {
                $gameMeta = $gameMap['games'][$console][$gameSlug] ?? null;
                $grouped[$console]['games'][$gameSlug] = [
                    'game_slug' => $gameSlug,
                    'title'     => $gameMeta['title'] ?? $gameSlug,
                    'slug'      => $gameMeta['slug'] ?? $gameSlug,
                    'poster'    => $gameMeta['poster'] ?? null,
                    'box'       => $gameMeta['box'] ?? null,
                    'slots'     => array_fill(1, 5, null),
                ];
            }

            $grouped[$console]['games'][$gameSlug]['slots'][$save->slot] = [
                'id'           => $save->id,
                'slot'         => $save->slot,
                'label'        => $save->label,
                'size_bytes'   => $save->size_bytes,
                'updated_at'   => $save->updated_at?->toISOString(),
                'download_url' => route('player-data.save-states.download', $save),
            ];
        }

        $this->grouped = $grouped;
        $this->totalSlots = $saves->count();
        $this->totalGames = $saves->groupBy(fn ($s) => $s->console.'.'.$s->game_slug)->count();
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
                'long_name'    => $console['long_name'] ?? strtoupper($short),
                'console_icon' => $console['console_icon'] ?? null,
                'console_logo' => $console['console_logo'] ?? null,
            ];

            foreach ($console['games'] ?? [] as $game) {
                $idKey = isset($game['id']) && $game['id'] !== null ? (string) $game['id'] : null;
                $slug = (string) ($game['slug'] ?? \Illuminate\Support\Str::slug($game['title'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $meta = [
                    'title'  => $game['title'] ?? null,
                    'slug'   => $slug,
                    'poster' => $game['poster'] ?? null,
                    'box'    => $game['box'] ?? null,
                ];

                // Primary lookup by slug (new schema).
                $map['games'][$shortKey][$slug] = $meta;

                // Back-compat: older production rows may store numeric IDs in `game_slug`.
                if ($idKey && ! isset($map['games'][$shortKey][$idKey])) {
                    $map['games'][$shortKey][$idKey] = $meta;
                }
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
