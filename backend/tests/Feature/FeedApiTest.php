<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\RefreshPostgresDatabase;
use Tests\TestCase;

class FeedApiTest extends TestCase
{
    use RefreshPostgresDatabase;

    public function test_feed_requires_authentication(): void
    {
        $response = $this->getJson('/api/feed');

        $response->assertStatus(401);
    }

    public function test_feed_returns_paginated_posts_for_authenticated_user(): void
    {
        $viewer = $this->makeUser('viewer@example.com', 'viewer');
        $author = $this->makeUser('author@example.com', 'author');
        $this->makePost($author, 'travel stories from the mountains');
        $this->makePost($author, 'food and weekend plans for friends');

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/feed?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'text', 'author']],
                'meta' => ['page', 'per_page', 'total'],
            ]);
    }

    public function test_search_returns_semantic_results(): void
    {
        $viewer = $this->makeUser('searcher@example.com', 'searcher');
        $author = $this->makeUser('another-author@example.com', 'author2');
        $this->makePost($author, 'travel stories from the mountains');

        Sanctum::actingAs($viewer);

        $response = $this->getJson('/api/search?q=travel stories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_post_creation_and_interactions_are_persisted(): void
    {
        $viewer = $this->makeUser('poster@example.com', 'poster');
        $author = $this->makeUser('reactor@example.com', 'reactor');
        $post = $this->makePost($author, 'a genuine update from a friend');

        Sanctum::actingAs($viewer);

        $createResponse = $this->postJson('/api/posts', ['text' => 'This is a new post for the feed']);
        $createResponse->assertStatus(201);

        $interactionResponse = $this->postJson('/api/interactions', [
            'post_id' => $post->id,
            'author_id' => $author->id,
            'type' => 'reaction',
        ]);

        $interactionResponse->assertStatus(201);
    }

    private function makeUser(string $email, string $username): User
    {
        return User::query()->create([
            'name' => ucfirst($username),
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password'),
            'avatar_url' => null,
            'interest_interaction_count' => 0,
        ]);
    }

    private function makePost(User $author, string $text): Post
    {
        return Post::query()->create([
            'user_id' => $author->id,
            'text' => $text,
            'image_url' => null,
            'authenticity_score' => 0.9,
            'authenticity_breakdown' => ['image_present' => false],
            'embedding_model' => 'mock',
            'embedding_status' => 'completed',
            'content_hash' => hash('sha256', $text),
            'metadata_json' => ['word_count' => str_word_count($text)],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
