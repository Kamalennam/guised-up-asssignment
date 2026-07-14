<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Mina Patel',
                'username' => 'mina',
                'email' => 'mina@example.com',
                'password' => 'password123',
                'avatar_url' => null,
            ],
            [
                'name' => 'Omar Khan',
                'username' => 'omar',
                'email' => 'omar@example.com',
                'password' => 'password123',
                'avatar_url' => null,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::query()->firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'avatar_url' => $userData['avatar_url'],
                    'interest_interaction_count' => 0,
                ],
            );

            if ($user->posts()->count() === 0) {
                $this->createPosts($user);
            }
        }
    }

    private function createPosts(User $user): void
    {
        $posts = [
            'Morning walk around the old quarter and a tiny coffee shop that felt like home.',
            'Trying to capture the feeling of ordinary days without filters — just real light and tired eyes.',
            'My favorite part of travel is the conversations with strangers who become part of the story.',
            'A long week, but the best part was sharing dinner with friends and laughing until midnight.',
        ];

        foreach ($posts as $index => $text) {
            Post::query()->create([
                'user_id' => $user->id,
                'text' => $text,
                'image_url' => $index % 2 === 0 ? 'https://picsum.photos/seed/guised/600/400' : null,
                'authenticity_score' => 0.82 + ($index * 0.03),
                'authenticity_breakdown' => ['text_length' => strlen($text), 'image_present' => $index % 2 === 0],
                'embedding_model' => 'mock',
                'embedding_status' => 'completed',
                'content_hash' => hash('sha256', $text),
                'metadata_json' => ['word_count' => str_word_count($text)],
            ]);
        }
    }
}
