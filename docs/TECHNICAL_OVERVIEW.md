# Vintage Consoles — Technical Project Overview

> A comprehensive technical reference.

---

## Project Summary

**Vintage Consoles** is a full-stack web application that delivers a browser-based retro gaming experience. Users can play classic games from multiple vintage gaming platforms (NES, SNES, Arcade, Atari 2600, and MS-DOS/PC) directly in their browser without any downloads or installations. The application features authenticated cloud save states, custom control mappings, per-game chat, YouTube walkthrough resume, OAuth authentication, an admin panel with IGDB-assisted metadata entry and site backup/restore, and a modern responsive UI with dark mode, custom fonts, and pixel-art cursors.

The game catalog runs on **MySQL via Eloquent**. A legacy `vintage-consoles.json` file remains only as the **seed/import source** for `php artisan vintage:import`.

---

## Core Technology Stack

### Backend Framework
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | ^8.1 | Server-side language |
| **Laravel** | ^10.10 | MVC Framework |
| **Livewire** | ^3.0 | Full-stack reactive components |
| **Laravel Jetstream** | ^4.0 | Authentication scaffolding (API tokens, 2FA) |
| **Laravel Sanctum** | ^3.2 | SPA & API token authentication |
| **Laravel Socialite** | ^5.21 | OAuth providers (Google login) |

### Frontend Stack
| Technology | Version | Purpose |
|------------|---------|---------|
| **TailwindCSS** | ^3.1.0 | Utility-first CSS framework |
| **Alpine.js** | (via Livewire) | Lightweight JS framework |
| **Vite** | ^4.0.0 | Frontend build tool |
| **Swiper** | ^11.2.10 | Touch-enabled carousel/slider |
| **pixelarticons** | ^2.1.0 | Pixel-art icon SVGs |

### Emulation Engines
| Engine | Version | Consoles Supported |
|--------|---------|-------------------|
| **EmulatorJS** | 4.2.3 (CDN) | NES, SNES, Arcade, Atari 2600 |
| **JS-DOS** | 8.3.20 (CDN) | MS-DOS/PC games |

### Metadata & Integrations
| Service | Package | Purpose |
|---------|---------|---------|
| **IGDB** | marcreichel/igdb-laravel ^4.3 | Game metadata, covers, screenshots, genres |
| **OpenAI PHP SDK** | openai-php/laravel ^0.11.0 | Present in composer; **unused** (replaced by IGDB fill) |

### Development & Testing
| Tool | Version | Purpose |
|------|---------|---------|
| **Pest PHP** | ^2.36 | Modern PHP testing framework |
| **PHPUnit** | ^10.1 | Test runner |
| **Laravel Pint** | ^1.0 | Code style fixer (PSR-12) |
| **Laravel Debugbar** | ^3.9 | Debug toolbar |
| **Mockery** | ^1.4.4 | Mock object framework |
| **FakerPHP** | ^1.9.1 | Fake data generation |

### Additional Laravel Packages
| Package | Version | Purpose |
|---------|---------|---------|
| **Spatie Laravel Permission** | ^6.3 | Role & permission management |
| **Spatie Laravel Collection Macros** | ^7.13 | Extended collection methods |
| **Guzzle HTTP** | ^7.2 | HTTP client |

---

## Architecture Overview

### Application Pattern
The project follows **Laravel's MVC architecture** enhanced with **Livewire's component-based reactive pattern**. Catalog and user data live in MySQL; ROMs, chat transcripts, save-state binaries, and backups live on dedicated storage disks.

