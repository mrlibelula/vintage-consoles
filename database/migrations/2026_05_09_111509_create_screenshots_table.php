<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->string('igdb_image_id')->nullable();
            $table->string('thumb_url');
            $table->string('full_url');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['game_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
    }
};
