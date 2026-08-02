<?php

namespace App\Livewire;

use App\Actions\SyncEmulatorSaveStatesFromDisk;
use App\Actions\UpsertEmulatorSaveState;
use App\Models\EmulatorSaveState;
use App\Service\Tool;
use App\Services\GameRepository;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class MySaves extends Component
{
    use WithFileUploads;

    private const SAVE_STATE_UNSUPPORTED_CONSOLES = ['pc'];

    public array $grouped = [];

    public string $gameSearch = '';

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

        $disk = Storage::disk('savestates');
        $disk->delete($save->disk_path);
        if ($save->backup_disk_path) {
            $disk->delete($save->backup_disk_path);
        } else {
            $disk->delete("{$save->disk_path}.backup");
        }
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
        if ($contents === false || $contents === '') {
            $this->addError('uploadStateFile', 'Could not read the uploaded file (empty or unreadable).');

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

        if (in_array(strtolower($this->globalConsole), self::SAVE_STATE_UNSUPPORTED_CONSOLES, true)) {
            $this->addError('globalConsole', 'Save states are not supported for this console.');
            $this->globalConsole = '';

            return;
        }

        $games   = app(GameRepository::class)->getGamesByConsole($this->globalConsole);
        $options = [];
        foreach ($games as $game) {
            if ($game->slug) {
                $options[$game->slug] = $game->title;
            }
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

        if (in_array(strtolower($this->globalConsole), self::SAVE_STATE_UNSUPPORTED_CONSOLES, true)) {
            $this->addError('globalConsole', 'Save states are not supported for this console.');

            return;
        }

        $contents = file_get_contents($this->globalStateFile->getRealPath());
        if ($contents === false || $contents === '') {
            $this->addError('globalStateFile', 'Could not read the uploaded file (empty or unreadable).');

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
        $consoles = app(GameRepository::class)->getConsoles();

        $options = [];
        foreach ($consoles as $console) {
            $short = strtolower((string) $console->short_name);
            if (in_array($short, self::SAVE_STATE_UNSUPPORTED_CONSOLES, true)) {
                continue;
            }
            if ($short !== '') {
                $options[$short] = $console->long_name ?? strtoupper($short);
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
                    'game_slug'    => $gameSlug,
                    'title'        => $gameMeta['title'] ?? $gameSlug,
                    'slug'         => $gameMeta['slug'] ?? $gameSlug,
                    'poster'       => $gameMeta['poster'] ?? null,
                    'game_preview' => $gameMeta['game_preview'] ?? null,
                    'slots'        => array_fill(1, 5, null),
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
        $repo     = app(GameRepository::class);
        $consoles = $repo->getConsoles();
        $map      = ['games' => [], 'consoles' => []];

        foreach ($consoles as $console) {
            $shortKey = strtolower((string) $console->short_name);

            $map['consoles'][$shortKey] = [
                'long_name'    => $console->long_name ?? strtoupper($shortKey),
                'console_icon' => $console->console_icon ?? null,
                'console_logo' => $console->console_logo ?? null,
            ];

            $games = $repo->getGamesByConsole($shortKey);
            foreach ($games as $game) {
                $slug = (string) $game->slug;
                if ($slug === '') {
                    continue;
                }

                $meta = [
                    'title'        => $game->title,
                    'slug'         => $slug,
                    'poster'       => $game->poster,
                    'game_preview' => $game->game_preview,
                ];

                $map['games'][$shortKey][$slug] = $meta;

                // Back-compat: older save rows may reference numeric IDs as game_slug.
                $idKey = (string) $game->id;
                if (! isset($map['games'][$shortKey][$idKey])) {
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

    public function clearGameSearch(): void
    {
        $this->gameSearch = '';
    }

    public function getFilteredGroupedProperty(): array
    {
        $query = trim($this->gameSearch);
        if ($query === '') {
            return $this->grouped;
        }

        $needle = mb_strtolower($query);
        $filtered = [];

        foreach ($this->grouped as $consoleShort => $consoleData) {
            $matchingGames = [];

            foreach ($consoleData['games'] as $gameKey => $game) {
                $title = mb_strtolower((string) ($game['title'] ?? ''));
                $slug = mb_strtolower((string) ($game['game_slug'] ?? ''));
                $consoleLabel = mb_strtolower((string) ($consoleData['long_name'] ?? $consoleShort));

                if (
                    str_contains($title, $needle)
                    || str_contains($slug, $needle)
                    || str_contains(mb_strtolower($consoleShort), $needle)
                    || str_contains($consoleLabel, $needle)
                ) {
                    $matchingGames[$gameKey] = $game;
                }
            }

            if ($matchingGames !== []) {
                $filtered[$consoleShort] = $consoleData;
                $filtered[$consoleShort]['games'] = $matchingGames;
            }
        }

        return $filtered;
    }

    public function render()
    {
        return view('livewire.my-saves');
    }
}
