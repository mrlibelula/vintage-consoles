<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    private const BACKUP_VERSION = 1;

    private const PREVIEW_FILE_LIST_LIMIT = 50;

    // FK insert order — restore truncates in reverse
    private const CORE_TABLES = [
        'consoles',
        'genres',
        'games',
        'game_genre',
        'screenshots',
        'app_fonts',
        'app_settings',
    ];

    /** ORDER BY column per table; `null` = no ordering (pivots). Default is `id`. */
    private const TABLE_ORDER_COLUMN = [
        'game_genre'   => null,
        'app_settings' => 'key',
    ];

    // Emulator tables, only when savestates included
    private const EMULATOR_TABLES = [
        'emulator_save_states',
        'emulator_control_settings',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    public function createBackup(bool $includeSavestates): string
    {
        $now = Carbon::now();
        $suffix = $includeSavestates ? '' : '_no-saves';
        $filename = 'backup_' . $now->format('Y-m-d_H-i-s') . $suffix . '.zip';

        $manifest = [
            'version'             => self::BACKUP_VERSION,
            'created_at'          => $now->toIso8601String(),
            'includes_savestates' => $includeSavestates,
            'core_tables'         => self::CORE_TABLES,
            'emulator_tables'     => $includeSavestates ? self::EMULATOR_TABLES : [],
        ];

        $coreData = $this->exportTables(self::CORE_TABLES, $now);

        $emulatorData = null;
        if ($includeSavestates) {
            $emulatorData = $this->exportTables(self::EMULATOR_TABLES, $now);
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'backup_');

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->addFromString('db/core.json', json_encode($coreData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($emulatorData !== null) {
            $zip->addFromString('db/user_data.json', json_encode($emulatorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        foreach (Storage::disk('local')->files('migration-docs') as $path) {
            $zip->addFromString($path, Storage::disk('local')->get($path));
        }

        foreach (Storage::disk('data')->files('chat') as $path) {
            $zip->addFromString($path, Storage::disk('data')->get($path));
        }

        if ($includeSavestates) {
            foreach (Storage::disk('savestates')->allFiles() as $path) {
                $zip->addFromString('savestates/' . $path, Storage::disk('savestates')->get($path));
            }
        }

        $zip->close();

        Storage::disk('local')->put("backups/{$filename}", file_get_contents($tmpFile));
        @unlink($tmpFile);

        return $filename;
    }

    public function listBackups(): array
    {
        $backups = [];

        foreach (Storage::disk('local')->files('backups') as $path) {
            if (! str_ends_with($path, '.zip')) {
                continue;
            }

            $filename      = basename($path);
            $size          = Storage::disk('local')->size($path);
            $lastModified  = Storage::disk('local')->lastModified($path);
            $manifest      = $this->readManifestFromZip($filename);

            $backups[] = [
                'filename'            => $filename,
                'size'                => $size,
                'size_human'          => $this->formatBytes($size),
                'modified_at'         => Carbon::createFromTimestamp($lastModified)->toIso8601String(),
                'includes_savestates' => $manifest['includes_savestates'] ?? false,
                'created_at'          => $manifest['created_at'] ?? null,
            ];
        }

        usort($backups, fn ($a, $b) => strcmp($b['modified_at'], $a['modified_at']));

        return $backups;
    }

    public function previewBackup(string $filename): array
    {
        [$zip, $tmpFile] = $this->openZip($filename);

        $manifest          = json_decode($zip->getFromName('manifest.json'), true) ?? [];
        $coreData          = json_decode($zip->getFromName('db/core.json'), true) ?? [];
        $includesSavestates = $manifest['includes_savestates'] ?? false;

        // DB diff — core tables
        $dbDiff = [];
        foreach (self::CORE_TABLES as $table) {
            $backupRows  = count($coreData['tables'][$table] ?? []);
            $currentRows = DB::table($table)->count();
            $dbDiff[$table] = [
                'backup_rows'  => $backupRows,
                'current_rows' => $currentRows,
                'diff'         => $backupRows - $currentRows,
                'in_backup'    => true,
            ];
        }

        // DB diff — emulator tables
        if ($includesSavestates) {
            $emulatorData = json_decode($zip->getFromName('db/user_data.json'), true) ?? [];
            foreach (self::EMULATOR_TABLES as $table) {
                $backupRows  = count($emulatorData['tables'][$table] ?? []);
                $currentRows = DB::table($table)->count();
                $dbDiff[$table] = [
                    'backup_rows'  => $backupRows,
                    'current_rows' => $currentRows,
                    'diff'         => $backupRows - $currentRows,
                    'in_backup'    => true,
                ];
            }
        } else {
            foreach (self::EMULATOR_TABLES as $table) {
                $dbDiff[$table] = [
                    'backup_rows'  => null,
                    'current_rows' => DB::table($table)->count(),
                    'diff'         => null,
                    'in_backup'    => false,
                ];
            }
        }

        // File diff helper
        $zipEntries = $this->collectZipEntries($zip);

        $diskMigrationDocs = $this->baseNames(Storage::disk('local')->files('migration-docs'));
        $zipMigrationDocs  = $this->baseNames(array_filter($zipEntries, fn ($e) => str_starts_with($e, 'migration-docs/')));

        $diskChat  = $this->baseNames(Storage::disk('data')->files('chat'));
        $zipChat   = $this->baseNames(array_filter($zipEntries, fn ($e) => str_starts_with($e, 'chat/')));

        $fileDiff = [
            'migration-docs' => $this->buildFileDiff($zipMigrationDocs, $diskMigrationDocs),
            'chat'           => $this->buildFileDiff($zipChat, $diskChat),
            'savestates'     => null,
        ];

        if ($includesSavestates) {
            $diskSavestates = Storage::disk('savestates')->allFiles();
            $zipSavestates  = array_values(array_map(
                fn ($e) => ltrim(substr($e, strlen('savestates/')), '/'),
                array_filter($zipEntries, fn ($e) => str_starts_with($e, 'savestates/') && ! str_ends_with($e, '/'))
            ));
            $fileDiff['savestates'] = $this->buildFileDiff($zipSavestates, $diskSavestates);
        }

        $zip->close();
        @unlink($tmpFile);

        return [
            'manifest' => [
                'filename'            => $filename,
                'created_at'          => $manifest['created_at'] ?? null,
                'includes_savestates' => $includesSavestates,
                'file_size'           => $this->formatBytes(Storage::disk('local')->size("backups/{$filename}")),
                'version'             => $manifest['version'] ?? null,
            ],
            'db'    => $dbDiff,
            'files' => $fileDiff,
        ];
    }

    public function restoreBackup(string $filename): void
    {
        [$zip, $tmpFile] = $this->openZip($filename);

        $manifest          = json_decode($zip->getFromName('manifest.json'), true) ?? [];
        $coreData          = json_decode($zip->getFromName('db/core.json'), true) ?? [];
        $includesSavestates = $manifest['includes_savestates'] ?? false;

        $driver = DB::getDriverName();

        DB::transaction(function () use ($zip, $coreData, $includesSavestates, $driver) {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            try {
                foreach (array_reverse(self::CORE_TABLES) as $table) {
                    DB::table($table)->truncate();
                }

                foreach (self::CORE_TABLES as $table) {
                    $rows = $coreData['tables'][$table] ?? [];
                    foreach (array_chunk($rows, 500) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }

                if ($includesSavestates) {
                    $emulatorData = json_decode($zip->getFromName('db/user_data.json'), true) ?? [];
                    foreach (array_reverse(self::EMULATOR_TABLES) as $table) {
                        DB::table($table)->truncate();
                    }
                    foreach (self::EMULATOR_TABLES as $table) {
                        $rows = $emulatorData['tables'][$table] ?? [];
                        foreach (array_chunk($rows, 500) as $chunk) {
                            DB::table($table)->insert($chunk);
                        }
                    }
                }
                // When savestates NOT in backup → leave emulator tables untouched
            } finally {
                if ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }
            }
        });

        // Restore migration-docs
        foreach (Storage::disk('local')->files('migration-docs') as $f) {
            Storage::disk('local')->delete($f);
        }
        foreach ($this->collectZipEntries($zip) as $entry) {
            if (str_starts_with($entry, 'migration-docs/')) {
                Storage::disk('local')->put($entry, $zip->getFromName($entry));
            }
        }

        // Restore chat
        foreach (Storage::disk('data')->files('chat') as $f) {
            Storage::disk('data')->delete($f);
        }
        foreach ($this->collectZipEntries($zip) as $entry) {
            if (str_starts_with($entry, 'chat/')) {
                Storage::disk('data')->put($entry, $zip->getFromName($entry));
            }
        }

        // Restore savestates files only when backup included them
        if ($includesSavestates) {
            foreach (Storage::disk('savestates')->allFiles() as $f) {
                Storage::disk('savestates')->delete($f);
            }
            foreach ($this->collectZipEntries($zip) as $entry) {
                if (str_starts_with($entry, 'savestates/')) {
                    $relative = substr($entry, strlen('savestates/'));
                    Storage::disk('savestates')->put($relative, $zip->getFromName($entry));
                }
            }
        }

        $zip->close();
        @unlink($tmpFile);
    }

    public function deleteBackup(string $filename): void
    {
        Storage::disk('local')->delete("backups/{$filename}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function exportTables(array $tables, Carbon $now): array
    {
        $data = [
            'version'     => self::BACKUP_VERSION,
            'exported_at' => $now->toIso8601String(),
            'tables'      => [],
        ];

        foreach ($tables as $table) {
            $query = DB::table($table);
            // Use array_key_exists — `null ?? 'id'` wrongly falls through to `id` for pivots.
            $orderColumn = array_key_exists($table, self::TABLE_ORDER_COLUMN)
                ? self::TABLE_ORDER_COLUMN[$table]
                : 'id';
            if ($orderColumn !== null) {
                $query->orderBy($orderColumn);
            }
            $data['tables'][$table] = $query->get()->map(fn ($row) => (array) $row)->toArray();
        }

        return $data;
    }

    /** Opens the ZIP from Storage into a temp file; caller must close + unlink. */
    private function openZip(string $filename): array
    {
        $content = Storage::disk('local')->get("backups/{$filename}");

        if ($content === null) {
            throw new \RuntimeException("Backup not found: {$filename}");
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'vcbkp_');
        file_put_contents($tmpFile, $content);

        $zip = new ZipArchive();
        if ($zip->open($tmpFile) !== true) {
            @unlink($tmpFile);
            throw new \RuntimeException("Cannot open backup archive: {$filename}");
        }

        return [$zip, $tmpFile];
    }

    private function readManifestFromZip(string $filename): array
    {
        try {
            [$zip, $tmpFile] = $this->openZip($filename);
            $manifest = json_decode($zip->getFromName('manifest.json'), true) ?? [];
            $zip->close();
            @unlink($tmpFile);
            return $manifest;
        } catch (\Throwable) {
            return [];
        }
    }

    /** Returns all non-directory entry names from an open ZipArchive. */
    private function collectZipEntries(ZipArchive $zip): array
    {
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! str_ends_with($name, '/')) {
                $entries[] = $name;
            }
        }
        return $entries;
    }

    private function baseNames(array $paths): array
    {
        return array_values(array_map('basename', $paths));
    }

    private function buildFileDiff(array $inBackup, array $onDisk): array
    {
        $onlyInBackup = array_values(array_diff($inBackup, $onDisk));
        $onlyOnDisk   = array_values(array_diff($onDisk, $inBackup));
        $inBoth       = array_values(array_intersect($inBackup, $onDisk));

        sort($onlyInBackup);
        sort($onlyOnDisk);
        sort($inBoth);

        $totals = [
            'only_in_backup' => count($onlyInBackup),
            'only_on_disk'   => count($onlyOnDisk),
            'in_both'        => count($inBoth),
        ];

        $truncated = array_sum($totals) > self::PREVIEW_FILE_LIST_LIMIT;

        if ($truncated) {
            return [
                'only_in_backup' => array_slice($onlyInBackup, 0, self::PREVIEW_FILE_LIST_LIMIT),
                'only_on_disk'   => array_slice($onlyOnDisk, 0, self::PREVIEW_FILE_LIST_LIMIT),
                'in_both'        => array_slice($inBoth, 0, self::PREVIEW_FILE_LIST_LIMIT),
                'totals'         => $totals,
                'truncated'      => true,
            ];
        }

        return [
            'only_in_backup' => $onlyInBackup,
            'only_on_disk'   => $onlyOnDisk,
            'in_both'        => $inBoth,
            'totals'         => $totals,
            'truncated'      => false,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1_048_576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return round($bytes / 1_048_576, 2) . ' MB';
    }
}
