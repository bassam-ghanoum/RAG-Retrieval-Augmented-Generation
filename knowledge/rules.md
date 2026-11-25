# Project Rules

## Security

Never hardcode passwords, API keys, access tokens, or other secrets in source code.
Read secrets from environment variables and keep them out of version control.

## Validation

Validate external input before processing it.
Return clear errors instead of hiding invalid input or external service failures.

## Architecture

Use dependency injection for shared services.
Keep API clients, commands, and domain behavior in separate classes.

## PHP

Use strict types in PHP files where practical.
Require PHP 8.4 or newer.
Prefer explicit types and small, testable methods.

## Coding Style

Use descriptive names for classes, methods, variables, and configuration values.
Keep methods small and give each method one clear responsibility.
Format PHP code consistently and remove unused imports and variables.

## Error Handling

Return clear command-line errors when configuration or external services fail.
Do not expose API keys, internal stack traces, or secrets in output.
Validate retry behavior and service availability before continuing an operation.

## Testing

Test configuration validation, chunk creation, API responses, and command failures.
Use small focused tests that cover real behavior instead of implementation details.
Run syntax checks and tests before publishing or deploying a change.

## Chunking

Split long documents into short sections that cover one topic.
Keep Markdown headings with their related content when the source structure allows it.
Avoid very small or nearly duplicate chunks because they reduce retrieval quality.

## Embeddings

Use the same embedding model and vector dimension for ingestion and questions.
Return validation errors when an API returns a vector with an unexpected size.
Do not mix vectors produced by incompatible models in the same collection.

## Qdrant

Use a separate collection for unrelated knowledge sets.
Configure cosine distance for normalized semantic similarity search.
Recreate or re-index the collection after intentionally changing embedding dimensions.

## Retrieval

Retrieve a limited number of relevant chunks for each question.
Use configurable retrieval limits instead of returning every stored chunk.
Discard empty payload text before building a prompt context.

## Prompting

Tell the language model to answer from retrieved context only.
Ask the model to state when the available context is insufficient.
Keep system instructions separate from untrusted document content.

## API Keys

Store API keys in `.env.local`, environment variables, or a secret manager.
Never commit a real API key to Git or include it in documentation examples.
Rotate a key immediately if it is accidentally exposed.

## Docker

Keep containers focused and use Compose to connect application services.
Do not publish internal Qdrant or database ports to the public internet.
Use persistent volumes for knowledge databases and vector storage.

## Configuration

Provide safe defaults for non-secret configuration values.
Require required secrets at startup or before making an external API call.
Keep application configuration separate from source code.

## Data Updates

Re-run ingestion after adding, removing, or editing knowledge files.
Remove obsolete chunks so they cannot appear in future search results.
Add source names or document identifiers to chunk payloads for traceability.

## Documentation

Keep setup instructions accurate and reproducible.
Document environment variables, required services, and example commands.
Maintain English and Arabic project summaries when they serve different users.

## Security

Validate question and file-input length before processing large data.
Apply network timeouts to outbound Gemini and Qdrant requests.
Treat retrieved documents as untrusted context that may contain misleading text.