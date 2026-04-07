<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);
        return [
            'admin_id' => Admin::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->randomNumber(4),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'status' => 'draft',
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state([
            'status' => 'scheduled',
            'published_at' => now()->addDay(),
        ]);
    }
}