```
┌──────────────────────────────────────────────────────────────────┐
│                           Browser                                 │
│  ┌─────────────┐  ┌──────────────┐  ┌──────────────────────────┐ │
│  │  Livewire   │  │  EmulatorJS  │  │         JS-DOS           │ │
│  │ Components  │  │   (iframe)   │  │        (iframe)          │ │
│  └──────┬──────┘  └──────┬───────┘  └────────────┬─────────────┘ │
└─────────┼────────────────┼───────────────────────┼───────────────┘
          │ AJAX/Livewire  │ ROM + SharedArrayBuffer│ .jsdos Bundle
┌─────────▼────────────────▼───────────────────────▼───────────────┐
│                      Laravel Backend                              │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────────────────┐ │
│  │   Livewire   │  │ Controllers  │  │ Services                │ │
│  │  Components  │  │ (saves, YT,  │  │ GameRepository, Backup, │ │
│  │              │  │  controls)   │  │ AppFont, Igdb*          │ │
│  └──────┬───────┘  └──────┬───────┘  └────────────┬────────────┘ │
│         │                 │                        │               │
│  ┌──────▼─────────────────▼────────────────────────▼────────────┐ │
│  │ MySQL (Eloquent)                                             │ │
│  │ consoles · games · genres · screenshots · save states ·      │ │
│  │ control settings · fonts · settings · YT progress · users    │ │
│  ├──────────────────────────────────────────────────────────────┤ │
│  │ Storage disks                                                │ │
│  │ data: ROMs, chat JSON, import JSON                           │ │
│  │ savestates: binary save files                                │ │
│  │ fonts: public font files                                     │ │
│  │ local: backups/*.zip, migration-docs                         │ │
│  └──────────────────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────────┘
```

**Import path (ops only):** `storage/data/vintage-consoles.json` → `php artisan vintage:import` → MySQL (+ IGDB enrichment).

### Directory Structure

```
app/
├── Actions/                    # Fortify/Jetstream + UpsertEmulatorSaveState, SyncEmulatorSaveStatesFromDisk
├── Console/Commands/           # vintage:import (JSON → MySQL + IGDB)
├── Http/
│   ├── Controllers/            # OAuth, EmulatorSaveState, EmulatorControlSetting, YoutubeVideoProgress
│   └── Middleware/             # Admin, Auth, HTTPS, CrossOriginIsolation
├── Livewire/                   # Reactive UI components
│   ├── Admin/
│   │   ├── GameManager.php     # Catalog CRUD + IGDB fill
│   │   ├── FontManager.php     # App font upload/activation
│   │   └── BackupManager.php   # Site backup/restore
│   ├── Chat.php                # Per-game chat (file-backed)
│   ├── Dashboard.php           # Console/game browser home
│   ├── DosPlayer.php / JsPlayer.php
│   ├── Play.php                # Game detail + session dock
│   ├── MySaves.php             # Cloud save-state manager
│   ├── Genres.php / Publishers.php
│   ├── Navigation.php, About.php, GameCard*.php, OrderBy*.php, …
│   └── Concerns/               # Sorting, toasts, carousel traits
├── Models/                     # Eloquent catalog + emulator + app models
├── Service/                    # Legacy helpers (Tool still used; Game/GameManager residual)
├── Services/                   # Active domain services
│   ├── GameRepository.php
│   ├── BackupService.php
│   ├── AppFontService.php
│   └── Igdb/                   # IgdbClient, GameImporter, IgdbImage
├── Support/                    # GameIgdbPresenter, YouTubeUrl, BrowserLabel
├── View/Components/            # Blade view components
└── helpers.php
```

---

## Key Features & Implementation Details

### 1. Browser-Based Emulation

#### EmulatorJS Integration (8/16-bit Consoles)
The `JsPlayer` Livewire component configures EmulatorJS and wires cloud save-state endpoints:

```php
// app/Livewire/JsPlayer.php
public function mount(string $enc_json_game, string $console_short_name)
{
    $json_game = Tool::decode($enc_json_game);
    $game_data = json_decode($json_game, true);
    $this->title = $game_data['title'];
    $this->short_name = $console_short_name;
    $this->game_url = route('game.serve', [
        'console' => $console_short_name,
        'filename' => $game_data['rom'],
    ]);
    $this->save_state_config = $this->buildSaveStateConfig($game_data, $console_short_name);
}
```

