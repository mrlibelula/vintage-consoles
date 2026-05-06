<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emulator_control_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('console', 64);
            $table->string('game_id', 128);
            $table->string('emulator', 32);
            $table->string('profile')->default('default');
            $table->json('settings');
            $table->string('checksum', 64);
            $table->timestamps();

            $table->unique(['user_id', 'console', 'game_id', 'emulator', 'profile'], 'emulator_control_settings_unique_profile');
            $table->index(['user_id', 'console', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emulator_control_settings');
    }
};
