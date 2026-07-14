<?php

namespace App\Services\Embedding;

use Illuminate\Support\Facades\Http;

class EmbeddingClient
{
    public function generateEmbedding(string $text): array
    {
        $provider = env('EMBEDDING_PROVIDER', 'mock');
        $dimension = (int) env('EMBEDDING_DIMENSION', 384);

        if ($provider !== 'http') {
            return $this->mockEmbedding($text, $dimension);
        }

        $serviceUrl = rtrim((string) env('EMBEDDING_SERVICE_URL', 'http://127.0.0.1:8001'), '/');

        try {
            $response = Http::timeout(3)->post($serviceUrl . '/embed', [
                'text' => $text,
            ]);

            if ($response->successful() && isset($response->json()['embedding'])) {
                $embedding = $response->json()['embedding'];

                return array_map(fn ($value) => (float) $value, array_slice($embedding, 0, $dimension));
            }
        } catch (\Throwable) {
            // Fall back to deterministic mock embeddings when the service is unavailable.
        }

        return $this->mockEmbedding($text, $dimension);
    }

    private function mockEmbedding(string $text, int $dimension): array
    {
        $hash = md5($text, true);
        $embedding = [];

        for ($index = 0; $index < $dimension; $index++) {
            $byte = ord($hash[$index % 16]);
            $value = ((($byte / 255) * 2) - 1) + (($index % 7) * 0.0001);
            $embedding[] = round($value, 6);
        }

        return $embedding;
    }
}
