<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = fake()->paragraph();

        return [
            'user_id' => User::factory(),
            'text' => $text,
            'image_url' => null,
            'authenticity_score' => fake()->randomFloat(4, 0.75, 0.98),
            'authenticity_breakdown' => [
                'all_caps_penalty' => 1.0,
                'link_density' => fake()->randomFloat(2, 0.9, 1.0),
                'duplicate_hash' => 1.0,
            ],
            'embedding_model' => null,
            'embedding_status' => 'pending',
            'content_hash' => hash('sha256', $text),
            'metadata_json' => [
                'word_count' => str_word_count($text),
                'has_question' => str_contains($text, '?'),
            ],
        ];
    }
}
