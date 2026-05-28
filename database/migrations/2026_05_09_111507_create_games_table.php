<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('console_id')->constrained('consoles')->cascadeOnDelete();
            $table->unsignedBigInteger('igdb_id')->nullable()->unique();
            $table->string('title');
            $table->string('slug');
            $table->string('publisher')->nullable();
            $table->string('release_year', 10)->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 5, 4)->nullable();
            $table->boolean('multiplayer_support')->default(false);
            $table->boolean('save_state_support')->default(true);
            $table->boolean('is_free')->default(true);
            $table->text('rom')->nullable();
            $table->text('poster')->nullable();
            $table->string('cover_image_id')->nullable();
            $table->text('game_preview')->nullable();
            $table->text('cartridge')->nullable();
            $table->boolean('needs_igdb_sync')->default(false);
            $table->json('igdb_response')->nullable();
            $table->timestamps();

            $table->unique(['console_id', 'slug']);
            $table->index('console_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
