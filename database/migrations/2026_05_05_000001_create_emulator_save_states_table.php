<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emulator_save_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('console', 64);
            $table->string('game_slug', 128);
            $table->unsignedTinyInteger('slot');
            $table->string('label')->nullable();
            $table->string('disk_path');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 64);
            $table->timestamps();

            $table->unique(['user_id', 'console', 'game_slug', 'slot'], 'emulator_save_states_unique_slot');
            $table->index(['user_id', 'console', 'game_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emulator_save_states');
    }
};
