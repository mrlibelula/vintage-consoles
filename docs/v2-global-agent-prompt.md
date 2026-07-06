# Vintage Consoles v2 — Global Agent Prompt

You are implementing **Vintage Consoles v2**: a Laravel 12 + React + Inertia + Tailwind + shadcn + pixelactUI rewrite of the existing **vintage-consoles** (v1) project. v1 remains the reference for behavior, schemas, and data formats.

---

## How to use this prompt

1. **Read this file first** — it defines phases, priorities, and how to start.
2. **Read the full technical spec** — all catalog, IGDB, and (future) backup details live in:
   - **[`docs/v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md)** (in the v1 repo; copy or link it into v2 as `docs/v2-migration-agent-prompt.md`)

Treat the migration spec as the source of truth for table shapes, field mappings, zip layout, and test expectations. This global prompt only **scopes what to build now vs later**.

---

## Project context

| | v1 (reference) | v2 (you are building) |
|---|----------------|------------------------|
| Backend | Laravel + Livewire | Laravel 12 + Inertia API |
| Frontend | Blade / Livewire | React + shadcn |
| Catalog source (prod) | MySQL after import | Same — **not** JSON at runtime |
| Seed / import source | `storage/data/vintage-consoles.json` + IGDB | Same file + same pipeline |
| Backups | Admin zip via `BackupService` | **Phase 2** — must stay compatible with v1 zips |
| Fonts | `app_fonts` + `app_settings` in DB (site-wide) | **v2 already has its own font system** — do not port v1 font tables or admin in Phase 1 |

v2 is an experiment to modernize the stack while keeping **data and import behavior** aligned with v1 so we can migrate catalog content and, later, restore `.zip` backups from production.

---

## Fonts (v1 reference only — not Phase 1)

The migration spec ([`v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md)) describes v1’s `app_fonts` and `app_settings` (e.g. `active_app_font_id`) because those tables appear in **v1 backups** (`db/core.json`). That description is a **reference for backup compatibility in Phase 2**, not a requirement to replicate v1’s font storage in v2.

**For Phase 1 (DB + import):**

- Do **not** add migrations, models, seeders, or import logic for `app_fonts` / `app_settings`.
- Do **not** wire catalog import or IGDB to fonts.
- Keep using **v2’s existing font system** (Tailwind, theme/CSS, pixelactUI, or whatever is already in the repo).

**For later (out of scope for DB + import):**

- **Per-user font preferences** (each user choosing different font families, sizes, or presets) may be added in a **future UX/settings phase** — likely user profile or preferences storage, not the catalog schema.
- When that ships, design it for v2’s stack; do not assume v1’s `app_fonts` table is the target model unless Phase 2 backup restore explicitly requires mirroring those rows for interchangeability.

**For Phase 2 (backup/restore only):**

- If implementing v1-compatible zips, you may need `app_fonts` / `app_settings` **tables so restore from a v1 backup does not fail** — that is backup plumbing, not replacing v2’s runtime font UX.
- Restored font rows from an old zip must not override v2’s default font experience without an explicit product decision.

---

## Emulator tables — schema in Phase 1, behavior later

**Decision:** Create **`emulator_save_states` and `emulator_control_settings` migrations + Eloquent models in Phase 1**, alongside the catalog tables. Tables stay **empty** until Player or backup restore writes data.

| | Phase 1 | Phase 2+ |
|---|---------|----------|
| **Migrations + models** | Yes — final v1-compatible shape (§2.6 in migration spec) | No duplicate migrations |
| **`vintage:import`** | Does **not** touch emulator tables | — |
| **Save-state API / player JS** | No | **Player phase** |
| **`BackupService` / zip restore** | No | **Phase 2** |
| **`savestates` disk** | Register in `config/filesystems.php` only (optional); no read/write logic | Phase 2 restore + Player uploads |

**Why include schema early:**

- Low cost; avoids a second migration pass before backup or player work.
- Phase 2 can restore v1 `db/user_data.json` into existing tables.
- `vintage-consoles.json` still does **not** seed these tables.

**Do not port v1’s chained legacy migrations** (`game_id`, nullable `emulator` column, etc.). v2 gets **one migration per table** with the consolidated schema in §4.6 / §2.6.

