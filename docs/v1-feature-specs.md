# Vintage Consoles v1 — Product Feature Specs

Agent-ready inventory of **current v1 behavior** (code-verified). Use this as the behavioral checklist when designing or porting the next version.

- **Live:** https://play.libe.dev
- **Schemas / IGDB import / backup zip format:** see [`v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md)
- **v2 phasing:** see [`v2-global-agent-prompt.md`](./v2-global-agent-prompt.md)

Prefer this file over README / `TECHNICAL_OVERVIEW.md` where they conflict (those docs are partly stale).

---

## 1. Product

Browser-based retro gaming platform: browse a catalog, launch ROM/jsdos titles in-browser, persist cloud save states (auth), chat per game, and operate the catalog via an admin panel.

---

## 2. Stack (v1)

| Layer | Tech |
|-------|------|
| Backend | PHP 8.1+, Laravel 10, Livewire 3, Jetstream (Livewire), Fortify, Sanctum, Socialite |
| AuthZ | Spatie Permission (`admin`, `user`) |
| Frontend | Blade, Tailwind 3, Alpine (via Livewire), Vite, Swiper |
| Metadata | IGDB (Laravel package + custom client). OpenAI is in Composer/env but **unused in app code** |
| Tests | Pest / PHPUnit — in-memory SQLite + fake storage disks only |

---

## 3. Platforms & emulators

| Console key | Emulator | Version | Player route | ROM / package |
|-------------|----------|---------|--------------|---------------|
| `nes` | EmulatorJS | **4.2.3** (`cdn.emulatorjs.org`) | `/player/{enc}/{console}` | `.nes` file upload |
| `snes` | EmulatorJS | 4.2.3 | same | `.zip`, `.7z`, `.smc` |
| `arcade` | EmulatorJS | 4.2.3 | same | `.zip` |
| `atari2600` | EmulatorJS | 4.2.3 | same | `.bin`, `.a26` |
| `pc` | JS-DOS | **8.3.20** (jsDelivr) + wdosbox | `/dosplayer/{enc}/{console}` | HTTP(S) URL ending in `.jsdos` (not file upload) |

- Dashboard tabs are hardcoded: `nes`, `snes`, `arcade`, `atari2600`, `pc`.
- EmulatorJS `EJS_core` = console `short_name`.
- Player routes use **cross-origin isolation** (COOP/COEP) for SharedArrayBuffer / WASM.
- Parent Livewire **Play** page embeds the player iframe; chat/UI stay outside the core.
- ROMs served privately: `/games/serve/{console}/{filename}` (long cache + CORP). Files live on disk `data` → `storage/data/games/{console}/`.

**IGDB platform IDs (import):** NES=18, SNES=19, arcade=52, atari2600=59, PC=13.

---

## 4. Player / user features

- Browse by **console**, **genre** (`/games/genres/...`), **publisher** (`/games/publishers/...`).
- Dashboard carousels with order modes (group / lista / squares); global nav search.
- **Play page** (`/emulator/{console}/{slug}`): game info, screenshots modal, accordion description, embedded player, per-game **live chat**, save-slot counter, multiplayer badge.
- **Theme:** dark / light / system.
- **Pixel cursors** (`cursor_style` on user): default / alternate.
- **Site-wide retro font** (admin-activated `AppFont`).
- **Gamepad** support with mappable controls; per-user **control settings** API (JSON profile per user/console/game/emulator `emulatorjs`|`jsdos`).
- Keyboard: **F** fullscreen (custom, survives EmulatorJS reload), **P** play/pause (EmulatorJS).
- EmulatorJS built-in virtual-gamepad hamburger is CSS-hidden.
- Flags on games: `multiplayer_support` (UI badge only — **no netplay**), `save_state_support`, `is_free`.

**Not implemented in app:** cheats, rewind, shaders/CRT filters, online multiplayer. (EmulatorJS may expose its own UI; the app does not configure those.)

---

## 5. Save states (critical)

| Rule | Value |
|------|--------|
| Who | Authenticated users only |
| Consoles | EmulatorJS only — **`pc` excluded** |
| Slots | **5 per game** (`UpsertEmulatorSaveState::MAX_SLOTS`) |
| Upload max | **102400 KB** (~100 MB) |
| Label | max 80 chars |
| Integrity | SHA-256 checksum in DB |
| Disk | `savestates` → `storage/app/savestates/{userId}/{console}/{slug}/{slug}-slot-N.state` |
| Backup | Overwrite rotates primary → `.backup` (max 2 files/slot); restore swaps |
| Game gate | `save_state_support` (default true) |

**APIs (web `auth`):** list/create/update/delete, download, restore-backup under `/player-data/save-states`.

**Client:** `SaveStateManager.js` — overlay panel, Cache API (`vintage-save-states-v1`), integrity checks, `.state` upload.

**Hotkeys:** F2 save · F4 load · Ctrl+Alt+1–5 select slot · Ctrl+Delete/Backspace clear.

**My Saves:** `/my/saves` — list/filter/delete/upload to slot/global upload/sync orphaned disk files.

IndexedDB reset helper for EmulatorJS internal storage: `window.vintageResetEmulatorStorage()`.

---

## 6. Auth & roles

- Email/password (Fortify): registration, reset, profile/password, **2FA**.
- **Google OAuth** (Socialite): `/login/google`, `/login/google/redirect`.
- Roles: `admin`, `user` (Spatie). Admin routes require `AdminMiddleware` (`hasRole('admin')`).
- Permissions (`create|read|update|delete|view`) exist; enforcement is mostly **role `admin`**, not fine-grained checks.
- Jetstream: account deletion on; teams / API tokens UI / profile photos / terms off.
- Save-state and control APIs enforce ownership.

---

## 7. Admin (operator)

Routes (auth + verified + admin): `/admin/games`, `/admin/fonts`, `/admin/backup`.

### Game Manager
- CRUD; **move game between consoles**; search/filter/sort/paginate.
- Fields: title, publisher, year, description, rating (0–1), ROM, poster, cover_image_id, game_preview, cartridge, genres[], screenshots[], flags (`multiplayer_support`, `save_state_support`, `is_free`).
- **IGDB “API Fill”** for metadata/screenshots; `needs_igdb_sync` when missing IGDB payload.
- Console-specific ROM validation (table in §3).

### Font Manager
- Upload ttf/otf/woff/woff2 (≤5 MB); activate/delete site font.

### Backup Manager
- ZIP backup/restore of core DB tables + chat JSON + optional savestates/user emulator data.
- Restore requires password confirm; notifies users (`SiteDataRestored`).
- Zip layout compatibility is specified in the v2 migration prompt — keep compatible.

---

## 8. Data & storage

| Concern | Where |
|---------|--------|
| Catalog + users + save meta + fonts + settings | **MySQL** (`consoles`, `games`, `genres`, `game_genre`, `screenshots`, …) |
| Import seed source | `storage/data/vintage-consoles.json` + Artisan IGDB import — **not** runtime catalog |
| ROMs / chat transcripts | Disk `data` → `storage/data/` (`games/...`, `chat/{consoleId}.{gameId}.json`) |
| Save binaries | Disk `savestates` |
| Backups | `storage/app/backups/*.zip` |
| Fonts | Disk `fonts` → `public/fonts/` |
| Sessions | DB driver |

Legacy JSON `GameManager` service may still exist for tooling; production browse/play use Eloquent/`GameRepository`.

**Chat:** file-backed JSON per game; guests + auth; Livewire polling refresh (no Redis/WebSocket).

---

## 9. Key routes (reference)

| Route | Purpose |
|-------|---------|
| `/{console?}`, `/dashboard/{console?}` | Browse |
| `/emulator/{console}/{slug}` | Play + chat shell |
| `/player/...`, `/dosplayer/...` | Emulator iframes |
| `/my/saves` | Cloud saves UI |
| `/admin/games\|fonts\|backup` | Admin |
| `/games/serve/...` | Private ROM stream |
| `/player-data/save-states*` | Save-state API |
| Control settings controller | Per-user emulator control profiles |

---

## 10. Gaps / do not invent as existing

- No netplay / true multiplayer runtime.
- No app-level cheat system (About copy may mention cheats).
- No rewind / shader pipeline owned by the app.
- PC has **no** cloud save states.
- README/docs claiming EmulatorJS **4.0.7**, OpenAI metadata fill, or JSON-as-runtime catalog are **wrong** vs current code.

---

## 11. How next-version agents should use this

1. Preserve **player-facing behavior** in §§3–5 unless product explicitly drops it.
2. Preserve **admin capabilities** in §7 (CRUD, IGDB fill, backups, fonts policy may diverge — see v2 global prompt).
3. For table shapes, import pipeline, and backup zip bytes, follow the migration prompts — not this file.
4. When adding features (cheats, netplay, PC saves, shaders), treat them as **new** work, not ports.
