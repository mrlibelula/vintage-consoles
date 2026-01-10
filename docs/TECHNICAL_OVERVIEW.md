# Vintage Consoles — Technical Project Overview

> A comprehensive technical reference for AI-assisted blog post generation, targeting tech interviewers and talent hunters.

---

## Project Summary

**Vintage Consoles** is a full-stack web application that delivers a browser-based retro gaming experience. Users can play classic games from multiple vintage gaming platforms (NES, SNES, Arcade, Atari 2600, and MS-DOS/PC) directly in their browser without any downloads or installations. The application features real-time chat per game, OAuth authentication, an admin panel with AI-assisted game metadata entry, and a modern, responsive UI with dark mode support.

---

## Core Technology Stack

### Backend Framework
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | ^8.1 | Server-side language |
| **Laravel** | ^10.10 | MVC Framework |
| **Livewire** | ^3.0 | Full-stack reactive components |
| **Laravel Jetstream** | ^4.0 | Authentication scaffolding (Teams, API tokens, 2FA) |
| **Laravel Sanctum** | ^3.2 | SPA & API token authentication |
| **Laravel Socialite** | ^5.21 | OAuth providers (Google login) |

### Frontend Stack
| Technology | Version | Purpose |
|------------|---------|---------|
| **TailwindCSS** | ^3.1.0 | Utility-first CSS framework |
| **Alpine.js** | (via Livewire) | Lightweight JS framework |
| **Vite** | ^4.0.0 | Next-gen frontend build tool |
| **Swiper** | ^11.2.10 | Touch-enabled carousel/slider |

### Emulation Engines
| Engine | Version | Consoles Supported |
|--------|---------|-------------------|
| **EmulatorJS** | 4.0.7 (CDN) | NES, SNES, Arcade, Atari 2600 |
| **JS-DOS** | v8 (CDN) | MS-DOS/PC games |

### AI Integration
| Service | Package | Purpose |
|---------|---------|---------|
| **OpenAI GPT-4o-mini** | openai-php/laravel ^0.11.0 | AI-assisted game metadata fetching |

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
The project follows **Laravel's MVC architecture** enhanced with **Livewire's component-based reactive pattern**. This creates a Single-Page Application (SPA) feel while maintaining server-side rendering benefits (SEO, security, simplicity).

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │  Livewire   │  │ EmulatorJS  │  │       JS-DOS            │ │
│  │ Components  │  │  (iframe)   │  │      (iframe)           │ │
│  └──────┬──────┘  └──────┬──────┘  └───────────┬─────────────┘ │
└─────────┼────────────────┼─────────────────────┼────────────────┘
          │ WebSocket/     │ ROM Fetch           │ .jsdos Bundle
          │ AJAX           │                     │
┌─────────▼────────────────▼─────────────────────▼────────────────┐
│                    Laravel Backend                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │
│  │  Livewire   │  │   Routes    │  │      Services           │ │
│  │  Components │  │  (web.php)  │  │  (Game, GameSession)    │ │
│  └──────┬──────┘  └──────┬──────┘  └───────────┬─────────────┘ │
│         │                │                     │                │
│  ┌──────▼────────────────▼─────────────────────▼─────────────┐ │
│  │              JSON File Storage (vintage-consoles.json)     │ │
│  │              ROM Files (storage/data/games/{console}/)     │ │
│  └────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### Directory Structure

```
app/
├── Actions/                    # Jetstream action classes (6 files)
├── Console/                    # Artisan commands
├── Http/
│   ├── Controllers/            # Traditional controllers (Login, base)
│   ├── Kernel.php              # HTTP middleware registration
│   └── Middleware/             # Custom middleware (Admin, Auth, HTTPS)
├── Livewire/                   # Reactive components
│   ├── Admin/
│   │   └── GameManager.php     # Admin CRUD with AI integration
│   ├── Chat.php                # Real-time game chat
│   ├── Dashboard.php           # Main console/game browser
│   ├── DosPlayer.php           # JS-DOS emulator wrapper
│   ├── JsPlayer.php            # EmulatorJS wrapper
│   ├── Play.php                # Game detail & player page
│   ├── Genres.php              # Genre-based game browser
│   └── Publishers.php          # Publisher-based game browser
├── Models/
│   └── User.php                # Eloquent user model
├── Providers/                  # Service providers (7 files)
├── Service/                    # Business logic layer
│   ├── Game.php                # Game data retrieval
│   ├── GameManager.php         # CRUD operations on JSON
│   ├── GameSession.php         # Session/cache optimization
│   └── Tool.php                # Utility helpers
├── View/
│   └── Components/             # Blade view components (40+ files)
└── helpers.php                 # Global helper functions
```

---

## Key Features & Implementation Details

### 1. Browser-Based Emulation

