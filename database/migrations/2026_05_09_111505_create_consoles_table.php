<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consoles', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('long_name');
            $table->string('short_name')->unique();
            $table->text('description')->nullable();
            $table->string('emulator_name')->nullable();
            $table->string('emulator_version')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('release_year', 10)->nullable();
            $table->string('console_logo')->nullable();
            $table->string('console_icon')->nullable();
            $table->integer('igdb_platform_id')->nullable()->unique();
            $table->json('console_bgs')->nullable();
            $table->json('specs')->nullable();
            $table->json('community_links')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consoles');
    }
};
