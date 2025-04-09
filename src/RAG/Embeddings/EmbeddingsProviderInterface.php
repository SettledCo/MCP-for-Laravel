<?php

namespace NeuronAI\RAG\Embeddings;

use NeuronAI\RAG\Document;

interface EmbeddingsProviderInterface
{
    /**
     * @return float[]
     */
    public function embedText(string $text): array;

    public function embedDocument(Document $document): Document;

    public function embedDocuments(array $documents): array;
}