#### EmulatorJS Integration (8/16/32-bit Consoles)
The `JsPlayer` Livewire component configures and loads EmulatorJS:

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
        'filename' => $game_data['rom']
    ]);
}
```

**Key configuration points:**
- Global `EJS_*` JavaScript variables configure core selection, ROM URL, theme
- EmulatorJS runtime loaded from jsDelivr CDN (v4.0.7)
- Custom overlay loader fades on `EJS_onGameStart`/`EJS_ready`
- Player isolated in iframe for sandboxed core execution

#### JS-DOS Integration (MS-DOS/PC)
The `DosPlayer` component handles `.jsdos` bundles:

```php
// app/Livewire/DosPlayer.php
public function mount(string $enc_json_game, string $console_short_name)
{
    $this->game = json_decode(Tool::decode($this->enc_json_game), true);
}
```

**Key features:**
- Auto-lock pointer capture for mouse-based games
- Dark theme matching app aesthetic
- Auto-start on bundle load
- Resilient loader using `sessionStorage` to prevent re-display

### 2. ROM Delivery Pipeline

```php
// routes/web.php
Route::get('/games/serve/{console}/{filename}', function ($console, $filename) {
    $gamePath = storage_path("data/games/{$console}/{$filename}");
    
    return response()->file($gamePath, [
        'Content-Type' => mime_content_type($gamePath),
        'Cache-Control' => 'public, max-age=31536000', // 1 year cache
    ]);
})->name('game.serve');
```

**Security & Performance:**
- ROMs served from `storage/data/games/{console}/` (not public)
- MIME type auto-detection
- Aggressive browser/CDN caching (1 year)
- No public storage exposure

### 3. Session & Memory Optimization

The `GameSession` service implements a two-tier caching strategy:

```php
// app/Service/GameSession.php
public function __construct(array $consoles = null)
{
    // Only store basic console info in session (no full game data)
    $basicConsoles = [];
    foreach ($data['consoles'] as $console) {
        $basicConsoles[] = [
            'id' => $console['id'],
            'long_name' => $console['long_name'],
            'short_name' => $console['short_name'],
            'game_count' => count($console['games'] ?? [])
            // Note: 'games' deliberately excluded to save memory
        ];
    }
    Session::put('consoles_basic', $basicConsoles);
}

public function getFullConsoleData(string $consoleShortName = null): array
{
    return cache()->remember($cacheKey, now()->addMinutes(30), function () {
        // Load full data only when needed
    });
}
```

**Benefits:**
- Session footprint reduced by ~90%
- Full game data loaded on-demand
- 30-minute cache for frequently accessed consoles
- Cache invalidation on admin updates

### 4. Real-Time Chat System

Per-game chat implemented with file-based JSON storage:

```php
// app/Livewire/Chat.php
public function chatFilePath(): string
{
    return 'chat/' . $this->console_id . '.' . $this->game['id'] . '.json';
}

