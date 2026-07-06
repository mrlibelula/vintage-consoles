# Vintage Consoles v2 — AI Agent Task Prompt

Use this document as the **full specification** for implementing catalog storage, IGDB import, and **v1-compatible backup/restore** in the v2 codebase.

**v2 stack:** Laravel 12, React, Inertia, Tailwind, shadcn.

**Reference implementation:** `vintage-consoles` (v1) — mirror behavior and schemas unless v2 explicitly diverges (document any divergence).

**Primary goals:**

1. Database schema + Eloquent models for the game catalog (and tables required for backup compatibility).
2. IGDB import from `storage/data/vintage-consoles.json` (same pipeline as v1).
3. `BackupService` that produces and consumes **the same `.zip` format as v1**, so backups created on v1 can be restored on v2 and vice versa.

**Phase mapping (read with [`v2-global-agent-prompt.md`](./v2-global-agent-prompt.md)):**

| Phase | What from *this* document to implement |
|-------|----------------------------------------|
| **Phase 1** | **§2.1–2.6** (catalog + **emulator table migrations/models**) + **§3** (IGDB import) + **§5.1** (importer tests). **Do not** implement §4, save-state APIs, or `app_fonts` migrations. |
| **Phase 2** | **§4** (backup zip) + `app_fonts`/`app_settings` migrations if needed for v1 zip core payload. Emulator tables already exist from Phase 1. |
| **Player** (separate) | Runtime save/control APIs + `savestates` disk usage — not part of `vintage:import`. |

**Emulator tables in Phase 1:** Migrations + models only (empty tables). `vintage-consoles.json` does not populate them. Phase 2 restores rows from zip; Player phase writes rows via API.

---

## 1. Scope

### In scope

| Area | Notes |
|------|--------|
| Catalog tables | `consoles`, `genres`, `games`, `game_genre`, `screenshots` |
| IGDB import | `vintage:import` artisan command, `GameImporter`, `IgdbClient`, `IgdbImage` |
| Backup / restore | `BackupService`, admin UI (Inertia), zip format version **1** |
| Emulator schema (Phase 1) | `emulator_save_states`, `emulator_control_settings` migrations + models — **§2.6**; no import, no API |
| Emulator runtime (later) | `savestates` disk I/O, APIs, backup zip — **Phase 2 / Player** |
| App metadata (for backup) | `app_fonts`, `app_settings` — **Phase 2** zip compatibility; v2 uses its own font system (see global prompt) |
| Chat files (for backup) | `storage/data/chat/*` — restored from zip; v2 may stub chat until implemented |
| Migration docs (for backup) | `storage/app/migration-docs/*` — included in zip |

### Out of scope (unless needed for auth on restore)

- Livewire admin, Jetstream-specific UI, game manager CRUD, chat Livewire component
- Per-slot `.backup` file rotation logic (player feature) — only schema columns must match if present in v1 backups
- Porting `vintage-consoles.json` as runtime catalog source in production (JSON is **import source only**)

---

## 2. Catalog database schema

Create migrations matching v1. Use SQLite in tests; support MySQL in production.

### 2.1 `consoles`

Non-incrementing primary key (IDs come from JSON / backup).

| Column | Type | Notes |
|--------|------|--------|
| `id` | `unsignedBigInteger` PK | Not auto-increment |
| `long_name` | string | |
| `short_name` | string, unique | e.g. `NES`, `SNES`, `arcade` |
| `description` | text, nullable | |
| `emulator_name` | string, nullable | From JSON `emulator.name` |
| `emulator_version` | string, nullable | From JSON `emulator.version` |
| `manufacturer` | string, nullable | |
| `release_year` | string(10), nullable | |
| `console_logo` | string, nullable | |
| `console_icon` | string, nullable | |
| `igdb_platform_id` | integer, nullable, unique | See platform map §3 |
| `console_bgs` | json, nullable | |
| `specs` | json, nullable | |
| `community_links` | json, nullable | |
| `options` | json, nullable | |
| `timestamps` | | |

**Model:** `$incrementing = false`; cast JSON columns to `array`; `hasMany(Game)`.

### 2.2 `genres`

