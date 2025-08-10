<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(8),
            'sentiment_score' => $this->faker->randomFloat(2, -1, 1),
        ];
    }
}
