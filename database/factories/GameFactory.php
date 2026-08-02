<?php

namespace Database\Factories;

use App\Models\Console;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);

        return [
            'console_id' => Console::factory(),
            'igdb_id' => $this->faker->optional()->numberBetween(1, 999999),
            'title' => ucwords($title),
            'slug' => Str::slug($title),
            'publisher' => $this->faker->company(),
            'release_year' => (string) $this->faker->numberBetween(1985, 2000),
            'description' => $this->faker->paragraph(),
            'rating' => $this->faker->randomFloat(4, 0, 1),
            'multiplayer_support' => $this->faker->boolean(),
            'save_state_support' => true,
            'is_free' => true,
            'rom' => null,
            'poster' => null,
            'cover_image_id' => null,
            'game_preview' => null,
            'cartridge' => null,
            'needs_igdb_sync' => false,
            'igdb_response' => null,
            'walkthrough_videos' => null,
        ];
    }

    public function needsSync(): static
    {
        return $this->state(['needs_igdb_sync' => true, 'igdb_id' => null, 'igdb_response' => null]);
    }

    public function withIgdb(string $imageId = 'co8lo8'): static
    {
        return $this->state([
            'igdb_id' => $this->faker->numberBetween(1, 999999),
            'poster' => "https://images.igdb.com/igdb/image/upload/t_cover_big/{$imageId}.webp",
            'cover_image_id' => $imageId,
            'needs_igdb_sync' => false,
        ]);
    }
}