| Column | Type | Notes |
|--------|------|--------|
| `id` | auto-increment PK | |
| `name` | string, unique | Stored as **slug** (`Str::slug` of display name) |
| `description` | text, nullable | |
| `igdb_id` | unsignedBigInteger, nullable, unique | |
| `timestamps` | | |

### 2.3 `games`

| Column | Type | Notes |
|--------|------|--------|
| `id` | auto-increment PK | |
| `console_id` | FK → `consoles`, cascade delete | |
| `igdb_id` | unsignedBigInteger, nullable, **unique globally** | |
| `title` | string | |
| `slug` | string | Unique with `console_id` |
| `publisher` | string, nullable | |
| `release_year` | string(10), nullable | |
| `description` | text, nullable | |
| `rating` | decimal(5,4), nullable | **0.0–1.0** in DB |
| `multiplayer_support` | boolean, default false | |
| `save_state_support` | boolean, default true | |
| `is_free` | boolean, default true | |
| `rom` | text, nullable | |
| `poster` | text, nullable | IGDB cover URL |
| `cover_image_id` | string, nullable | IGDB image id |
| `game_preview` | text, nullable | From JSON field `box` |
| `cartridge` | text, nullable | |
| `needs_igdb_sync` | boolean, default false | |
| `igdb_response` | json, nullable | Full IGDB payload |
| `timestamps` | | |

Index: `(console_id, slug)` unique; index `console_id`.

Use **TEXT** for `rom`, `poster`, `game_preview`, `cartridge` (URLs/paths can exceed 255 chars).

### 2.4 `game_genre`

Composite PK `(game_id, genre_id)`; both FKs cascade delete. **No `id` column.**

### 2.5 `screenshots`

| Column | Type | Notes |
|--------|------|--------|
| `id` | auto-increment PK | |
| `game_id` | FK → `games`, cascade | |
| `igdb_image_id` | string, nullable | |
| `thumb_url` | string | |
| `full_url` | string | |
| `position` | unsignedSmallInteger, default 0 | |
| `timestamps` | | |

Index `(game_id, position)`.

Optional accessors: rebuild thumb/full URLs from `igdb_image_id` via `IgdbImage` (v1 behavior).

### 2.6 `emulator_save_states` and `emulator_control_settings` (Phase 1 — schema only)

Create in Phase 1 so v2’s database matches what v1 backups and the future player expect. **One migration per table** with the **final** column set (do not replay v1’s legacy `game_id` / `emulator` column migrations).

**`emulator_save_states`**

| Column | Type |
|--------|------|
| `id` | bigint PK |
| `user_id` | FK → `users`, cascade delete |
| `console` | string(64) |
| `game_slug` | string(128) |
| `slot` | unsignedTinyInteger |
| `label` | string, nullable |
| `disk_path` | string |
| `backup_disk_path` | string, nullable |
| `size_bytes` | unsignedBigInteger, default 0 |
| `backup_size_bytes` | unsignedBigInteger, nullable |
| `checksum` | string(64) |
| `backup_checksum` | string(64), nullable |
| `backup_updated_at` | timestamp, nullable |
| `timestamps` | |

Unique: `(user_id, console, game_slug, slot)`. Index: `(user_id, console, game_slug)`.

**`emulator_control_settings`**

| Column | Type |
|--------|------|
| `id` | bigint PK |
| `user_id` | FK → `users`, cascade delete |
| `console` | string(64) |
| `game_id` | string(128) — game slug (v1 column name) |
| `emulator` | string(32) |
| `profile` | string, default `default` |
| `settings` | json |
| `checksum` | string(64) |
| `timestamps` | |

Unique: `(user_id, console, game_id, emulator, profile)`. Index: `(user_id, console, game_id)`.

**Models:** `EmulatorSaveState`, `EmulatorControlSetting` — `belongsTo(User)`; fillable/casts per v1 `app/Models/`.

**Phase 1 must not:** seed these tables, call them from `vintage:import`, or implement HTTP save/load endpoints.

**Optional Phase 1:** Register `savestates` disk in `config/filesystems.php` (`storage/app/savestates`).

---

## 3. IGDB import

### 3.1 Dependencies & config

```bash
composer require marcreichel/igdb-laravel
```

