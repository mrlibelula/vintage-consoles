# Vintage Consoles in the Browser — Technical Feature Report

## Runtime architecture
- Single-page experience built with Laravel + Livewire. The `Play` component resolves a game by slug, loads console metadata from session/cache, and builds a player route per console.
- ROM files live under `storage/data/games/{console}/{filename}` and are streamed by a dedicated download route with MIME detection and long cache headers.
- Game metadata (title, genres, screenshots, save-state flag, multiplayer flag, rating, etc.) is kept in `storage/data/vintage-consoles.json` and hydrated into Livewire view models for UI rendering and emulator wiring.

## Emulator engines
- **EmulatorJS (8/16/32-bit consoles):** `JsPlayer` configures global `EJS_*` variables (core selection, ROM URL, game ID/name, theme color) and pulls the EmulatorJS runtime from jsDelivr (`EmulatorJS@4.0.7`). A custom overlay loader fades out on `EJS_onGameStart`/`EJS_ready` or after a fallback timeout, ensuring the iframe is never stuck behind a spinner.
- **JS-DOS (PC/MS-DOS):** `DosPlayer` feeds `.jsdos` bundles to the JS-DOS v8 runtime, enabling autolock pointer capture, dark theme, and auto-start. A universal loader is hidden once the emulator boots or after a safety timeout.
- Player surfaces are isolated in iframes (`/player/{enc_json_game}/{console}` for EmulatorJS and `/dosplayer/{enc_json_game}/{console}` for JS-DOS) to keep cores sandboxed while the parent Livewire view handles UI and chat.

## ROM delivery pipeline
- Route `game.serve` streams ROMs and bundles directly from disk with detected `Content-Type` and `Cache-Control: public, max-age=31536000` for CDN/browser caching.
- Console-specific subfolders keep assets separated per core, matching the console short name expected by EmulatorJS.
- Game URLs passed to EmulatorJS/JS-DOS are the same signed/served endpoints, so no public storage exposure is needed.

## Player UI and UX
- Game panel shows publisher, release year, rating, save-state support, multiplayer flag, and console branding.
- Screenshot gallery with modal navigation and keyboard arrows; collapsible accordions for description and genres.
- Live chat per game writes JSON transcripts to `storage/data/chat/{consoleId}.{gameId}.json`, dispatched to the UI in near real time with lightweight file-backed storage.
- The DOS player includes a resilient loader that persists across Livewire updates using `sessionStorage` to avoid re-showing once hidden.

## Data model, caching, and admin hooks
- `GameSession` primes session with lightweight console summaries to cut memory footprint, while full console/game payloads are cached for 30 minutes and invalidated when data changes.
- `GameManager` offers CRUD helpers for consoles/games (slugging, default flags, play counters, metadata merges) against the JSON source of truth.
- `Tool` utilities provide encoding/decoding for player payloads, route helpers for play URLs, sorters, and image URL validation tooling.

## Operational notes
- EmulatorJS data path is pinned to CDN assets; switching cores or versions is a config change in `JsPlayer`.
- JS-DOS pulls its WebAssembly bridge (`wdosbox.js`) from the official CDN; bundles remain local.
- Long-lived cache headers on ROMs mean cache-busting requires filename changes or CDN purge when updating assets.


