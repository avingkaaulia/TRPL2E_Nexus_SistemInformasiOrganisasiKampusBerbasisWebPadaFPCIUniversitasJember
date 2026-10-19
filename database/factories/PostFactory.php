<?php

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => 'Diskusi Kebijakan Luar Negeri ' . fake()->numberBetween(1, 999),
            'meta_title' => null,
            'meta_description' => null,
            'content' => 'Isi postingan FPCI UNEJ tentang diskusi hubungan internasional dan kebijakan luar negeri.',
            'id_post_category' => PostCategory::factory(),
            'post_type' => 'post',
            'id_user' => User::factory(),
            'date_published' => now()->toDateString(),
            'status' => 'publish',
            'featured_image_path' => null,
        ];
    }

    public function draft()
    {
        return $this->state(fn (array $attributes) => ['status' => 'draft']);
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function halaman()
    {
        return $this->state(fn (array $attributes) => ['post_type' => 'page']);
    }
}