```env
IGDB_CLIENT_ID=
IGDB_CLIENT_SECRET=
IGDB_CACHE_LIFETIME=3600
```

Credentials: [Twitch Developer Console](https://dev.twitch.tv/console/apps).

### 3.2 Platform map (import command)

| `short_name` | IGDB platform ID |
|--------------|------------------|
| `NES` | 18 |
| `SNES` | 19 |
| `arcade` | 52 |
| `atari2600` | 59 |
| `PC` | 13 |

Persist on `consoles.igdb_platform_id` when seeding from JSON.

### 3.3 Source file

**Path:** `storage/data/vintage-consoles.json`

**Root shape:**

```json
{
  "consoles": [
    {
      "id": 1,
      "short_name": "NES",
      "long_name": "Nintendo Entertainment System",
      "description": "...",
      "manufacturer": "Nintendo",
      "release_year": "1985",
      "console_logo": "/images/consoles/nes-logo.png",
      "console_icon": "/images/consoles/nes-icon.png",
      "console_bgs": ["/images/bgs/nes-bg.jpg"],
      "emulator": { "name": "EmulatorJS", "version": "4.0.7" },
      "specs": {},
      "community_links": [],
      "options": {},
      "games": [
        {
          "title": "Super Mario Bros.",
          "slug": "super-mario-bros",
          "publisher": "Nintendo",
          "release_year": "1985",
          "rating": 0.89,
          "description": "...",
          "rom": "super-mario-bros.nes",
          "box": "/images/games/mario-box.jpg",
          "cartridge": "/images/games/mario-cart.jpg",
          "genres": [{ "name": "platformer", "description": "..." }],
          "multiplayer_support": true,
          "save_state_support": true,
          "is_free": true
        }
      ]
    }
  ]
}
```

Copy the real file from v1 ops (`storage/data/vintage-consoles.json`). The importer must **never delete** this file.

### 3.4 Field mapping (JSON ↔ DB ↔ IGDB)

| JSON | DB | IGDB match |
|------|-----|------------|
| `box` | `game_preview` | Local only |
| `rom`, `cartridge`, flags | same | Local only |
| `title`, `slug` | same | IGDB `name` for title when matched |
| `rating` (0–1) | `rating` | IGDB: `round(total_rating/100, 4)` clamped 0–1 |
| — | `poster`, `cover_image_id` | From `cover.image_id` |
| — | `igdb_response` | Full payload when matched |
| — | `needs_igdb_sync` | `false` if IGDB hit; `true` if JSON-only |

### 3.5 Services (port from v1)

**`App\Services\Igdb\IgdbImage`**

- Base: `https://images.igdb.com/igdb/image/upload/{preset}/{imageId}.{ext}`
- Presets: `t_cover_big`, `t_screenshot_big` (thumb), `t_original` (full screenshot)

**`App\Services\Igdb\IgdbClient`**

- `fetchGamesBatchForPlatform(string[] $titles, int $platformId): array` — keyed by `strtolower(name)`
- `fetchGameForConsole(string $title, int $platformId): ?array` — `search($title)->whereIn('platforms', [$platformId])->limit(1)`
- Eager-load: `cover`, `genres`, `screenshots`, `involved_companies`, `involved_companies.company`, … (see v1 `IgdbClient::RELATIONS`)
- Convert models with `$game->toArray()`, **not** `json_encode($game)` (circular reference bug)
- Normalize nested `Carbon` to Unix timestamps

**`App\Services\Igdb\GameImporter`**

- `import(array $igdbPayload, Console $console, array $localData = []): Game`
- `importFromJson(array $jsonGame, Console $console): Game` — no poster/screenshots; `needs_igdb_sync = true`
- Publisher: first `involved_companies` with `publisher: true`, else first `developer: true`
- Genres: `Genre::firstOrCreate(['name' => Str::slug($name)], …)`; `sync()` pivot
- Screenshots: delete existing; insert with thumb/full URLs + `position`
- Nested transactions: if `DB::transactionLevel() > 0`, run callback without starting inner transaction

**`App\Services\GameRepository`** (read API for Inertia)

- `getConsoles()`, `getConsole($shortName)` with `games.genres`, `games.screenshots` ordered by `position`
- `getGameBySlug($shortName, $slug)`

### 3.6 Artisan command `vintage:import`

```
vintage:import {--console=} {--force} {--dry-run}
```

**Phase 1 (no DB writes):**

1. Load JSON from `storage_path('data/vintage-consoles.json')`.
2. Per console: batch IGDB fetch by exact title + platform; per-title fallback search; build plan entries: `batch` | `fallback` | `miss`.
3. Print preview tables; write markdown report to `storage/app/migration-docs/migration-{Y-m-d_His}.md`.
4. If `--dry-run`, stop.

**Phase 2 (after confirm unless `--force`):**

1. **Outside** `DB::transaction`: truncate `game_genre`, `screenshots`, `games`, `genres` (MySQL: `SET FOREIGN_KEY_CHECKS=0` first — TRUNCATE is DDL and implicit-commits).
2. Inside transaction: upsert consoles (preserve JSON `id`); import plan via `GameImporter`.
3. Write `storage/logs/igdb-migration-unmatched.json` for misses.
4. Append Phase 2 result to migration markdown.

**v1 reference files:**

- `app/Console/Commands/ImportVintageConsoles.php`
- `app/Services/Igdb/{GameImporter,IgdbClient,IgdbImage}.php`
- `tests/Unit/GameImporterTest.php`

---

## 4. Backup / restore (v1 zip compatibility)

**Critical:** v2 must read and write backups that v1’s `BackupService` can read. Do not change `BACKUP_VERSION` or zip layout without a version bump strategy.

### 4.1 `BackupService` constants

```php
private const BACKUP_VERSION = 1;
```

### 4.2 Tables included in zip

**Core tables** (`db/core.json`) — export/restore in this **insert order** (truncate in **reverse**):

```php
private const CORE_TABLES = [
    'consoles',
    'genres',
    'games',
    'game_genre',
    'screenshots',
    'app_fonts',
    'app_settings',
];
```

**Emulator tables** (`db/user_data.json`) — only when backup includes savestates:

```php
private const EMULATOR_TABLES = [
    'emulator_save_states',
    'emulator_control_settings',
];
```

**Table ordering for export:**

```php
private const TABLE_ORDER_COLUMN = [
    'game_genre'   => null,   // no ORDER BY — pivot has no id
    'app_settings' => 'key',  // PK is key, not id
];
// All other tables: orderBy('id')
```

### 4.3 Zip archive layout

| Zip entry | When | Content |
|-----------|------|---------|
| `manifest.json` | Always | See §4.4 |
| `db/core.json` | Always | `{ version, exported_at, tables: { tableName: [ rows ] } }` |
| `db/user_data.json` | `includes_savestates === true` | Same shape for emulator tables |
| `migration-docs/*` | If any on disk | Files from `Storage::disk('local')` path `migration-docs/` |
| `chat/*` | If any on disk | Files from `Storage::disk('data')` path `chat/` |
| `savestates/{relativePath}` | `includes_savestates === true` | All files on `savestates` disk |

**Stored location:** `storage/app/backups/{filename}` on `local` disk.

**Filename pattern:**

- With savestates: `backup_{Y-m-d_H-i-s}.zip`
- Without: `backup_{Y-m-d_H-i-s}_no-saves.zip`

### 4.4 `manifest.json` schema

```json
{
  "version": 1,
  "created_at": "2026-06-02T12:00:00+00:00",
  "includes_savestates": true,
  "core_tables": ["consoles", "genres", "games", "game_genre", "screenshots", "app_fonts", "app_settings"],
  "emulator_tables": ["emulator_save_states", "emulator_control_settings"]
}
```

When `includes_savestates` is false, `emulator_tables` is `[]` and `db/user_data.json` is **omitted**.

### 4.5 Restore semantics

1. Open zip from `backups/{filename}` via temp file + `ZipArchive`.
2. **DB transaction:**
   - Disable FK checks on MySQL.
   - Truncate all `CORE_TABLES` in reverse order; insert rows from `db/core.json` in chunks of 500.
   - If `manifest.includes_savestates`:
     - Truncate `EMULATOR_TABLES` in reverse; insert from `db/user_data.json`.
   - If **no** savestates in backup: **do not truncate or modify** emulator tables.
   - Re-enable FK checks.
3. **Files (outside DB transaction):**
   - Replace all `migration-docs/*` on local disk with zip entries prefixed `migration-docs/`.
   - Replace all `data/chat/*` with zip entries prefixed `chat/`.
   - If savestates included: wipe `savestates` disk, restore files under `savestates/` prefix.

### 4.6 Emulator table schemas (must match v1 rows in backups)

**`emulator_save_states`**

| Column | Type |
|--------|------|
| `id` | bigint PK |
| `user_id` | FK users, cascade |
| `console` | string(64) |
| `game_slug` | string(128) |
| `slot` | unsignedTinyInteger |
| `label` | string, nullable |
| `disk_path` | string |
| `backup_disk_path` | string, nullable |
| `size_bytes` | unsignedBigInteger, default 0 |
| `backup_size_bytes` | unsignedBigInteger, nullable |
| `checksum` | string(64) |
| `backup_checksum` | string(64), nullable |
| `backup_updated_at` | timestamp, nullable |
| `timestamps` | |

Unique: `(user_id, console, game_slug, slot)`.

**`emulator_control_settings`**

| Column | Type |
|--------|------|
| `id` | bigint PK |
| `user_id` | FK users, cascade |
| `console` | string(64) |
| `game_id` | string(128) — slug or legacy id string |
| `emulator` | string(32) |
| `profile` | string, default `default` |
| `settings` | json |
| `checksum` | string(64) |
| `timestamps` | |

Unique: `(user_id, console, game_id, emulator, profile)`.

### 4.7 `app_fonts` / `app_settings` (core backup)

**`app_fonts`:** `id`, `label`, `family_name`, `relative_path` (unique), `format`, `is_bundled`, timestamps.

**`app_settings`:** `key` PK, `value` text, timestamps.

Seed defaults in migration if v2 has no font UI yet (v1 seeds VT323 + HackerNoonV2 + `active_app_font_id`).

### 4.8 Filesystem disks (config)

```php
'data' => [
    'driver' => 'local',
    'root' => storage_path('data'),
],
'savestates' => [
    'driver' => 'local',
    'root' => storage_path('app/savestates'),
],
```

Backups themselves: `local` disk, `storage/app/backups/`.

### 4.9 Public API

Implement on `App\Services\BackupService`:

| Method | Behavior |
|--------|----------|
| `createBackup(bool $includeSavestates): string` | Returns filename |
| `listBackups(): array` | Metadata: filename, size, size_human, modified_at, includes_savestates, created_at |
| `previewBackup(string $filename): array` | manifest + per-table row diff + file diff (migration-docs, chat, savestates) |
| `restoreBackup(string $filename): void` | Full restore per §4.5 |
| `deleteBackup(string $filename): void` | Remove zip |

Port tests from v1 `tests/Feature/Admin/BackupServiceTest.php` (fake `local`, `data`, `savestates` disks; in-memory DB).

### 4.10 Admin UI (Inertia + shadcn)

Replace v1 Livewire `BackupManager` with an Inertia admin page:

- List backups (`listBackups`)
- Create backup toggle: “Include save games” → `createBackup($includeSavestates)`
- Preview modal: show `previewBackup` db/file diff
- Restore flow: **require current user password** (`Hash::check`) before `restoreBackup`
- Delete backup with confirmation
- After successful restore: notify **all users** via database notification (see §4.11)

Routes: admin-only middleware (same role/policy pattern as v2 auth).

### 4.11 `SiteDataRestored` notification

Database notification payload:

```php
[
    'type' => 'site_data_restored',
    'message' => '...', // mentions date from filename; save games changed or not
    'backup_filename' => $filename,
    'includes_savestates' => bool,
    'admin_name' => string,
    'restored_at' => ISO8601,
]
```

Message when **no** savestates: “Your save games were not changed.”

Message when savestates included: “Your save games may have been replaced.”

### 4.12 Cross-version compatibility checklist

- [ ] v2 can `restoreBackup()` a zip created on v1 production.
- [ ] v1 can `restoreBackup()` a zip created on v2 (smoke-test if possible).
- [ ] `_no-saves` backups do not touch `emulator_*` tables or `savestates` disk.
- [ ] `game_genre` export does not `orderBy('id')` (pivot has no id).
- [ ] `app_settings` export uses `orderBy('key')`.
- [ ] Raw row arrays round-trip via `DB::table()->insert()` (JSON/datetime columns serialize as in v1).
- [ ] `BACKUP_VERSION` remains `1` until a deliberate breaking change with migration path.

**v1 reference:** `app/Services/BackupService.php`, `tests/Feature/Admin/BackupServiceTest.php`, `app/Livewire/Admin/BackupManager.php`.

---

## 5. Testing requirements

All tests must use:

- SQLite `:memory:` (or configured `testing` connection)
- `RefreshDatabase`
- `Storage::fake()` for `local`, `data`, `savestates` — **no real files or production DB**

### 5.1 Catalog / IGDB

Port `tests/Unit/GameImporterTest.php`:

- Rating normalization, genre/screenshot sync, `importFromJson` + `needs_igdb_sync`
- Mock `IgdbClient` in command tests; `--dry-run` writes zero game rows

### 5.2 Backup

Port `tests/Feature/Admin/BackupServiceTest.php`:

- Zip contains `manifest.json`, `db/core.json`
- With/without savestates filenames and contents
- Restore catalog-only does not delete emulator rows
- Restore with savestates replaces emulator rows + disk files
- `game_genre` export does not throw
- Notifications (fake `Notification` facade)

---

## 6. v2 / Inertia integration notes

- **Runtime catalog:** read from DB via `GameRepository`, not JSON.
- **Inertia props:** expose consoles/games with genres, screenshots, `coverUrl()`; omit `igdb_response` from public props unless admin.
- **Import:** operational command run after deploy or when JSON changes; not on every request.
- **Optional dev command:** `vintage:import-json` (JSON-only, no IGDB) — not in v1; add only if IGDB blocked in dev.

---

## 7. Acceptance criteria

### Catalog

- [ ] Migrations run on MySQL and SQLite.
- [ ] `php artisan vintage:import --dry-run` succeeds with sample JSON + mocked IGDB.
- [ ] `php artisan vintage:import --force` populates catalog; JSON `box` → `game_preview`.
- [ ] IGDB misses have `needs_igdb_sync = true`.

### Backup

- [ ] `createBackup(true|false)` produces valid zip per §4.3–4.4.
- [ ] `previewBackup` returns manifest, db diff, file diff.
- [ ] `restoreBackup` matches §4.5 semantics.
- [ ] Admin restore requires password; failed password does not mutate DB.
- [ ] Restore notifies all users.

### Compatibility

- [ ] Restore a real v1 production backup zip into v2 staging without schema errors.
- [ ] All tests green without network or real IGDB.

---

## 8. Suggested implementation order

1. Migrations + models (catalog + emulator + app_fonts/settings).
2. `IgdbImage`, `IgdbClient`, `GameImporter`, `GameRepository`.
3. `ImportVintageConsoles` command + unit tests.
4. `BackupService` + feature tests (verify zip structure byte-for-byte against v1 sample if available).
5. Inertia admin backup page + policies + `SiteDataRestored`.
6. Wire catalog into first Inertia pages.

---

## 9. Reference file index (v1 repo)

| Topic | Path |
|-------|------|
| Consoles migration | `database/migrations/2026_05_09_111505_create_consoles_table.php` |
| Games migration | `database/migrations/2026_05_09_111507_create_games_table.php` |
| Genres / pivot / screenshots | `2026_05_09_111506`, `111508`, `111509` |
| App fonts/settings | `database/migrations/2026_05_12_060100_create_app_fonts_and_settings_tables.php` |
| Emulator tables | `2026_05_05_000001`, `2026_05_05_000002`, `2026_05_07_235900` |
| Import command | `app/Console/Commands/ImportVintageConsoles.php` |
| IGDB services | `app/Services/Igdb/` |
| Backup service | `app/Services/BackupService.php` |
| Backup tests | `tests/Feature/Admin/BackupServiceTest.php` |
| Game importer tests | `tests/Unit/GameImporterTest.php` |
| JSON source | `storage/data/vintage-consoles.json` |

---

*Generated from vintage-consoles v1 for v2 agent handoff. Update `BACKUP_VERSION` and this document together if the zip format ever changes.*
