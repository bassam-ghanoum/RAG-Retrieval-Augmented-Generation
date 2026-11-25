<?php
declare(strict_types=1);

namespace App\Rag;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GeminiClient
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly string $apiKey,
        private readonly string $model,
    ) {}

    public function generate(string $question, string $context): string
    {
        if (trim($question) === '') {
            throw new \InvalidArgumentException('Question cannot be empty.');
        }
        if (trim($context) === '') {
            throw new \RuntimeException('No relevant context was retrieved.');
        }
        if ($this->apiKey === '') {
            throw new \RuntimeException('Set GEMINI_API_KEY in the repository-root .env.local.');
        }
        $prompt = "Answer the question using only the retrieved context below. "
            ."If the context is insufficient, say that clearly.\n\n"
            ."Retrieved context:\n{$context}\n\nQuestion:\n{$question}";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
        $data = $this->http->request('POST', $url, [
            'headers' => ['x-goog-api-key' => $this->apiKey, 'Content-Type' => 'application/json'],
            'json' => ['contents' => [['parts' => [['text' => $prompt]]]]],
            'timeout' => 60,
        ])->toArray();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? throw new \RuntimeException('No LLM answer returned.');
    }
}
