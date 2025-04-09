<?php

namespace Settled\MCP\RAG\Embeddings;

use Settled\MCP\RAG\Document;

interface EmbeddingsProviderInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;

    public function embedDocument(Document $document): Document;

    public function embedDocuments(array $documents): array;
}
