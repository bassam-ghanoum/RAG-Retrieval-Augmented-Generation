# Mini RAG

A small Retrieval-Augmented Generation (RAG) CLI project built with PHP, Symfony Console, Google Gemini, and Qdrant.

## Project layout

- `app/` contains the PHP application, Composer files, knowledge sources, and project documentation.
- `docker/` and `docker-compose.yml` contain the development environment and container configuration.
- `.env.example` at the repository root is the single environment template. Keep local secrets in the root `.env` or `.env.local`; do not commit them.

## Requirements

- Docker Desktop or Docker Engine with Compose
- A Google Gemini API key
- PHP 8.4+ and Composer only when running the CLI outside Docker

## Run with Docker

Run these commands from the repository root:

1. Copy the environment template:

   ```bash
   cp .env.example .env
   ```

   On PowerShell:

   ```powershell
   Copy-Item .env.example .env
   ```

2. Set `GEMINI_API_KEY` in the root `.env`.

3. Build and start the services. The PHP container installs the locked Composer dependencies automatically:

   ```bash
   docker compose up -d --build
   ```

4. Ingest the knowledge file:

   ```bash
   docker compose run --rm app php bin/console app:ingest
   ```

5. Ask a question:

   ```bash
   docker compose run --rm app php bin/console app:ask "How should API keys be handled?"
   ```

Use `--limit` to change the number of retrieved chunks:

```bash
docker compose run --rm app php bin/console app:ask "What PHP practices should I follow?" --limit=5
```

Stop the services with `docker compose down`. Add `-v` to also remove persistent Qdrant data.

## Run locally

Create the root `.env` from `.env.example` if you have not already, set `GEMINI_API_KEY`, and ensure Qdrant is available at `http://localhost:6333`. Then run:

```bash
cd app
composer install
php bin/console app:ingest
php bin/console app:ask "What is RAG?"
```

The app loads environment settings from the repository-root `.env` and `.env.local`; there is no separate environment file inside `app/`. Docker Compose supplies its own Qdrant service URL (`http://qdrant:6333`) to the container.

## Configuration

| Variable | Default | Description |
|---|---|---|
| `GEMINI_API_KEY` | none | Required Gemini API key |
| `GEMINI_EMBEDDING_MODEL` | `gemini-embedding-001` | Gemini embedding model |
| `GEMINI_MODEL` | `gemini-3.6-flash` | Gemini generation model |
| `QDRANT_URL` | `http://localhost:6333` | Qdrant URL for local PHP runs; Compose uses its internal service URL |
| `QDRANT_COLLECTION` | `project_rules` | Vector collection name |
| `QDRANT_VECTOR_SIZE` | `768` | Embedding dimension |

`QDRANT_VECTOR_SIZE` must match the dimension returned by the selected embedding model.

## How it works

```text
knowledge/rules.md
        |
        v
Text chunks -> Gemini Embeddings -> Qdrant

Question -> Gemini Embedding -> Similarity search
                                    |
                                    v
             Retrieved context -> Gemini -> Answer
```

## Limits

This is a focused learning project, not an HTTP service, authentication system, or production-ready data pipeline. Re-run ingestion after changing the knowledge file.

## License

MIT
