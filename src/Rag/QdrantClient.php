<?php
declare(strict_types=1);

namespace App\Rag;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class QdrantClient
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private string $baseUrl,
        private readonly string $collection,
        private readonly int $size,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        if ($this->collection === '' || preg_match('/^[A-Za-z0-9_-]+$/', $this->collection) !== 1) {
            throw new \InvalidArgumentException('QDRANT_COLLECTION contains invalid characters.');
        }
        if ($this->size <= 0) {
            throw new \InvalidArgumentException('QDRANT_VECTOR_SIZE must be greater than 0.');
        }
    }

    public function recreateCollection(): void
    {
        $delete = $this->http->request('DELETE', "{$this->baseUrl}/collections/{$this->collection}", ['timeout' => 10]);
        if ($delete->getStatusCode() !== 404) {
            $delete->getContent();
        }
        $this->http->request('PUT', "{$this->baseUrl}/collections/{$this->collection}", [
            'json' => ['vectors' => ['size' => $this->size, 'distance' => 'Cosine']],
            'timeout' => 10,
        ])->getContent();
    }

    public function upsert(int $id, array $vector, array $payload): void
    {
        $this->http->request('PUT', "{$this->baseUrl}/collections/{$this->collection}/points", ['json' => ['points' => [['id' => $id, 'vector' => $vector, 'payload' => $payload]]]])->getContent();
    }

    public function search(array $vector, int $limit): array
    {
        $data = $this->http->request('POST', "{$this->baseUrl}/collections/{$this->collection}/points/query", ['json' => ['query' => $vector, 'limit' => $limit, 'with_payload' => true]])->toArray();
        return $data['result']['points'] ?? [];
    }
}
