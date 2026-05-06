<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('emulator_save_states', 'game_slug')) {
            return;
        }

        Schema::table('emulator_save_states', function (Blueprint $table) {
            $table->string('game_slug', 128)->nullable()->after('console');
            $table->index(['user_id', 'console', 'game_slug'], 'emulator_save_states_user_console_game_slug_idx');
        });

        // Best-effort backfill for older production schemas.
        // - If a legacy `game_id` column exists, use it as the slug value.
        // - Otherwise, try to parse `{userId}/{console}/{gameSlug}/...` from `disk_path`.
        $hasLegacyGameId = Schema::hasColumn('emulator_save_states', 'game_id');

        DB::table('emulator_save_states')
            ->select(['id', $hasLegacyGameId ? 'game_id' : DB::raw('NULL as game_id'), 'disk_path'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($hasLegacyGameId) {
                foreach ($rows as $row) {
                    $gameSlug = null;

                    if ($hasLegacyGameId && isset($row->game_id) && $row->game_id !== null && $row->game_id !== '') {
                        $gameSlug = (string) $row->game_id;
                    } elseif (isset($row->disk_path) && is_string($row->disk_path) && $row->disk_path !== '') {
                        $parts = explode('/', str_replace('\\', '/', $row->disk_path));
                        // {userId}/{console}/{gameSlug}/...
                        if (count($parts) >= 3 && $parts[2] !== '') {
                            $gameSlug = (string) $parts[2];
                        }
                    }

                    if ($gameSlug !== null) {
                        DB::table('emulator_save_states')
                            ->where('id', $row->id)
                            ->update(['game_slug' => $gameSlug]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('emulator_save_states', 'game_slug')) {
            return;
        }

        Schema::table('emulator_save_states', function (Blueprint $table) {
            $table->dropIndex('emulator_save_states_user_console_game_slug_idx');
            $table->dropColumn('game_slug');
        });
    }
};

