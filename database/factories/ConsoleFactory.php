<?php

namespace Database\Factories;

use App\Models\Console;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsoleFactory extends Factory
{
    protected $model = Console::class;

    private static int $sequence = 100;

    public function definition(): array
    {
        return [
            'id' => self::$sequence++,
            'long_name' => $this->faker->words(3, true),
            'short_name' => strtoupper($this->faker->unique()->lexify('???')),
            'description' => $this->faker->sentence(),
            'emulator_name' => 'EmulatorJS',
            'emulator_version' => '4.2.3',
            'manufacturer' => $this->faker->company(),
            'release_year' => (string) $this->faker->numberBetween(1977, 1995),
            'console_logo' => null,
            'console_icon' => null,
            'igdb_platform_id' => $this->faker->unique()->numberBetween(1, 9999),
            'console_bgs' => [],
            'specs' => [],
            'community_links' => [],
            'options' => [],
        ];
    }

    public function nes(): static
    {
        return $this->state([
            'id' => 1,
            'long_name' => 'Nintendo Entertainment System',
            'short_name' => 'NES',
            'igdb_platform_id' => 18,
        ]);
    }
}
