<?php
declare(strict_types=1);

namespace App\Rag;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class EmbeddingClient
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $vectorSize,
    ) {
        if ($this->vectorSize <= 0) {
            throw new \RuntimeException('QDRANT_VECTOR_SIZE must be greater than 0.');
        }
    }

    public function embed(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            throw new \InvalidArgumentException('Text to embed cannot be empty.');
        }
        if ($this->apiKey === '') {
            throw new \RuntimeException('Set GEMINI_API_KEY in the repository-root .env.local.');
        }
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent";
        $data = $this->http->request('POST', $url, [
            'headers' => ['x-goog-api-key' => $this->apiKey, 'Content-Type' => 'application/json'],
            'json' => ['content' => ['parts' => [['text' => $text]]], 'outputDimensionality' => $this->vectorSize],
            'timeout' => 30,
        ])->toArray();
        $values = $data['embedding']['values'] ?? null;
        if (!is_array($values) || count($values) !== $this->vectorSize) {
            throw new \RuntimeException('The embedding API returned an unexpected vector size.');
        }
        return $values;
    }
}
