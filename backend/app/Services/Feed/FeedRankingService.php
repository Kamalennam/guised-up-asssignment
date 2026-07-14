<?php

namespace App\Services\Feed;

class FeedRankingService
{
    /**
     * @param array<int, array<string, mixed>> $posts
     * @param array<string, mixed> $viewerContext
     * @return array<int, array<string, mixed>>
     */
    public function rank(array $posts, array $viewerContext = []): array
    {
        $weights = [
            'semantic' => (float) env('FEED_WEIGHT_SEMANTIC', 0.40),
            'relationship' => (float) env('FEED_WEIGHT_RELATIONSHIP', 0.30),
            'authenticity' => (float) env('FEED_WEIGHT_AUTHENTICITY', 0.20),
            'time' => (float) env('FEED_WEIGHT_TIME', 0.10),
        ];

        $viewerEmbedding = $viewerContext['interest_embedding'] ?? null;
        $relationshipCounts = $viewerContext['relationship_counts'] ?? [];
        $now = now();

        foreach ($posts as $index => $post) {
            $semanticScore = $this->semanticScore($post, $viewerEmbedding);
            $relationshipScore = $this->relationshipScore($post, $relationshipCounts);
            $authenticityScore = $this->authenticityScore($post);
            $timeScore = $this->timeScore($post, $now);

            $posts[$index]['feed_score'] = round(
                ($semanticScore * $weights['semantic'])
                + ($relationshipScore * $weights['relationship'])
                + ($authenticityScore * $weights['authenticity'])
                + ($timeScore * $weights['time']),
                6,
            );
        }

        usort($posts, fn (array $left, array $right): int => $right['feed_score'] <=> $left['feed_score']);

        return $posts;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<int, float>|null $viewerEmbedding
     */
    private function semanticScore(array $post, ?array $viewerEmbedding): float
    {
        $embedding = $post['embedding'] ?? null;

        if (is_array($embedding) && is_array($viewerEmbedding) && count($embedding) > 0 && count($viewerEmbedding) > 0) {
            return $this->cosineSimilarity($embedding, $viewerEmbedding);
        }

        $text = (string) ($post['text'] ?? '');
        if ($text === '') {
            return 0.5;
        }

        return 0.5 + min(0.4, strlen($text) / 5000);
    }

    /**
     * @param array<string, mixed> $post
     * @param array<int, int> $relationshipCounts
     */
    private function relationshipScore(array $post, array $relationshipCounts): float
    {
        $authorId = (int) ($post['author_id'] ?? 0);
        if ($authorId === 0) {
            return 0.2;
        }

        $count = $relationshipCounts[$authorId] ?? 0;
        if ($count <= 0) {
            return 0.2;
        }

        return min(1.0, 0.2 + ($count * 0.1));
    }

    /**
     * @param array<string, mixed> $post
     */
    private function authenticityScore(array $post): float
    {
        $raw = (float) ($post['authenticity_score'] ?? 0.5);
        $text = (string) ($post['text'] ?? '');
        $wordCount = max(1, str_word_count($text));
        $lengthBonus = min(0.1, $wordCount / 1000);

        return min(1.0, $raw + $lengthBonus);
    }

    /**
     * @param array<string, mixed> $post
     */
    private function timeScore(array $post, \DateTimeInterface $now): float
    {
        $createdAt = $post['created_at'] ?? null;
        if ($createdAt === null) {
            return 0.5;
        }

        $created = is_string($createdAt) ? new \DateTimeImmutable($createdAt) : $createdAt;
        $hoursAgo = max(1, abs($now->getTimestamp() - $created->getTimestamp()) / 3600);

        return max(0.1, 1 - min(1.0, $hoursAgo / 720));
    }

    /**
     * @param array<int, float> $left
     * @param array<int, float> $right
     */
    private function cosineSimilarity(array $left, array $right): float
    {
        $dot = 0.0;
        $leftNorm = 0.0;
        $rightNorm = 0.0;

        $length = min(count($left), count($right));
        for ($index = 0; $index < $length; $index++) {
            $leftValue = (float) $left[$index];
            $rightValue = (float) $right[$index];
            $dot += $leftValue * $rightValue;
            $leftNorm += $leftValue * $leftValue;
            $rightNorm += $rightValue * $rightValue;
        }

        if ($leftNorm === 0.0 || $rightNorm === 0.0) {
            return 0.0;
        }

        return max(-1.0, min(1.0, $dot / (sqrt($leftNorm) * sqrt($rightNorm))));
    }
}
