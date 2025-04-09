<?php

namespace NeuronAI\RAG\Embeddings;

use NeuronAI\RAG\Document;

abstract class AbstractEmbeddingsProvider implements EmbeddingsProviderInterface
{
    public function embedDocuments(array $documents): array
    {
        /** @var Document $document */
        foreach ($documents as $index => $document) {
            $documents[$index] = $this->embedDocument($document);
        }

        return $documents;
    }

    public function embedDocument(Document $document): Document
    {
        $text = $document->formattedContent ?? $document->content;
        $document->embedding = $this->embedText($text);

        return $document;
    }
}
