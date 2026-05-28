<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `games` MODIFY `rom` TEXT NULL, MODIFY `poster` TEXT NULL, MODIFY `game_preview` TEXT NULL, MODIFY `cartridge` TEXT NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `games` MODIFY `rom` VARCHAR(255) NULL, MODIFY `poster` VARCHAR(255) NULL, MODIFY `game_preview` VARCHAR(255) NULL, MODIFY `cartridge` VARCHAR(255) NULL');
    }
};