**Key configuration points:**
- Global `EJS_*` variables configure core, ROM URL, theme, and game ID
- EmulatorJS runtime pinned to CDN **v4.2.3** (`cdn.emulatorjs.org`)
- Custom overlay loader fades on `EJS_onGameStart` / `EJS_ready`
- Player routes use `cross-origin-isolation` middleware (COOP/COEP) so SharedArrayBuffer works for threaded WASM cores
- Client-side save-state manager (`resources/js/emulation/SaveStateManager.js`) syncs slots with `/player-data/save-states`

#### JS-DOS Integration (MS-DOS/PC)
The `DosPlayer` component loads **JS-DOS 8.3.20** from jsDelivr for `.jsdos` bundles. PC titles do not use cloud save states (`MySaves` treats PC as unsupported for save-state sync).

### 2. ROM Delivery Pipeline

```php
// routes/web.php
Route::get('/games/serve/{console}/{filename}', function ($console, $filename) {
    $gamePath = storage_path("data/games/{$console}/{$filename}");

    return response()->file($gamePath, [
        'Content-Type'                 => mime_content_type($gamePath),
        'Cache-Control'                => 'public, max-age=31536000',
        'Cross-Origin-Resource-Policy' => 'cross-origin', // required under COEP
    ]);
})->name('game.serve');
```

**Security & Performance:**
- ROMs served from `storage/data/games/{console}/` (not publicly listed)
- MIME type auto-detection with octet-stream fallback
- Aggressive browser/CDN caching (1 year)
- CORP header so COEP-isolated player pages can load same-origin ROMs

### 3. Catalog via Eloquent (`GameRepository`)

Runtime catalog access goes through `App\Services\GameRepository` (registered as a singleton). There is **no** `GameSession` session-footprint layer anymore — consoles and games are queried from MySQL with eager loading as needed.

```php
// app/Services/GameRepository.php
public function getConsole(string $shortName): ?Console
{
    return Console::where('short_name', $shortName)
        ->with(['games' => function ($q) {
            $q->with(['genres', 'screenshots' => fn ($q2) => $q2->orderBy('position')])
              ->orderBy('title');
        }])
        ->first();
}
```

**Responsibilities:** list/get consoles and games, genre/publisher browsing, search/sort, admin CRUD helpers, IGDB screenshot sync.

### 4. Cloud Save States & Control Settings

Authenticated players get **5 save slots** per game (EmulatorJS consoles), stored as:
- **DB rows** in `emulator_save_states` (metadata, checksums, optional previous-state backup columns)
- **Binary files** on the `savestates` disk

Related endpoints live under `player-data` (web middleware + `auth`), not `routes/api.php`:

| Endpoint group | Purpose |
|----------------|---------|
| `/player-data/save-states` | Index, store, update label, destroy, download, restore backup |
| `/player-data/control-settings` | Get/put per-user emulator control profiles (JSON) |
| `/player-data/youtube-progress/{game}` | Resume positions for walkthrough videos |

`MySaves` Livewire page (`/my/saves`) lists, uploads, deletes, and syncs save states from disk. The Play page session bar exposes slot dots, arcade Coin/Start helpers, hotkeys, and cloud-save CTAs.

### 5. Real-Time Chat System

Per-game chat remains **file-based JSON** on the `data` disk (intentionally not MySQL):

```php
// app/Livewire/Chat.php
public function chatFilePath(): string
{
    return 'chat/' . $this->console_id . '.' . $this->game['id'] . '.json';
}
```

**Design decisions:**
- Lightweight JSON transcripts per game
- Livewire event dispatching for near real-time updates
- Guest and authenticated user support
- Included in site backup/restore zip payloads

### 6. Admin Panel with IGDB Integration

Admin “API Fill” uses **IGDB** (replacing the former OpenAI metadata fill):

```php
// app/Livewire/Admin/GameManager.php
/**
 * Fetch game metadata from the IGDB API (replaces the old AI Fill).
 */
```

**Admin surfaces:**
| Route | Component | Purpose |
|-------|-----------|---------|
| `/admin/games` | `GameManager` | CRUD, ROM validation, IGDB fill, walkthrough videos |
| `/admin/fonts` | `FontManager` | Upload/activate/delete app fonts |
| `/admin/backup` | `BackupManager` | Create, upload, preview, restore, delete backups |

