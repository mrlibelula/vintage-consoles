<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('emulator_save_states')) {
            return;
        }

        if (! Schema::hasColumn('emulator_save_states', 'game_id')) {
            return;
        }

        // Avoid doctrine/dbal dependency by using raw SQL.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $col = DB::selectOne(
            "SELECT COLUMN_TYPE as column_type
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'emulator_save_states'
               AND COLUMN_NAME = 'game_id'
             LIMIT 1"
        );

        $columnType = $col?->column_type ? (string) $col->column_type : 'varchar(128)';

        // Make legacy column nullable so new inserts (which use `game_slug`) succeed.
        DB::statement("ALTER TABLE `emulator_save_states` MODIFY `game_id` {$columnType} NULL");
    }

    public function down(): void
    {
        // Intentionally no-op: restoring NOT NULL safely requires knowing how the app
        // will populate legacy columns, and could break production again.
    }
};

