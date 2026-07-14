<?php

namespace Database\Factories;

use App\Enums\InteractionType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interaction>
 */
class InteractionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $post = Post::factory()->create();

        return [
            'user_id' => User::factory(),
            'post_id' => $post->id,
            'author_id' => $post->user_id,
            'type' => fake()->randomElement(InteractionType::cases())->value,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