Bulk catalog seeding: `php artisan vintage:import` (`ImportVintageConsoles` + `GameImporter` / `IgdbClient`).

Role-based access via Spatie Permission (`admin` middleware).

### 7. Backup / Restore

`BackupService` produces and consumes versioned `.zip` archives including:
- Core catalog tables (consoles, games, genres, screenshots, fonts, settings)
- Optional emulator user data + `savestates` disk files
- Chat JSON files and migration docs

### 8. Authentication System

```php
Route::get('/login/google', [LoginController::class, 'redirectToGoogle']);
Route::get('/login/google/redirect', [LoginController::class, 'handleGoogleCallback']);
```

**Features:**
- Google OAuth via Laravel Socialite
- Email/password via Fortify
- Two-factor authentication
- API token management via Sanctum
- Session browser management
- Email verification
- Per-user `cursor_style` (`default` | `alternate`)

### 9. UI/UX Implementation

```javascript
// tailwind.config.js — retro pixel typography + custom dark palette
fontFamily: { sans: ['VT323', ...defaultTheme.fontFamily.sans] },
colors: { 'cod-gray': { /* … */, 950: '#121315' } }
```

**UI highlights:**
- ~80 Blade view components
- Theme switcher (light/dark)
- Swiper carousels for screenshots / videos
- Pixel-art icons (`pixelarticons`) and custom cursor pointers
- App font system (bundled VT323 / HackerNoon V2; admin-managed extras)
- Play session bar dock for emulator controls and save slots
- Skeleton loaders, fixed modals, accordion panels

---

## Data Model

### MySQL Catalog (runtime)

**consoles** — non-incrementing PK; `short_name` unique; JSON: `console_bgs`, `specs`, `community_links`, `options`; `igdb_platform_id`

**games** — `console_id`, unique `(console_id, slug)`, optional unique `igdb_id`, media fields (`rom`, `poster`, `cover_image_id`, …), flags (`multiplayer_support`, `save_state_support`, `is_free`, `needs_igdb_sync`), JSON `igdb_response` + `walkthrough_videos`

**genres** / **game_genre** — slug-style genre names with optional `igdb_id`

**screenshots** — `igdb_image_id`, URLs, `position`

### Emulator & App Tables

**emulator_save_states** — `user_id` + `console` + `game_slug` + `slot`; disk path, size, checksum; backup columns for previous state

**emulator_control_settings** — unique `user_id` + `console` + `game_id` + `emulator` + `profile`; JSON `settings`

**app_fonts** / **app_settings** — font registry + key/value (e.g. active font)

**youtube_video_progress** — `user_id`, `game_id`, `youtube_id`, `position_seconds`

**users** — Jetstream/Fortify/Sanctum + Spatie roles + `cursor_style`

### Import-Only JSON

`storage/data/vintage-consoles.json` seeds consoles/games via `vintage:import`. It is **not** read at request time for the catalog UI.

### Eloquent Relationships (summary)

```
Console 1──* Game *──* Genre
Game 1──* Screenshot
User ── EmulatorSaveState / EmulatorControlSetting / YoutubeVideoProgress (queried by user_id)
```

---

## Testing Strategy

### Test Structure

```
tests/
├── Feature/
│   ├── Admin/
│   │   ├── GameManagerTest.php
│   │   ├── FontManagerTest.php
│   │   ├── BackupManagerTest.php
│   │   └── BackupServiceTest.php
│   ├── Livewire/
│   │   ├── ChatTest.php, DashboardTest.php, PlayTest.php
│   │   ├── DosPlayerTest.php, JsPlayerTest.php
│   │   ├── MySavesUploadTest.php, NavigationCursorStyleTest.php
│   │   ├── OrderBy*.php, SelectedConsoleTest.php
│   ├── EmulatorSaveStateTest.php
│   ├── YoutubeVideoProgressTest.php
│   ├── GameRepositoryTest.php
│   └── … (Jetstream auth/profile/token tests)
├── Unit/
│   ├── GameModelTest.php, ScreenshotModelTest.php
│   ├── GameImporterTest.php, IgdbImageTest.php
│   ├── GameIgdbPresenterTest.php, YouTubeUrlTest.php
│   ├── ToolServiceTest.php
│   └── Support/BrowserLabelTest.php
└── Pest.php
```

