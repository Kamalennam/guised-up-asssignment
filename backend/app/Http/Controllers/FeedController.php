<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Post;
use App\Services\Feed\FeedRankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FeedController extends Controller
{
    public function __construct(private readonly FeedRankingService $rankingService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));
        $candidateLimit = max(50, (int) config('feed.candidate_limit', 500));
        $candidateDays = max(1, (int) config('feed.candidate_days', 30));

        $posts = Post::query()
            ->with('author')
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>=', now()->subDays($candidateDays))
            ->latest('created_at')
            ->limit($candidateLimit)
            ->get()
            ->map(function (Post $post) {
                $embedding = $post->getAttribute('embedding');
                $post->setAttribute('embedding', $embedding !== null ? $this->decodeEmbedding($embedding) : null);

                return $post;
            })
            ->all();

        $relationshipCounts = Interaction::query()
            ->where('user_id', $user->id)
            ->selectRaw('author_id, count(*) as interaction_count')
            ->groupBy('author_id')
            ->pluck('interaction_count', 'author_id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $ranked = $this->rankingService->rank(array_map(function (Post $post) use ($user): array {
            return [
                'id' => $post->id,
                'author_id' => $post->user_id,
                'text' => $post->text,
                'image_url' => $post->image_url,
                'authenticity_score' => (float) $post->authenticity_score,
                'created_at' => $post->created_at?->toISOString(),
                'author' => [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                    'username' => $post->author->username,
                    'avatar_url' => $post->author->avatar_url,
                ],
                'embedding' => $post->getAttribute('embedding'),
            ];
        }, $posts), [
            'interest_embedding' => null,
            'relationship_counts' => $relationshipCounts,
        ]);

        $items = array_slice($ranked, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'data' => array_map(function (array $item): array {
                return [
                    'id' => $item['id'],
                    'text' => $item['text'],
                    'image_url' => $item['image_url'],
                    'authenticity_score' => $item['authenticity_score'],
                    'created_at' => $item['created_at'],
                    'feed_score' => $item['feed_score'],
                    'author' => $item['author'],
                ];
            }, $items),
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => count($ranked),
            ],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json(['data' => []]);
        }

        $posts = Post::query()
            ->with('author')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest('created_at')
            ->limit(50)
            ->get();

        $scored = [];
        foreach ($posts as $post) {
            $embedding = $post->embedding !== null ? $this->decodeEmbedding($post->embedding) : null;
            $queryEmbedding = app(\App\Services\Embedding\EmbeddingClient::class)->generateEmbedding($query);
            $score = 0.0;

            if ($embedding !== null && count($embedding) > 0) {
                $score = $this->cosineSimilarity($embedding, $queryEmbedding);
            } else {
                $score = similar_text(strtolower($query), strtolower($post->text));
            }

            $scored[] = [
                'id' => $post->id,
                'score' => $score,
                'text' => $post->text,
                'author' => [
                    'username' => $post->author->username,
                    'name' => $post->author->name,
                ],
            ];
        }

        usort($scored, fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return response()->json([
            'data' => array_slice($scored, 0, 10),
        ]);
    }

    private function decodeEmbedding(mixed $value): array
    {
        if (is_array($value)) {
            return array_map(fn ($item) => (float) $item, $value);
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return array_map(fn ($item) => (float) $item, $decoded);
            }
        }

        return [];
    }

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
