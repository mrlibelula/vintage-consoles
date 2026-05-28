<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        $name = Str::slug($this->faker->unique()->word());

        return [
            'name' => $name,
            'description' => $this->faker->sentence(),
            'igdb_id' => $this->faker->optional()->numberBetween(1, 50),
        ];
    }
}
