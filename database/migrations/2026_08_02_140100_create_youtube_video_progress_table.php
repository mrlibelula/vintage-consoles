<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_video_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('youtube_id', 32);
            $table->unsignedInteger('position_seconds')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'game_id', 'youtube_id'], 'yt_progress_user_game_video_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_video_progress');
    }
};
