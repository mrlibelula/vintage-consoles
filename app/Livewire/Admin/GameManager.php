<?php

namespace App\Livewire\Admin;

use App\Models\Console;
use App\Services\GameRepository;
use App\Services\Igdb\IgdbClient;
use App\Services\Igdb\IgdbImage;
use App\Models\Screenshot;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]

class GameManager extends Component
{
    use WithPagination, WithFileUploads;

    private const ROM_EXTENSIONS = [
        'arcade'    => ['zip'],
        'nes'       => ['nes'],
        'snes'      => ['zip', '7z', 'smc'],
        'atari2600' => ['bin', 'a26'],
    ];

    public string $selectedConsole = '';
    public $consoles = [];
    public $games = [];
    public bool $showModal = false;
    public string $modalMode = 'add'; // 'add', 'edit', 'delete'
    public $editingGame = null;
    public string $searchTerm = '';
    public string $sortField = 'title';
    public string $sortDirection = 'asc';
    public int $perPage = 4;

    // Form fields
    public string $formConsole = '';
    public string $originalConsole = '';
    public string $title = '';
    public string $publisher = '';
    public string $release_year = '';
    public string $description = '';
    public string $rating = '0.5';
    public string $rom = '';
    public $romFile = null;
    public string $poster = '';
    public string $cover_image_id = '';
    public string $game_preview = '';
    public string $cartridge = '';
    public bool $multiplayer_support = false;
    public bool $save_state_support = true;
    public bool $is_free = false;
    public array $genres = [['name' => '', 'description' => '']];
    public array $screenshots = [];
    public array $igdbResponse = [];
    public bool $igdbScreenshotsShouldSync = false;

    protected GameRepository $repo;

    public function boot(GameRepository $repo): void
    {
        $this->repo = $repo;
    }

    public function mount(): void
    {
        $this->consoles = Console::orderBy('id')->get();
        $this->perPage  = (int) Session::get('admin-game-manager.perPage', 4);

        if ($this->consoles->isNotEmpty()) {
            $this->selectedConsole = $this->consoles->first()->short_name;
            $this->loadGames();
        }
    }

    public function updatedSelectedConsole(): void
    {
        $this->resetPage();
        $this->searchTerm = '';
        $this->loadGames();
    }

    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(int $value): void
    {
        $this->perPage = max(1, $value);
        Session::put('admin-game-manager.perPage', $this->perPage);
        $this->resetPage();
    }

    public function updatedFormConsole(): void
    {
        $this->romFile = null;

        if ($this->modalMode === 'add') {
            $this->rom = '';
        }
    }

    public function getRomFormatHintProperty(): string
    {
        $console = strtolower($this->formConsole ?: $this->selectedConsole);

        return match ($console) {
            'nes'       => 'NES: .nes',
            'snes'      => 'SNES: .zip, .7z, .smc',
            'arcade'    => 'Arcade: .zip',
            'atari2600' => 'Atari: .bin, .a26',
            default     => 'Allowed file types depend on the selected console.',
        };
    }

