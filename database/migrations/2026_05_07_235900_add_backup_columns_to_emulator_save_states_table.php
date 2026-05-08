<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emulator_save_states', function (Blueprint $table) {
            $table->string('backup_disk_path')->nullable()->after('disk_path');
            $table->unsignedBigInteger('backup_size_bytes')->nullable()->after('size_bytes');
            $table->string('backup_checksum', 64)->nullable()->after('checksum');
            $table->timestamp('backup_updated_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('emulator_save_states', function (Blueprint $table) {
            $table->dropColumn([
                'backup_disk_path',
                'backup_size_bytes',
                'backup_checksum',
                'backup_updated_at',
            ]);
        });
    }
};