**If an agent plan says “Out of scope: emulator tables”** — read that as **no emulator APIs, BackupService, or import coupling**. Schema + models are **in scope** for Phase 1 unless explicitly removed by the user.

---

## Phased delivery

### Phase 1 — NOW: Database + IGDB import (+ emulator schema)

**Goal:** Persist the game catalog in MySQL/SQLite and populate it from `vintage-consoles.json` + IGDB, matching v1. Also create **empty** emulator tables (migrations + models) for later backup/player work.

**Build in Phase 1:**

- Migrations + Eloquent models: **catalog** — `consoles`, `genres`, `games`, `game_genre`, `screenshots`
- Migrations + Eloquent models: **emulator (schema only)** — `emulator_save_states`, `emulator_control_settings` (see [Emulator tables](#emulator-tables--schema-in-phase-1-behavior-later))
- Services: `IgdbImage`, `IgdbClient`, `GameImporter`, `GameRepository`
- Artisan command: `php artisan vintage:import` (`--dry-run`, `--force`, `--console=`)
- Config: `config/igdb.php`, env vars `IGDB_CLIENT_ID`, `IGDB_CLIENT_SECRET`
- Placeholder or minimal read usage for Inertia (e.g. one route that loads a console + games from DB) — **no full UI required in Phase 1**
- Tests: port `GameImporterTest` + command tests with mocked IGDB; **in-memory SQLite only**, faked storage

**Explicitly defer to Phase 2 (do not implement yet):**

- `BackupService`, admin backup/restore UI, zip create/list/preview/restore
- Migrations for `app_fonts`, `app_settings` — **only** when implementing backup restore (see [Fonts](#fonts-v1-reference-only--not-phase-1)); not for Phase 1
- Per-user font configuration UI or persistence (future phase; see [Fonts](#fonts-v1-reference-only--not-phase-1))
- `savestates` / `data/chat` disk restore logic
- `SiteDataRestored` notifications
- Full game player, save-state upload, control settings

When reading [`v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md), **follow §2 (catalog schema) and §3 (IGDB import) and §5.1 (catalog tests)**. **Skim §4 (backup)** for awareness only — do not implement it in Phase 1.

---

### Phase 2 — LATER: Backup / restore (v1 zip compatible)

**Goal:** Admins can create and restore `.zip` backups that are **interchangeable** with v1 (`BACKUP_VERSION = 1`).

**Build in Phase 2 (when requested):**

- Everything in **§4** of [`v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md): `BackupService`, `app_fonts` / `app_settings` only if needed for v1 zip core payload, filesystem restore for `savestates` + `data/chat`, Inertia admin page, password-gated restore, notifications (emulator **tables** already exist from Phase 1)
- Port `BackupServiceTest` and verify restoring a real v1 backup zip

**Not Phase 2 unless explicitly requested:** Full in-browser player, save-slot UI, `UpsertEmulatorSaveState` — that is the **Player phase** (can precede or follow Phase 2).

Do **not** start Phase 2 until Phase 1 is merged and `vintage:import` works end-to-end.

---

## Phase 1 acceptance criteria

Before marking Phase 1 done:

- [ ] `php artisan migrate` succeeds
- [ ] `storage/data/vintage-consoles.json` is present (copied from v1 or documented in README)
- [ ] `php artisan vintage:import --dry-run` runs without DB writes
- [ ] `php artisan vintage:import --force` fills consoles/games/genres/screenshots
- [ ] JSON field `box` maps to DB column `game_preview`
- [ ] IGDB misses get `needs_igdb_sync = true`
- [ ] All new tests pass (`php artisan test` filtered to importer/command)
- [ ] No real IGDB calls in CI/tests (mock `IgdbClient`)
- [ ] No `app_fonts` / `app_settings` migrations or font-related import code (v2 font system unchanged)
- [ ] `emulator_save_states` / `emulator_control_settings` exist after migrate but have **zero rows** before Player/backup
- [ ] `vintage:import` does not read or write emulator tables

---

## Start implementation — Phase 1 checklist

Execute in order. Stop and fix tests before moving to the next step.

### Step 0 — Repo setup

1. Confirm stack: Laravel 12, Inertia, React, Pest or PHPUnit (match repo convention).
2. Copy **[`docs/v2-migration-agent-prompt.md`](./v2-migration-agent-prompt.md)** into the v2 repo under `docs/`.
3. Copy **`storage/data/vintage-consoles.json`** from v1 into v2 `storage/data/` (or document `cp` path in `docs/DATA.md`). Add `storage/data/.gitignore` exception if the file should be committed for dev.

### Step 1 — Migrations & models

1. Create migrations per **§2** of the migration spec: **§2.1–2.5** (catalog) + **§2.6** (emulator tables, consolidated final schema).
2. Create models `Console`, `Game`, `Genre`, `Screenshot`, `EmulatorSaveState`, `EmulatorControlSetting` with relationships and casts from spec.
3. `Console`: `$incrementing = false`; preserve numeric `id` from JSON.
4. Optional: add `savestates` disk to `config/filesystems.php` (root `storage/app/savestates`) — config only.
5. Run `php artisan migrate` locally.

### Step 2 — IGDB package & config

1. `composer require marcreichel/igdb-laravel`
2. Publish/config `config/igdb.php`; document `.env` keys in `.env.example`.
3. Implement `App\Services\Igdb\IgdbImage` (URL builder).

### Step 3 — Import services

1. Implement `IgdbClient` (batch + fallback search; `toArray` + date normalization per spec).
2. Implement `GameImporter` (`import` + `importFromJson`).
3. Implement `GameRepository` (read methods for consoles/games).

### Step 4 — Import command

1. Port `ImportVintageConsoles` → `app/Console/Commands/ImportVintageConsoles.php`.
2. Register command; signature: `vintage:import {--console=} {--force} {--dry-run}`.
3. Hard-code platform map (NES 18, SNES 19, arcade 52, atari2600 59, PC 13).
4. Phase 1 truncate behavior: same as v1 (truncate games-related tables before import; **not** consoles table truncate — upsert consoles).

### Step 5 — Tests

1. Add `tests/Unit/GameImporterTest.php` (or Pest equivalent) — in-memory DB, no network.
2. Add feature test for command with mocked `IgdbClient`.
3. Run full test suite for new files; fix until green.

### Step 6 — Minimal verification (optional but recommended)

1. Add a dev-only route or `php artisan tinker` snippet doc: load `GameRepository::getConsole('NES')` and assert game count > 0 after import.
2. Do **not** build backup UI or **emulator** migrations/API in this step (catalog verification only).

### Step 7 — Handoff note

Leave a short `docs/PHASE-1-DONE.md` (or PR description) listing:

- Env vars required
- Commands run (`migrate`, `vintage:import --force`)
- Known IGDB misses count from last import log
- Reminder: **Phase 2 = backup/restore per `v2-migration-agent-prompt.md` §4** (then emulator tables appear for zip compatibility)
- Reminder: **Player / cloud saves** = separate phase; not part of Phase 1 import

---

## Constraints (all phases)

- **Tests:** Never use production DB or real storage paths; use SQLite `:memory:` and `Storage::fake()`.
- **Scope:** Minimal diffs; match v1 naming and behavior unless v2 architecture forces a thin adapter layer (document why).
- **JSON:** `vintage-consoles.json` is import input only — runtime catalog reads the database.
- **Fonts:** Phase 1 touches catalog tables only; v2’s existing fonts stay as-is. v1 `app_fonts` in the spec = backup reference, not Phase 1 work. Per-user fonts = later, not DB + import.
- **Commits:** Only when the user asks; prefer small logical commits per step above.

---

## Quick reference — what to read in the spec

| Phase | Spec sections |
|-------|----------------|
| Phase 1 | §2 catalog + **§2.6 emulator schema**, §3 IGDB import, §5.1 tests — **ignore `app_fonts` / `app_settings`**; no §4 backup code |
| Phase 2 | §4 Backup/restore, `app_fonts`/`app_settings` if needed for zip, §5.2 backup tests |
| Player (later) | Save-state API, control settings API, `SaveStateManager` JS — uses Phase 1 emulator tables |
| Later | Per-user font preferences — not Phase 1 |

---

## One-line mission

**Phase 1:** Same catalog in the database as v1, fed by the same JSON + IGDB import. **Phase 2:** Same backup zip as v1, restorable in v2 admin.

Begin with **Step 0** in “Start implementation” when the user says to start Phase 1.
