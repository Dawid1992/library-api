<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(3),
            'isbn'           => fake()->optional()->isbn13(),
            'published_year' => fake()->year(),
            'description'    => fake()->optional()->paragraph(),
        ];
    }
}
