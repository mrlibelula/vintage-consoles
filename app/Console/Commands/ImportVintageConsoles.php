<?php

namespace App\Console\Commands;

use App\Models\Console as ConsoleModel;
use App\Services\Igdb\GameImporter;
use App\Services\Igdb\IgdbClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportVintageConsoles extends Command
{
    protected $signature = 'vintage:import
                            {--console= : Scope to a single console short_name (e.g. NES)}
                            {--force    : Skip the confirmation prompt}
                            {--dry-run  : Run Phase 1 only — never write to the database}';

    protected $description = 'Two-phase migration: fetch IGDB data (Phase 1 preview), then persist to MySQL (Phase 2 write).';

    /**
     * Locked IGDB platform IDs (from the plan §4.1.1).
     */
    private const PLATFORM_MAP = [
        'NES'      => 18,
        'SNES'     => 19,
        'arcade'   => 52,
        'atari2600'=> 59,
        'PC'       => 13,
    ];

    public function handle(IgdbClient $igdb, GameImporter $importer): int
    {
        $this->line('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║       Vintage Consoles — IGDB Import Tool        ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->line('');

        // ------------------------------------------------------------------
        // Load JSON source
        // ------------------------------------------------------------------
        $jsonPath = storage_path('data/vintage-consoles.json');
        if (! file_exists($jsonPath)) {
            $this->error("vintage-consoles.json not found at: {$jsonPath}");
            return self::FAILURE;
        }

        $raw      = json_decode(file_get_contents($jsonPath), true);
        $consoles = $raw['consoles'] ?? [];

        if ($scopeConsole = $this->option('console')) {
            $consoles = array_filter($consoles, fn ($c) => $c['short_name'] === $scopeConsole);
            if (empty($consoles)) {
                $this->error("No console found with short_name '{$scopeConsole}'.");
                return self::FAILURE;
            }
        }

        // ------------------------------------------------------------------
        // Phase 1 — Fetch from IGDB + build MigrationPlan
        // ------------------------------------------------------------------
        $this->info('Phase 1 — Fetching IGDB data (no DB writes)...');
        $this->line('');

        $startPhase1  = microtime(true);
        $plan         = [];   // [{console, title, slug, matchType, igdbPayload|null, jsonGame}]
        $apiCallCount = 0;

        foreach ($consoles as $consoleData) {
            $shortName  = $consoleData['short_name'];
            $platformId = self::PLATFORM_MAP[$shortName] ?? null;
            $games      = $consoleData['games'] ?? [];

            if (empty($games)) {
                continue;
            }

            $this->line("  <fg=cyan>→ {$shortName}</> ({$platformId}) — " . count($games) . ' games');

            if ($platformId === null) {
                $this->warn("    No IGDB platform ID for '{$shortName}'. All games will be fallback rows.");
                foreach ($games as $game) {
                    $plan[] = $this->planEntry($consoleData, $game, 'miss', null);
                }
                continue;
            }

            // Batch fetch: one API call for the whole platform
            $titles = array_map(fn ($g) => $g['title'], $games);
            $this->line("    Batch fetching " . count($titles) . " titles...");

            $batchResults = $igdb->fetchGamesBatchForPlatform($titles, $platformId);
            $apiCallCount++;

            $batchHits = $fallbackHits = $misses = 0;

            foreach ($games as $game) {
                $key     = strtolower($game['title']);
                $payload = $batchResults[$key] ?? null;

                if ($payload !== null) {
                    $plan[] = $this->planEntry($consoleData, $game, 'batch', $payload);
                    $batchHits++;
                    continue;
                }

                // Fallback: fuzzy search
                $this->line("    <fg=yellow>↳ Fallback search:</> {$game['title']}");
                $payload = $igdb->fetchGameForConsole($game['title'], $platformId);
                $apiCallCount++;

                if ($payload !== null) {
                    $plan[] = $this->planEntry($consoleData, $game, 'fallback', $payload);
                    $fallbackHits++;
                } else {
                    $plan[] = $this->planEntry($consoleData, $game, 'miss', null);
                    $misses++;
                }
            }

            $this->line("    ✓ Batch hits: <fg=green>{$batchHits}</> | Fallback hits: <fg=yellow>{$fallbackHits}</> | Misses: <fg=red>{$misses}</>");
            $this->line('');
        }

        $elapsedPhase1 = round(microtime(true) - $startPhase1, 2);

        // ------------------------------------------------------------------
        // Phase 1 — Print preview tables
        // ------------------------------------------------------------------
        $this->printPreviewTables($plan);

        $this->line('');
        $this->info("Total IGDB API calls: {$apiCallCount}");
        $this->info("Phase 1 completed in {$elapsedPhase1}s");
        $this->line('');

        // ------------------------------------------------------------------
        // Generate Markdown report
        // ------------------------------------------------------------------
        $timestamp  = Carbon::now()->format('Y-m-d_His');
        $mdFilename = "migration-{$timestamp}.md";
        $mdPath     = storage_path("app/migration-docs/{$mdFilename}");
        $gitSha     = $this->getGitSha();
        $clientIdHint = substr((string) config('igdb.credentials.client_id', ''), -4);

        $markdown = $this->buildMarkdownReport(
            plan: $plan,
            apiCallCount: $apiCallCount,
            elapsedPhase1: $elapsedPhase1,
            gitSha: $gitSha,
            clientIdHint: $clientIdHint,
            timestamp: $timestamp,
        );

        File::ensureDirectoryExists(storage_path('app/migration-docs'));
        File::put($mdPath, $markdown);

        $this->line('');
        $this->info("📄 Migration plan saved to:");
        $this->line("   {$mdPath}");
        $this->line('   Open this file to review the full plan before confirming.');
        $this->line('');

        // ------------------------------------------------------------------
        // Dry-run stops here
        // ------------------------------------------------------------------
        if ($this->option('dry-run')) {
            $this->warn('--dry-run specified: no database writes will be made.');
            return self::SUCCESS;
        }

        // ------------------------------------------------------------------
        // Confirmation prompt
        // ------------------------------------------------------------------
        if (! $this->option('force') && ! $this->confirmMigration($plan)) {
            $abortLine = "\n## Aborted\n\nAborted at " . Carbon::now()->toIso8601String() . " — no database writes were made.\n";
            File::append($mdPath, $abortLine);
            $this->warn('Migration aborted. No changes were made.');
            return self::SUCCESS;
        }

        // ------------------------------------------------------------------
        // Phase 2 — Write to database
        // ------------------------------------------------------------------
        $this->info('Phase 2 — Writing to database...');
        $this->line('');

        $startPhase2 = microtime(true);
        $stats = ['consoles' => 0, 'games' => 0, 'genres' => 0, 'screenshots' => 0, 'misses' => []];
        $txStatus = 'committed';

        try {
            // MySQL treats TRUNCATE as DDL and issues an implicit COMMIT. Running it
            // inside DB::transaction() breaks Laravel's savepoint bookkeeping.
            $this->truncateGameTables();

            DB::transaction(function () use ($consoles, $plan, $importer, &$stats) {
                // Seed consoles
                foreach ($consoles as $consoleData) {
                    ConsoleModel::updateOrCreate(
                        ['id' => $consoleData['id']],
                        [
                            'long_name'        => $consoleData['long_name'],
                            'short_name'       => $consoleData['short_name'],
                            'description'      => $consoleData['description'] ?? null,
                            'emulator_name'    => $consoleData['emulator']['name'] ?? null,
                            'emulator_version' => $consoleData['emulator']['version'] ?? null,
                            'manufacturer'     => $consoleData['manufacturer'] ?? null,
                            'release_year'     => $consoleData['release_year'] ?? null,
                            'console_logo'     => $consoleData['console_logo'] ?? null,
                            'console_icon'     => $consoleData['console_icon'] ?? null,
                            'igdb_platform_id' => self::PLATFORM_MAP[$consoleData['short_name']] ?? null,
                            'console_bgs'      => $consoleData['console_bgs'] ?? [],
                            'specs'            => $consoleData['specs'] ?? [],
                            'community_links'  => $consoleData['community_links'] ?? [],
                            'options'          => $consoleData['options'] ?? [],
                        ]
                    );
                    $stats['consoles']++;
                }

                // Import games
                foreach ($plan as $entry) {
                    $console = ConsoleModel::where('short_name', $entry['console_short_name'])->firstOrFail();

                    if ($entry['match_type'] !== 'miss') {
                        $game = $importer->import(
                            igdbPayload: $entry['igdb_payload'],
                            console: $console,
                            localData: $entry['json_game'],
                        );
                    } else {
                        $game = $importer->importFromJson($entry['json_game'], $console);
                        $stats['misses'][] = [
                            'console' => $entry['console_short_name'],
                            'title'   => $entry['title'],
                            'slug'    => $entry['slug'],
                        ];
                    }

                    $stats['games']++;
                    $stats['screenshots'] += $game->screenshots()->count();
                    $stats['genres'] += $game->genres()->count();
                }
            });
        } catch (\Throwable $e) {
            $txStatus = 'rolled_back';
            $root = $e;
            while ($root->getPrevious() instanceof \Throwable) {
                $root = $root->getPrevious();
            }

            $this->error('Migration failed: ' . $root->getMessage());
            if ($root !== $e) {
                $this->warn('Transaction rollback failed while handling: ' . $e->getMessage());
            }

            $failLine = "\n## Phase 2 — Execution Result\n\nStatus: **ROLLED BACK** — {$root->getMessage()}\n";
            File::append($mdPath, $failLine);

            return self::FAILURE;
        }

        $elapsedPhase2 = round(microtime(true) - $startPhase2, 2);

        // ------------------------------------------------------------------
        // Write unmatched JSON report
        // ------------------------------------------------------------------
        if (! empty($stats['misses'])) {
            $unmatchedReport = [
                'generated_at' => Carbon::now()->toIso8601String(),
                'total_misses' => count($stats['misses']),
                'games'        => $stats['misses'],
            ];
            File::ensureDirectoryExists(storage_path('logs'));
            File::put(storage_path('logs/igdb-migration-unmatched.json'), json_encode($unmatchedReport, JSON_PRETTY_PRINT));
        }

        // ------------------------------------------------------------------
        // Append Phase 2 result to MD
        // ------------------------------------------------------------------
        $phase2Section = $this->buildPhase2MarkdownSection($stats, $txStatus, $elapsedPhase2);
        File::append($mdPath, $phase2Section);

        // ------------------------------------------------------------------
        // Final summary
        // ------------------------------------------------------------------
        $this->line('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║             Migration Complete!                  ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->line('');
        $this->table(
            ['', 'Count'],
            [
                ['Consoles seeded',   $stats['consoles']],
                ['Games imported',    $stats['games']],
                ['Screenshots',       $stats['screenshots']],
                ['Genre relations',   $stats['genres']],
                ['Needs IGDB sync',   count($stats['misses'])],
                ['Phase 2 elapsed',   "{$elapsedPhase2}s"],
            ]
        );

        if (! empty($stats['misses'])) {
            $this->line('');
            $this->warn('Games inserted from JSON only (needs_igdb_sync=true):');
            foreach ($stats['misses'] as $miss) {
                $this->line("  [{$miss['console']}] {$miss['title']}");
            }
            $this->line('');
            $this->info('Unmatched report: ' . storage_path('logs/igdb-migration-unmatched.json'));
        }

        $this->line('');
        $this->info("Full audit report: {$mdPath}");
        $this->line('');

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function planEntry(array $consoleData, array $game, string $matchType, ?array $igdbPayload): array
    {
        return [
            'console_short_name' => $consoleData['short_name'],
            'console_long_name'  => $consoleData['long_name'],
            'title'              => $game['title'],
            'slug'               => $game['slug'] ?? \Illuminate\Support\Str::slug($game['title']),
            'match_type'         => $matchType,
            'igdb_payload'       => $igdbPayload,
            'json_game'          => $game,
        ];
    }

    private function confirmMigration(array $plan): bool
    {
        $total   = count($plan);
        $hits    = count(array_filter($plan, fn ($e) => $e['match_type'] !== 'miss'));
        $misses  = $total - $hits;

        $this->warn("Ready to write {$total} games ({$hits} IGDB-matched, {$misses} JSON-only) to MySQL.");
        $this->warn('This will TRUNCATE games, genres, screenshots, and game_genre tables.');

        return $this->confirm('Proceed with the migration?', false);
    }

    private function printPreviewTables(array $plan): void
    {
        $byConsole = [];
        foreach ($plan as $entry) {
            $byConsole[$entry['console_short_name']][] = $entry;
        }

        foreach ($byConsole as $shortName => $entries) {
            $this->line("<fg=cyan>── {$shortName} ─────────────────────────────────</>");
            $rows = [];
            foreach (array_slice($entries, 0, 5) as $e) {
                $payload = $e['igdb_payload'];
                $rows[] = [
                    substr($e['title'], 0, 30),
                    $e['match_type'],
                    $payload['id'] ?? '—',
                    substr($payload['involved_companies'][0]['company']['name'] ?? '—', 0, 18),
                    isset($payload['first_release_date'])
                        ? date('Y', $payload['first_release_date'])
                        : ($e['json_game']['release_year'] ?? '—'),
                    isset($payload['total_rating'])
                        ? round($payload['total_rating'] / 100, 2)
                        : ($e['json_game']['rating'] ?? '—'),
                    count($payload['screenshots'] ?? []),
                ];
            }
            if (count($entries) > 5) {
                $rows[] = ['... (' . (count($entries) - 5) . ' more)', '', '', '', '', '', ''];
            }
            $this->table(
                ['Title', 'Match', 'IGDB ID', 'Publisher', 'Year', 'Rating', 'Shots'],
                $rows
            );
        }

        // Misses
        $misses = array_filter($plan, fn ($e) => $e['match_type'] === 'miss');
        if (! empty($misses)) {
            $this->line('');
            $this->warn('Games with NO IGDB match (will insert from JSON, no screenshots/poster):');
            foreach ($misses as $m) {
                $this->line("  [{$m['console_short_name']}] {$m['title']}");
            }
        }
    }

    private function getGitSha(): string
    {
        try {
            return trim(shell_exec('git rev-parse --short HEAD') ?? 'unknown');
        } catch (\Throwable) {
            return 'unknown';
        }
    }

    // -------------------------------------------------------------------------
    // Markdown generation
    // -------------------------------------------------------------------------

    private function buildMarkdownReport(
        array $plan,
        int $apiCallCount,
        float $elapsedPhase1,
        string $gitSha,
        string $clientIdHint,
        string $timestamp,
    ): string {
        $now        = Carbon::now()->toIso8601String();
        $appEnv     = app()->environment();
        $totalGames = count($plan);
        $hits       = count(array_filter($plan, fn ($e) => $e['match_type'] !== 'miss'));
        $misses     = $totalGames - $hits;
        $batches    = count(array_filter($plan, fn ($e) => $e['match_type'] === 'batch'));
        $fallbacks  = count(array_filter($plan, fn ($e) => $e['match_type'] === 'fallback'));

        $flags = collect([
            $this->option('console') ? '--console=' . $this->option('console') : null,
            $this->option('force') ? '--force' : null,
            $this->option('dry-run') ? '--dry-run' : null,
        ])->filter()->join(' ') ?: '(none)';

        $md = <<<MD
        # Vintage Consoles — IGDB Migration Report

        > **Generated:** {$now}
        > **Environment:** `{$appEnv}`
        > **Git SHA:** `{$gitSha}`
        > **IGDB Client ID (last 4):** `...{$clientIdHint}`
        > **Command flags:** `{$flags}`

        ---

        ## Phase 1 — Pre-flight Summary

        | Metric | Value |
        |---|---|
        | Total JSON games | {$totalGames} |
        | Batch hits | {$batches} |
        | Fallback hits | {$fallbacks} |
        | Misses (JSON-only) | {$misses} |
        | Total IGDB API calls | {$apiCallCount} |
        | Phase 1 elapsed | {$elapsedPhase1}s |

        MD;

        // Per-console sections
        $byConsole = [];
        foreach ($plan as $entry) {
            $byConsole[$entry['console_short_name']][] = $entry;
        }

        foreach ($byConsole as $shortName => $entries) {
            $platformId = self::PLATFORM_MAP[$shortName] ?? 'N/A';
            $md .= "\n---\n\n## Console: {$shortName} (IGDB Platform ID: {$platformId})\n\n";
            $md .= "| Title | Match | IGDB ID | Publisher | Year | Rating | Screenshots | Genres |\n";
            $md .= "|---|---|---|---|---|---|---|---|\n";

            foreach ($entries as $e) {
                $p         = $e['igdb_payload'] ?? [];
                $igdbId    = $p['id'] ?? '—';
                $pub       = $p['involved_companies'][0]['company']['name'] ?? ($e['json_game']['publisher'] ?? '—');
                $year      = isset($p['first_release_date'])
                    ? date('Y', $p['first_release_date'])
                    : ($e['json_game']['release_year'] ?? '—');
                $rating    = isset($p['total_rating'])
                    ? round($p['total_rating'] / 100, 2)
                    : ($e['json_game']['rating'] ?? '—');
                $shotCount = count($p['screenshots'] ?? []);
                $genres    = implode(', ', array_map(
                    fn ($g) => is_array($g) ? ($g['name'] ?? '') : '',
                    $p['genres'] ?? ($e['json_game']['genres'] ?? [])
                ));
                $match = $e['match_type'];
                $title = str_replace('|', '\\|', $e['title']);

                $md .= "| {$title} | {$match} | {$igdbId} | {$pub} | {$year} | {$rating} | {$shotCount} | {$genres} |\n";
            }
        }

        // Misses section
        $missEntries = array_filter($plan, fn ($e) => $e['match_type'] === 'miss');
        if (! empty($missEntries)) {
            $md .= "\n---\n\n## Misses — JSON-only rows\n\n";
            $md .= "These games had no IGDB match. They will be inserted from JSON data only: **no screenshots**, **no poster**, **no `igdb_response`**. They land with `needs_igdb_sync=true` and can be refreshed later via the admin API Fill button.\n\n";
            foreach ($missEntries as $m) {
                $md .= "- **[{$m['console_short_name']}]** {$m['title']} (`{$m['slug']}`)\n";
            }
        }

        // Phase 2 plan checklist
        $md .= <<<MD

        ---

        ## Phase 2 — Write Plan

        The following steps will execute upon confirmation:

        - [ ] `SET FOREIGN_KEY_CHECKS=0`
        - [ ] `TRUNCATE game_genre`
        - [ ] `TRUNCATE screenshots`
        - [ ] `TRUNCATE games`
        - [ ] `TRUNCATE genres`
        - [ ] `SET FOREIGN_KEY_CHECKS=1`
        - [ ] Upsert **{$totalGames}** console rows (preserving IDs)
        - [ ] Import **{$hits}** IGDB-matched games (with screenshots + genres)
        - [ ] Insert **{$misses}** JSON-only game rows (`needs_igdb_sync=true`)
        - [ ] Write `storage/logs/igdb-migration-unmatched.json`
        - [ ] Append Phase 2 result section to this file

        > `vintage-consoles.json` is **never deleted** by this command.

        MD;

        return $md;
    }

    private function buildPhase2MarkdownSection(array $stats, string $txStatus, float $elapsed): string
    {
        $now         = Carbon::now()->toIso8601String();
        $missCount   = count($stats['misses']);
        $statusBadge = $txStatus === 'committed' ? '✅ **COMMITTED**' : '❌ **ROLLED BACK**';

        $md = <<<MD

        ---

        ## Phase 2 — Execution Result

        | Metric | Value |
        |---|---|
        | Confirmed at | {$now} |
        | Transaction status | {$statusBadge} |
        | Consoles seeded | {$stats['consoles']} |
        | Games written | {$stats['games']} |
        | Screenshots written | {$stats['screenshots']} |
        | Genre relations | {$stats['genres']} |
        | Needs IGDB sync | {$missCount} |
        | Phase 2 elapsed | {$elapsed}s |

        MD;

        if ($txStatus === 'committed' && $missCount > 0) {
            $md .= "\n### Unmatched Games\n\n";
            foreach ($stats['misses'] as $miss) {
                $md .= "- **[{$miss['console']}]** {$miss['title']} (`{$miss['slug']}`)\n";
            }
            $md .= "\nFull details: `storage/logs/igdb-migration-unmatched.json`\n";
        }

        return $md;
    }

    private function truncateGameTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('game_genre')->truncate();
        DB::table('screenshots')->truncate();
        DB::table('games')->truncate();
        DB::table('genres')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
