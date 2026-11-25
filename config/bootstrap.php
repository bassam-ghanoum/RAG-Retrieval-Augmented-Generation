<?php

use App\Rag\EmbeddingClient;
use App\Rag\GeminiClient;
use App\Rag\QdrantClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__, 2).'/.env';
if (is_file($envFile)) {
    (new Dotenv())->loadEnv($envFile);
}

$container = new ContainerBuilder();
$container->set('http_client', HttpClient::create());

$container->register(EmbeddingClient::class)
    ->setPublic(true)
    ->setArguments([
        new Reference('http_client'),
        $_ENV['GEMINI_API_KEY'] ?? '',
        $_ENV['GEMINI_EMBEDDING_MODEL'] ?? 'gemini-embedding-001',
        (int)($_ENV['QDRANT_VECTOR_SIZE'] ?? 768)
    ]);

$container->register(GeminiClient::class)
    ->setPublic(true)
    ->setArguments([
        new Reference('http_client'),
        $_ENV['GEMINI_API_KEY'] ?? '',
        $_ENV['GEMINI_MODEL'] ?? 'gemini-3.6-flash'
    ]);

$container->register(QdrantClient::class)
    ->setPublic(true)
    ->setArguments([
        new Reference('http_client'),
        $_ENV['QDRANT_URL'] ?? 'http://localhost:6333',
        $_ENV['QDRANT_COLLECTION'] ?? 'project_rules',
        (int)($_ENV['QDRANT_VECTOR_SIZE'] ?? 768)
    ]);

$container->compile();
return $container;
