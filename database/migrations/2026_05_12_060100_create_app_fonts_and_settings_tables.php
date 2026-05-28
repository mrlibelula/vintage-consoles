<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('family_name');
            $table->string('relative_path');
            $table->string('format', 8);
            $table->boolean('is_bundled')->default(false);
            $table->timestamps();

            $table->unique('relative_path');
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->timestamps();
        });

        $now = now();

        DB::table('app_fonts')->insert([
            [
                'label' => 'VT323',
                'family_name' => 'VT323',
                'relative_path' => 'VT323-Regular.ttf',
                'format' => 'ttf',
                'is_bundled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'label' => 'HackerNoon V2',
                'family_name' => 'HackerNoonV2',
                'relative_path' => 'HackerNoonV2-Regular.otf',
                'format' => 'otf',
                'is_bundled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('app_settings')->insert([
            'key' => 'active_app_font_id',
            'value' => '1',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('app_fonts');
    }
};
