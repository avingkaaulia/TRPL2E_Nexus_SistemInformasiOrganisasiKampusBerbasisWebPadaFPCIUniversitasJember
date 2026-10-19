<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostCategoryFactory extends Factory
{
    public function definition()
    {
        return [
            'category_name' => fake()->unique()->word(),
            'parent_id' => null,
        ];
    }
}