    public function getRomFileAcceptProperty(): string
    {
        $console = strtolower($this->formConsole ?: $this->selectedConsole);

        return match ($console) {
            'arcade'    => '.zip',
            'nes'       => '.nes',
            'snes'      => '.zip,.7z,.smc',
            'atari2600' => '.bin,.a26',
            default     => '*',
        };
    }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field)
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->sortField = $field;
    }

    protected function loadGames(): void
    {
        if ($this->selectedConsole) {
            $this->games = $this->repo->getGamesByConsole($this->selectedConsole);
        }
    }

    public function getFilteredGamesProperty()
    {
        $games = $this->games;

        if (! empty($this->searchTerm)) {
            $games = $games->filter(fn ($game) =>
                stripos($game->title, $this->searchTerm) !== false ||
                stripos($game->publisher ?? '', $this->searchTerm) !== false
            )->values();
        }

        return $games->sortBy(
            fn ($game) => in_array($this->sortField, ['release_year', 'rating', 'id'])
                ? (float) ($game->{$this->sortField} ?? 0)
                : strtolower((string) ($game->{$this->sortField} ?? '')),
            SORT_REGULAR,
            $this->sortDirection === 'desc'
        )->values();
    }

    public function getPaginatedGamesProperty(): LengthAwarePaginator
    {
        $filtered = $this->filteredGames;
        $total    = $filtered->count();
        $page     = (int) $this->getPage();
        $perPage  = max(1, $this->perPage);
        $offset   = max(0, ($page - 1) * $perPage);

        return new LengthAwarePaginator(
            $filtered->slice($offset, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->modalMode     = 'add';
        $this->showModal     = true;
        $this->formConsole   = $this->selectedConsole;
        $this->originalConsole = $this->selectedConsole;
        $this->dispatch('loader-top-off');
    }

    public function openEditModal(int $gameId): void
    {
        $game = $this->repo->getGame($this->selectedConsole, $gameId);

        if ($game) {
            $this->editingGame   = $game;
            $this->fillForm($game);
            $this->modalMode     = 'edit';
            $this->showModal     = true;
            $this->formConsole   = $this->selectedConsole;
            $this->originalConsole = $this->selectedConsole;
        }
        $this->dispatch('loader-top-off');
    }

    public function openDeleteModal(int $gameId): void
    {
        $game = $this->repo->getGame($this->selectedConsole, $gameId);

        if ($game) {
            $this->editingGame = $game;
            $this->modalMode   = 'delete';
            $this->showModal   = true;
        }
        $this->dispatch('loader-top-off');
    }

    public function closeModal(): void
    {
        $this->showModal   = false;
        $this->editingGame = null;
        $this->resetForm();
        $this->js('document.body.style.overflow = "auto"');
        $this->dispatch('loader-top-off');
    }

    public function saveGame(): void
    {
        $this->validate([
            'title'        => 'required|string|max:255',
            'publisher'    => 'required|string|max:255',
            'release_year' => 'required|numeric|min:1970|max:' . (date('Y') + 5),
            'description'  => 'required|string',
            'rating'       => 'required|numeric|min:0|max:1',
            'poster'       => ['nullable', 'string', 'max:500', fn ($a, $v, $f) => $this->validateImagePath($v, $a, $f)],
            'game_preview' => ['nullable', 'string', 'max:500', fn ($a, $v, $f) => $this->validateImagePath($v, $a, $f)],
            'cartridge'    => ['nullable', 'string', 'max:500', fn ($a, $v, $f) => $this->validateImagePath($v, $a, $f)],
            'screenshots'  => 'array',
            'screenshots.*.thumb_url' => ['nullable', 'string', 'max:500', fn ($a, $v, $f) => $this->validateImagePath($v, $a, $f)],
            'screenshots.*.full_url' => ['nullable', 'string', 'max:500', fn ($a, $v, $f) => $this->validateImagePath($v, $a, $f)],
        ]);

        $consoleForValidation = $this->formConsole ?: $this->selectedConsole;
        $isPc = strtolower($consoleForValidation) === 'pc';

        // ROM validation / file upload
        $romFilename = $this->rom;

        if ($isPc) {
            $romValidation = $this->validateRomUrl($this->rom);
            if (! $romValidation['valid']) {
                $this->dispatchRomError($romValidation['message']);
                return;
            }
        } else {
            if ($this->romFile) {
                $result = $this->handleRomUpload($consoleForValidation);
                if (! $result['valid']) {
                    $this->dispatchRomError($result['message']);
                    return;
                }
                $romFilename = $result['filename'];
            } elseif (empty($this->rom) && $this->modalMode === 'add') {
                $this->dispatchRomError('A ROM file is required when adding a new game.');
                return;
            }
        }

        // Genres
        $processedGenres = array_values(array_filter(
            $this->genres,
            fn ($g) => ! empty($g['name'])
        ));
        foreach ($processedGenres as &$genre) {
            $genre['name'] = Str::slug($genre['name']);
        }
        unset($genre);

        $gameData = [
            'title'               => $this->title,
            'publisher'           => $this->publisher,
            'release_year'        => $this->release_year,
            'description'         => $this->description,
            'rating'              => (string) $this->rating,
            'rom'                 => $romFilename,
            'poster'              => $this->poster ?: null,
            'cover_image_id'      => $this->cover_image_id ?: null,
            'game_preview'        => $this->game_preview ?: null,
            'cartridge'           => $this->cartridge ?: null,
            'multiplayer_support' => $this->multiplayer_support,
            'save_state_support'  => $this->save_state_support,
            'is_free'             => $this->is_free,
            'genres'              => $processedGenres,
            'needs_igdb_sync'     => empty($this->igdbResponse),
        ];

        if (! empty($this->igdbResponse)) {
            $gameData['igdb_response'] = $this->igdbResponse;
        }

        if ($this->modalMode === 'add') {
            $game    = $this->repo->addGame($this->formConsole, $gameData);
            $success = $game !== false;
            $message = 'Game added successfully!';
        } else {
            if ($this->formConsole !== $this->originalConsole) {
                // Move: delete from original, add to target
                $moveData       = $gameData;
                $this->repo->deleteGame($this->originalConsole, $this->editingGame->id);
                $game    = $this->repo->addGame($this->formConsole, $moveData);
                $success = $game !== false;
                $message = 'Game moved and updated successfully!';
            } else {
                $success = $this->repo->updateGame(
                    $this->originalConsole,
                    $this->editingGame->id,
                    $gameData
                );
                $message = 'Game updated successfully!';
            }
        }

        // Sync screenshots from IGDB response if present
        if ($success && $this->igdbScreenshotsShouldSync && ! empty($this->igdbResponse)) {
            $savedGame = $this->repo->getGame(
                $this->formConsole ?: $this->selectedConsole,
                $game->id ?? $this->editingGame->id
            );
            if ($savedGame) {
                $this->syncScreenshotsFromIgdb($savedGame, $this->igdbResponse);
            }
        } elseif ($success) {
            $savedGame = $this->repo->getGame(
                $this->formConsole ?: $this->selectedConsole,
                $game->id ?? $this->editingGame->id
            );
            if ($savedGame) {
                $this->syncScreenshotsFromForm($savedGame, $this->screenshots);
            }
        }

        if ($success) {
            $this->selectedConsole = $this->formConsole ?: $this->selectedConsole;
            $this->loadGames();
            $this->closeModal();
            session()->flash('success', $message);
        } else {
            session()->flash('error', 'Failed to save game. Please try again.');
        }

        $this->dispatch('loader-top-off');
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
    }

    public function deleteGame(): void
    {
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-on"))');

        if ($this->editingGame) {
            $success = $this->repo->deleteGame($this->selectedConsole, $this->editingGame->id);

            if ($success) {
                $this->loadGames();
                $this->closeModal();
                session()->flash('success', 'Game deleted successfully!');
            } else {
                session()->flash('error', 'Failed to delete game. Please try again.');
            }
        }

        $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
    }

    public function addGenre(): void
    {
        $this->genres[] = ['name' => '', 'description' => ''];
    }

    public function addScreenshot(): void
    {
        $this->screenshots[] = [
            'id'            => null,
            'igdb_image_id' => null,
            'thumb_url'     => '',
            'full_url'      => '',
        ];
    }

    public function removeScreenshot(int $index): void
    {
        if (! isset($this->screenshots[$index])) {
            return;
        }

        unset($this->screenshots[$index]);
        $this->screenshots = array_values($this->screenshots);
    }

    public function removeGenre(int $index): void
    {
        if (count($this->genres) > 1) {
            unset($this->genres[$index]);
            $this->genres = array_values($this->genres);
        }
    }

    /**
     * Fetch game metadata from the IGDB API (replaces the old AI Fill).
     */
    public function fetchGameDataFromIgdb(): void
    {
        if (empty(trim($this->title))) {
            $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
            return;
        }

        try {
            $consoleKey = $this->formConsole ?: $this->selectedConsole;
            $console    = $this->consoles->firstWhere('short_name', $consoleKey);

            if (! $console || ! $console->igdb_platform_id) {
                throw new \RuntimeException('No IGDB platform ID found for this console.');
            }

            $igdb    = app(IgdbClient::class);
            $payload = $igdb->fetchGameForConsole($this->title, $console->igdb_platform_id);

            if (! $payload) {
                session()->flash('error', "No IGDB match found for \"{$this->title}\" on {$console->short_name}.");
                $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
                return;
            }

            // Fill form fields
            $this->igdbResponse = $payload;

            $this->description = $payload['summary'] ?? $this->description;
            $this->rating      = isset($payload['total_rating'])
                ? (string) round($payload['total_rating'] / 100, 4)
                : $this->rating;

            if (isset($payload['first_release_date'])) {
                $this->release_year = (string) date('Y', $payload['first_release_date']);
            }

            // Publisher from involved_companies
            foreach ($payload['involved_companies'] ?? [] as $ic) {
                if (! empty($ic['publisher']) && ! empty($ic['company']['name'])) {
                    $this->publisher = $ic['company']['name'];
                    break;
                }
            }

            // Poster
            $cover = $payload['cover'] ?? null;
            if (is_array($cover) && ! empty($cover['image_id'])) {
                $this->cover_image_id = $cover['image_id'];
                $this->poster         = IgdbImage::url($cover['image_id'], IgdbImage::COVER_BIG, 'webp');
            }

            // Genres
            if (! empty($payload['genres'])) {
                $this->genres = array_map(
                    fn ($g) => ['name' => Str::slug($g['name'] ?? ''), 'description' => ''],
                    $payload['genres']
                );
            }

            $this->screenshots = $this->screenshotsFromIgdbPayload($payload);
            $this->igdbScreenshotsShouldSync = true;

            $this->js('window.dispatchEvent(new CustomEvent("api-success"))');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to fetch from IGDB: ' . $e->getMessage());
        } finally {
            $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resetForm(): void
    {
        $this->title               = '';
        $this->publisher           = '';
        $this->release_year        = '';
        $this->description         = '';
        $this->rating              = '0.5';
        $this->rom                 = '';
        $this->romFile             = null;
        $this->poster              = '';
        $this->cover_image_id      = '';
        $this->game_preview        = '';
        $this->cartridge           = '';
        $this->multiplayer_support = false;
        $this->save_state_support  = true;
        $this->is_free             = false;
        $this->genres              = [['name' => '', 'description' => '']];
        $this->screenshots         = [];
        $this->igdbResponse        = [];
        $this->igdbScreenshotsShouldSync = false;
    }

    private function fillForm(\App\Models\Game $game): void
    {
        $this->title               = $game->title;
        $this->publisher           = $game->publisher ?? '';
        $this->release_year        = $game->release_year ?? '';
        $this->description         = $game->description ?? '';
        $this->rating              = (string) ($game->rating ?? '0.5');
        $this->rom                 = $game->rom ?? '';
        $this->poster              = $game->poster ?? '';
        $this->cover_image_id      = $game->cover_image_id ?? '';
        $this->game_preview        = $game->game_preview ?? '';
        $this->cartridge           = $game->cartridge ?? '';
        $this->multiplayer_support = (bool) $game->multiplayer_support;
        $this->save_state_support  = (bool) $game->save_state_support;
        $this->is_free             = (bool) $game->is_free;
        $this->genres              = $game->genres->isNotEmpty()
            ? $game->genres->map(fn ($g) => ['name' => $g->name, 'description' => $g->description ?? ''])->toArray()
            : [['name' => '', 'description' => '']];
        $this->screenshots         = $game->screenshots
            ->map(fn ($screenshot) => [
                'id'            => $screenshot->id,
                'igdb_image_id' => $screenshot->igdb_image_id,
                'thumb_url'     => $screenshot->thumb_url,
                'full_url'      => $screenshot->full_url,
            ])
            ->values()
            ->all();
        $this->igdbResponse        = $game->igdb_response ?? [];
        $this->igdbScreenshotsShouldSync = false;
    }

    private function validateRomUrl(string $url): array
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'message' => 'For PC/MS-DOS games, ROM must be a valid .jsdos URL.'];
        }
        if (! str_ends_with(strtolower($url), '.jsdos')) {
            return ['valid' => false, 'message' => 'PC/MS-DOS games must use .jsdos file format.'];
        }
        try {
            $headers = get_headers($url, 1);
            if (! $headers || ! str_contains($headers[0], '200')) {
                return ['valid' => false, 'message' => 'The .jsdos URL is not accessible.'];
            }
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Unable to verify .jsdos URL: ' . $e->getMessage()];
        }
        return ['valid' => true];
    }

    private function handleRomUpload(string $consoleShortName): array
    {
        $consoleDir = strtolower($consoleShortName);
        $allowedExtensions = self::ROM_EXTENSIONS;

        $ext = strtolower($this->romFile->getClientOriginalExtension());

        if (isset($allowedExtensions[$consoleDir]) && ! in_array($ext, $allowedExtensions[$consoleDir], true)) {
            return [
                'valid'   => false,
                'message' => "Invalid file extension for {$consoleDir}. Allowed: " .
                             implode(', ', $allowedExtensions[$consoleDir]) . ". Got: {$ext}",
            ];
        }

        $filename = $this->romFile->getClientOriginalName();
        $this->romFile->storeAs("games/{$consoleDir}", $filename, 'data');

        return ['valid' => true, 'filename' => $filename];
    }

    private function validateImagePath(string $value, string $attribute, callable $fail): void
    {
        if (empty($value)) {
            return;
        }
        $value = trim($value);
        if (! filter_var($value, FILTER_VALIDATE_URL) && ! str_starts_with($value, '/')) {
            $fail("The {$attribute} must be a valid URL or a path starting with \"/\".");
        }
    }

    private function dispatchRomError(string $message): void
    {
        $this->js('window.dispatchEvent(new CustomEvent("rom-error", { detail: { message: "' . addslashes($message) . '" } }))');
        $this->js('window.dispatchEvent(new CustomEvent("loader-top-off"))');
    }

    private function syncScreenshotsFromForm(\App\Models\Game $game, array $rows): void
    {
        $normalized = [];

        foreach ($rows as $row) {
            $thumb = trim((string) ($row['thumb_url'] ?? ''));
            $full  = trim((string) ($row['full_url'] ?? ''));

            if ($thumb === '' && $full === '') {
                continue;
            }

            if ($thumb === '') {
                $thumb = $full;
            }

            if ($full === '') {
                $full = $thumb;
            }

            $normalized[] = [
                'igdb_image_id' => $row['igdb_image_id'] ?? null,
                'thumb_url'     => $thumb,
                'full_url'      => $full,
                'position'      => count($normalized),
            ];
        }

        $game->screenshots()->delete();

        foreach ($normalized as $row) {
            $game->screenshots()->create($row);
        }
    }

    private function screenshotsFromIgdbPayload(array $payload): array
    {
        $screenshots = $payload['screenshots'] ?? [];
        if (! is_array($screenshots) || empty($screenshots)) {
            return [];
        }

        $rows = [];

        foreach ($screenshots as $shot) {
            if (! is_array($shot) || empty($shot['image_id'])) {
                continue;
            }

            $imageId = $shot['image_id'];
            $rows[] = [
                'id'            => null,
                'igdb_image_id' => $imageId,
                'thumb_url'     => IgdbImage::screenshotThumb($imageId),
                'full_url'      => IgdbImage::fullScreenshot($imageId),
            ];
        }

        return $rows;
    }

    private function syncScreenshotsFromIgdb(\App\Models\Game $game, array $igdbPayload): void
    {
        $screenshots = $igdbPayload['screenshots'] ?? [];
        if (empty($screenshots)) {
            return;
        }

        $game->screenshots()->delete();

        $rows = [];
        foreach ($screenshots as $index => $shot) {
            if (! is_array($shot) || empty($shot['image_id'])) {
                continue;
            }
            $imageId = $shot['image_id'];
            $rows[] = [
                'game_id'       => $game->id,
                'igdb_image_id' => $imageId,
                'thumb_url'     => IgdbImage::screenshotThumb($imageId),
                'full_url'      => IgdbImage::fullScreenshot($imageId),
                'position'      => $index,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        if (! empty($rows)) {
            Screenshot::insert($rows);
        }
    }

    public function render()
    {
        return view('livewire.admin.game-manager');
    }

    public function rendered(): void
    {
        $this->dispatch('loader-top-off');
    }
}
