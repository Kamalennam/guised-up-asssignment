<?php

namespace Tests\Unit;

use App\Services\Feed\FeedRankingService;
use Tests\TestCase;

class FeedRankingServiceTest extends TestCase
{
    public function test_ranking_prefers_higher_relationship_and_authenticity_scores(): void
    {
        $service = new FeedRankingService();
        $posts = [
            [
                'id' => 1,
                'author_id' => 2,
                'text' => 'A short authentic post about real life.',
                'authenticity_score' => 0.70,
                'created_at' => now()->subHours(2)->toISOString(),
                'embedding' => [0.5, 0.5],
            ],
            [
                'id' => 2,
                'author_id' => 3,
                'text' => 'A polished update from a connection with lots of activity.',
                'authenticity_score' => 0.90,
                'created_at' => now()->subHours(3)->toISOString(),
                'embedding' => [0.2, 0.2],
            ],
        ];

        $ranked = $service->rank($posts, [
            'interest_embedding' => [0.5, 0.5],
            'relationship_counts' => [2 => 4],
        ]);

        $this->assertGreaterThan($ranked[1]['feed_score'], $ranked[0]['feed_score']);
    }
}
