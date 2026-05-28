<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Screenshot;
use App\Services\Igdb\IgdbImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScreenshotFactory extends Factory
{
    protected $model = Screenshot::class;

    public function definition(): array
    {
        $imageId = $this->faker->lexify('scr????');

        return [
            'game_id' => Game::factory(),
            'igdb_image_id' => $imageId,
            'thumb_url' => IgdbImage::screenshotThumb($imageId),
            'full_url' => IgdbImage::fullScreenshot($imageId),
            'position' => $this->faker->numberBetween(0, 9),
        ];
    }
}