### Test Configuration

```php
// phpunit.xml → sqlite :memory:
// Feature tests typically:
uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('data');
    Storage::fake('savestates');
    // fonts / local faked where backup & font features are exercised
});
```

**Testing principles:**
- In-memory SQLite (no real MySQL touched)
- Fake storage disks (`data`, `savestates`, `fonts`, `local`)
- Livewire component tests + save-state / YouTube / repository coverage
- No tests hit production files or live IGDB/OpenAI services

---

## Deployment & Operations

### Build Process

```json
// package.json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build"
  }
}
```

### Environment Configuration

Key configs:
- `config/services.php` — OAuth (Google), IGDB credentials
- `config/filesystems.php` — `data`, `savestates`, `fonts`, `local` disks
- `config/openai.php` — leftover OpenAI config (unused by app code)
- `config/livewire.php` — Livewire asset URL configuration
- `.env` — `APP_URL`, DB, IGDB client id/secret

### Catalog bootstrap

```bash
php artisan migrate
php artisan vintage:import   # JSON + IGDB → MySQL
```

### Performance Considerations

1. **CDN assets**: EmulatorJS 4.2.3 and JS-DOS 8.3.20 from CDN
2. **Browser caching**: 1-year cache headers on ROM files
3. **Eager loading**: GameRepository loads genres/screenshots with console games
4. **COI isolation**: Player iframes get SharedArrayBuffer without weakening the main app
5. **IGDB images**: Cover/screenshot URLs built from image IDs (CDN presets) rather than storing large binaries

---

## Skills & Competencies Demonstrated

### Backend Development
- PHP 8.1+ with typed properties and modern Laravel patterns
- Eloquent relational modeling for a content-heavy catalog
- Livewire full-stack reactive components
- Service-layer architecture (`GameRepository`, `BackupService`, IGDB pipeline)
- OAuth 2.0, Fortify, Sanctum, Spatie roles
- File + DB hybrid persistence (chat JSON, save-state binaries)

### Frontend Development
- TailwindCSS utility-first styling with dark mode
- Component-based Blade architecture
- Emulator JS interop (postMessage, gamepad adapters, save-state manager)
- Responsive play UI / session dock
- Pixel typography, custom cursors, Swiper media carousels

### Emulation & Browser Constraints
- Cross-origin isolation (COOP/COEP) for SharedArrayBuffer
- CORP headers on ROM responses
- Iframe sandboxing of WASM cores
- Cloud save-state sync across slots with backup restore

### Testing & Quality
- Pest PHP feature and unit coverage (~40 test files)
- In-memory DB + fake storage isolation
- Livewire, repository, and importer tests

### Integrations & Ops
- IGDB metadata import and admin fill
- Site-wide backup/restore zip format
- Vite build pipeline
- Role-gated admin tooling (games, fonts, backups)

### Software Architecture
- MVC + Livewire hybrid
- Clear separation: Models / Services / Livewire / Controllers
- Import-only JSON vs runtime MySQL boundary
- DRY shared concerns (sorting, toasts, presenters)

---

## Repository Statistics

| Metric | Value |
|--------|-------|
| **Main language** | PHP (Laravel) |
| **Frontend** | Blade + TailwindCSS + Livewire |
| **Livewire components** | 20 (+ 3 concerns) |
| **Blade components** | ~80 |
| **Eloquent models** | 10 |
| **Active domain services** | GameRepository, BackupService, AppFontService, Igdb* |
| **Test files** | 40 |
| **Supported consoles** | 5 (NES, SNES, Arcade, Atari 2600, PC/DOS) |
| **Emulators** | EmulatorJS 4.2.3 · JS-DOS 8.3.20 |

---

*This document was generated to provide comprehensive technical context for AI-assisted blog post creation targeting technical recruiters and talent hunters.*
