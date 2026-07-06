# Vintage Consoles

**A browser-based retro gaming platform — built as a full-stack portfolio product, not a toy demo.**

[![Live Demo](https://img.shields.io/badge/Demo-play.libe.dev-8b5cf6?style=for-the-badge)](https://play.libe.dev)
[![Portfolio](https://img.shields.io/badge/Portfolio-libe.dev-0ea5e9?style=for-the-badge)](https://libe.dev)
[![Blog Post](https://img.shields.io/badge/Blog-build_story-6366f1?style=for-the-badge)](https://libe.dev/blog/crafting-vintage-consoles-a-browser-based-retro-gaming-platform)

> Play NES, SNES, Arcade, Atari 2600, and MS-DOS classics directly in the browser — no installs, no plugins.

---

## At a glance

| | |
|---|---|
| **Live app** | [play.libe.dev](https://play.libe.dev) |
| **Author** | [libe.dev](https://libe.dev) |
| **Deep dive** | [Crafting Vintage Consoles: A Browser-Based Retro Gaming Platform](https://libe.dev/blog/crafting-vintage-consoles-a-browser-based-retro-gaming-platform) |
| **Stack** | Laravel 10 · Livewire 3 · Tailwind CSS · EmulatorJS · JS-DOS |
| **Tests** | 35+ Pest/PHPUnit feature & unit tests |

---

## What this project demonstrates

Vintage Consoles is a **production-minded web application** that combines nostalgic UX with real engineering constraints: emulator sandboxing, ROM delivery, session memory optimization, authenticated save states, and an admin workflow backed by AI-assisted metadata entry.

It was built to show how I think about **shipping product**, not just writing endpoints:

- **End-to-end ownership** — discovery UI, emulator integration, auth, admin tools, chat, and deployment concerns in one cohesive app.
- **Pragmatic architecture** — Laravel + Livewire for a SPA-like experience without a separate frontend framework; JSON file storage for game catalogs where a relational model would add friction; MySQL only where persistence actually matters (users, save states).
- **Browser-native emulation** — two emulator runtimes (EmulatorJS for 8/16-bit consoles, JS-DOS for PC classics) isolated in iframes with cross-origin isolation headers and a controlled ROM serving pipeline.
- **Operator experience** — role-gated admin panel with CRUD, backups, font management, and OpenAI-assisted game metadata import.
- **Quality bar** — automated test suite with in-memory database and fake storage; no tests hit production files or external services.

If you're a recruiter, engineering manager, or founder evaluating full-stack craft, this repo is meant to answer: *Can this person design a product, make sound tradeoffs, and write maintainable code?*

---

## Features

### For players
- Browse games by **console**, **genre**, or **publisher**
- Launch titles instantly in-browser across **NES, SNES, Arcade, Atari 2600, and MS-DOS**
- **Save states** and custom control mappings for authenticated users
- Per-game **live chat** rooms
- **Dark mode**, retro pixel typography, responsive layout, gamepad support
- Google OAuth and traditional account flows (Jetstream / Fortify)

### For operators (admin)
- Game catalog CRUD across all consoles
- **AI-assisted metadata** entry (publisher, year, genres, ratings) via OpenAI
- ROM validation, console-specific file rules, backup management
- Role-based access with Spatie Permission

---

## Architecture highlights

```
Browser                         Laravel backend
┌─────────────────────┐        ┌──────────────────────────────┐
│ Livewire UI         │◄──────►│ Routes · Services · Livewire │
│ EmulatorJS (iframe) │  ROMs  │ JSON catalog + MySQL (users) │
│ JS-DOS (iframe)     │◄──────►│ Secure ROM streaming route   │
└─────────────────────┘        └──────────────────────────────┘
```

**Decisions worth noting:**

| Area | Approach | Why |
|------|----------|-----|
| **Emulation** | EmulatorJS + JS-DOS in sandboxed iframes | Keeps WASM cores isolated; parent Livewire view owns UX |
| **ROM delivery** | Private `storage/` path, streamed via Laravel route | No public disk exposure; MIME detection + long cache headers |
| **Catalog data** | `vintage-consoles.json` + service layer | Fast iteration for a content-heavy catalog without migration churn |
| **Session memory** | Lightweight console summaries in session; full payloads cached 30 min | ~90% session footprint reduction for large game libraries |
| **Chat** | File-backed JSON transcripts per game | Near real-time feel without adding Redis/WebSocket infra |
| **Save states** | Authenticated API + Eloquent models | Proper persistence where user data must survive |

More technical detail lives in [`docs/TECHNICAL_OVERVIEW.md`](docs/TECHNICAL_OVERVIEW.md) and [`docs/emulation-browser.md`](docs/emulation-browser.md).

---

## Tech stack

**Backend:** PHP 8.1+, Laravel 10, Livewire 3, Jetstream, Sanctum, Socialite, Spatie Permission, OpenAI PHP SDK, IGDB Laravel (metadata import)

**Frontend:** Blade, Tailwind CSS 3, Vite, Alpine.js (via Livewire), Swiper

**Emulation:** [EmulatorJS](https://github.com/EmulatorJS/EmulatorJS) 4.x (NES/SNES/Arcade/Atari), [JS-DOS](https://js-dos.com/) v8 (MS-DOS)

**Testing & tooling:** Pest PHP, PHPUnit, Laravel Pint, Mockery, Laravel Debugbar

---

## Testing

```bash
php artisan test
```

Tests use an **in-memory SQLite database** and **fake storage disks** — no production data or real filesystem dependencies are touched during the suite.

Coverage spans Livewire components (dashboard, players, chat, admin), save-state APIs, authentication flows, and core service-layer logic.

---

## Local development

**Requirements:** PHP 8.1+, Composer, Node.js 18+, npm

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run dev
php artisan serve
```

Game catalog JSON and ROM assets are expected under `storage/data/`. Admin features require seeding a user with the `admin` role. See [`ADMIN_README.md`](ADMIN_README.md) for operator documentation.

> **Note:** ROM files are not included in this repository. The app is designed to run against a locally provisioned `storage/data/` dataset.

---

## Project structure (high level)

```
app/
├── Livewire/          # Dashboard, Play, JsPlayer, DosPlayer, Chat, Admin/*
├── Service/           # GameManager, GameSession, Tool — business logic
├── Http/Controllers/  # Save states, OAuth, control settings
└── View/Components/   # 40+ reusable Blade components

resources/views/       # Livewire templates + layout
tests/                 # Pest feature & unit tests
docs/                  # Technical overviews and architecture notes
```

---

## About the author

Built and maintained by **Luis** — full-stack web developer.

- Portfolio: [libe.dev](https://libe.dev)
- Build story: [Crafting Vintage Consoles](https://libe.dev/blog/crafting-vintage-consoles-a-browser-based-retro-gaming-platform)
- Live demo: [play.libe.dev](https://play.libe.dev)

Questions or feedback? Reach out via [libe.dev](https://libe.dev).

---

## License

MIT
