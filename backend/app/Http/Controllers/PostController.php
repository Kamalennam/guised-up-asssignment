<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Services\Embedding\EmbeddingClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function __construct(private readonly EmbeddingClient $embeddingClient)
    {
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $user = $request->user();
        $text = trim($request->validated('text'));
        $embedding = $this->embeddingClient->generateEmbedding($text);
        $authenticityScore = $this->authenticityScore($text, $request->validated('image_url'));

        $post = $user->posts()->create([
            'text' => $text,
            'image_url' => $request->validated('image_url'),
            'authenticity_score' => $authenticityScore,
            'authenticity_breakdown' => [
                'text_length' => strlen($text),
                'image_present' => $request->has('image_url') && $request->validated('image_url') !== null,
            ],
            'embedding_model' => env('EMBEDDING_MODEL', 'mock'),
            'embedding_status' => 'completed',
            'content_hash' => hash('sha256', $text),
            'metadata_json' => [
                'word_count' => str_word_count($text),
            ],
        ]);

        DB::statement('UPDATE posts SET embedding = ?::vector WHERE id = ?', [$this->toVectorString($embedding), $post->id]);

        return response()->json([
            'data' => [
                'id' => $post->id,
                'text' => $post->text,
                'image_url' => $post->image_url,
                'authenticity_score' => $post->authenticity_score,
                'created_at' => $post->created_at?->toISOString(),
                'author' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                ],
            ],
        ], 201);
    }

    private function authenticityScore(string $text, ?string $imageUrl): float
    {
        $lengthFactor = min(0.3, max(0.05, strlen($text) / 1500));
        $imageFactor = $imageUrl !== null ? 0.05 : 0.0;
        $capsPenalty = str_contains($text, strtoupper($text)) && str_contains($text, ' ') ? 0.05 : 0.0;

        return round(min(0.99, 0.7 + $lengthFactor + $imageFactor - $capsPenalty), 4);
    }

    private function toVectorString(array $embedding): string
    {
        return '[' . implode(', ', array_map(fn (float $value): string => number_format($value, 6, '.', ''), $embedding)) . ']';
    }
}