public function loadMessages()
{
    $this->messages = json_decode(
        Storage::disk('data')->get($this->chatFilePath()), 
        true
    );
    $this->messages = Tool::sortByDate($this->messages, 'timestamp');
}
```

**Design decisions:**
- File-based storage (no database dependency)
- Lightweight JSON transcripts per game
- Livewire event dispatching for near real-time updates
- Guest and authenticated user support

### 5. Admin Panel with AI Integration

The `GameManager` component integrates OpenAI for metadata fetching:

```php
// app/Livewire/Admin/GameManager.php
public function fetchGameDataFromAI()
{
    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
        ],
        'temperature' => 0.3,
    ]);
    
    $gameData = json_decode($response->choices[0]->message->content, true);
    
    // Auto-fill form fields
    $this->publisher = $gameData['publisher'];
    $this->release_year = $gameData['release_year'];
    $this->genres = $gameData['genres'];
}
```

**Admin Features:**
- Full CRUD for games across consoles
- ROM file validation (local files for consoles, URLs for DOS)
- Console-specific file extension validation
- Move games between consoles
- AI-assisted metadata entry (publisher, year, rating, genres)
- Role-based access via Spatie Permission

### 6. Authentication System

Multi-provider authentication setup:

```php
// routes/web.php
Route::get('/login/google', [LoginController::class, 'redirectToGoogle']);
Route::get('/login/google/redirect', [LoginController::class, 'handleGoogleCallback']);
```

**Features:**
- Google OAuth via Laravel Socialite
- Traditional email/password via Fortify
- Two-factor authentication support
- API token management via Sanctum
- Session browser management
- Email verification

### 7. UI/UX Implementation

#### TailwindCSS Configuration

```javascript
// tailwind.config.js
export default {
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['VT323', ...defaultTheme.fontFamily.sans], // Retro pixel font
            },
            colors: {
                'cod-gray': {
                    // Custom gray palette for dark theme
                    DEFAULT: '#6B7280',
                    950: '#121315'
                }
            }
        }
    },
    plugins: [forms, typography],
}
```

**UI Components:**
- 40+ Blade view components
- Skeleton loaders for async content
- Fixed modals with keyboard navigation
- Accordion panels for collapsible content
- Theme switcher (light/dark mode)
- Swiper carousel for screenshots

---

## Data Model

### JSON-Based Storage

Game data stored in `storage/data/vintage-consoles.json`:

```json
{
  "consoles": [
    {
      "id": 1,
      "short_name": "NES",
      "long_name": "Nintendo Entertainment System",
      "description": "Classic 8-bit gaming console from Nintendo",
      "manufacturer": "Nintendo",
      "release_year": "1985",
      "console_logo": "/images/consoles/nes-logo.png",
      "console_icon": "/images/consoles/nes-icon.png",
      "console_bgs": ["/images/bgs/nes-bg.jpg"],
      "emulator": {
        "name": "EmulatorJS",
        "version": "4.0.7"
      },
      "specs": {
        "cpu": "8-bit MOS 6502",
        "memory": "2KB RAM"
      },
      "games": [
        {
          "id": 1,
          "title": "Super Mario Bros.",
          "slug": "super-mario-bros",
          "publisher": "Nintendo",
          "release_year": "1985",
          "rating": "0.89",
          "description": "Classic platformer...",
          "rom": "super-mario-bros.nes",
          "poster": "/images/games/mario-poster.jpg",
          "box": "/images/games/mario-box.jpg",
          "cartridge": "/images/games/mario-cart.jpg",
          "screenshots": ["..."],
          "genres": [
            {"name": "platformer", "description": "Jump and run"}
          ],
          "multiplayer_support": true,
          "save_state_support": true,
          "is_free": true
        }
      ]
    }
  ]
}
```

---

## Testing Strategy

### Test Structure

```
tests/
├── Feature/
│   ├── Admin/
│   │   └── GameManagerTest.php    # Admin CRUD tests
│   ├── Livewire/
│   │   ├── ChatTest.php           # Chat functionality
│   │   ├── DashboardTest.php      # Dashboard component
│   │   ├── DosPlayerTest.php      # DOS emulator
│   │   ├── JsPlayerTest.php       # Console emulator
│   │   ├── PlayTest.php           # Game page
│   │   └── SelectedConsoleTest.php
│   ├── AuthenticationTest.php
│   ├── RegistrationTest.php
│   └── ... (Jetstream feature tests)
├── Unit/
│   ├── GameServiceTest.php
│   ├── GameSessionServiceTest.php
│   └── ToolServiceTest.php
└── Pest.php                       # Pest configuration
```

### Test Configuration

```php
// tests/Feature/Livewire/DashboardTest.php
beforeEach(function () {
    // In-memory SQLite database
    config(['database.default' => 'testing']);
    config(['database.connections.testing' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]]);
    
    // Fake storage disk
    Storage::fake('data');
    Storage::disk('data')->put('vintage-consoles.json', json_encode(getMockData()));
});
```

**Testing principles:**
- In-memory SQLite database (no real DB touched)
- Fake storage disks (no real files modified)
- Comprehensive Livewire component testing
- Service layer unit tests
- Feature tests for full user flows

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

Key configurations:
- `config/openai.php` - OpenAI API key
- `config/services.php` - OAuth credentials (Google)
- `config/filesystems.php` - Storage disk for game data
- `config/livewire.php` - Livewire asset URL configuration

### Performance Considerations

1. **CDN Assets**: EmulatorJS and JS-DOS loaded from CDN
2. **Browser Caching**: 1-year cache headers on ROM files
3. **Session Optimization**: Minimal session footprint
4. **On-Demand Loading**: Full game data cached 30 minutes
5. **Iframe Isolation**: Emulators sandboxed from main app

---

## Skills & Competencies Demonstrated

### Backend Development
- Modern PHP 8.1+ with typed properties and union types
- Laravel framework mastery (routing, middleware, service providers)
- Livewire full-stack reactive components
- Service layer architecture for business logic
- JSON-based data storage with caching strategies
- OAuth 2.0 implementation

### Frontend Development
- TailwindCSS utility-first styling
- Dark mode implementation
- Component-based Blade architecture
- JavaScript interop with PHP (Livewire)
- Responsive design
- Accessibility considerations

### Testing & Quality
- Pest PHP modern testing syntax
- Feature and unit test coverage
- Mock/fake patterns for isolation
- Livewire component testing

### DevOps & Performance
- Vite build pipeline
- CDN integration
- Aggressive caching strategies
- Memory optimization techniques

### AI Integration
- OpenAI API integration
- Prompt engineering for structured data
- Error handling for AI responses

### Software Architecture
- MVC + Component hybrid pattern
- Service layer abstraction
- Separation of concerns
- DRY principles with reusable components

---

## Repository Statistics

- **Main Language**: PHP (Laravel)
- **Frontend**: Blade + TailwindCSS + Livewire
- **Livewire Components**: 17
- **Blade Components**: 40+
- **Service Classes**: 4
- **Test Files**: 20+
- **Supported Consoles**: 5 (NES, SNES, Arcade, Atari 2600, PC/DOS)

---

*This document was generated to provide comprehensive technical context for AI-assisted blog post creation targeting technical recruiters and talent hunters.*
