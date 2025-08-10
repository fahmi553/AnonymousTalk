<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraph(3),
            'category' => $this->faker->randomElement(['General', 'Help', 'News']),
            'sentiment_score' => $this->faker->randomFloat(2, -1, 1),
            'status' => 'published',
        ];
    }
}
