<?php

namespace NeuronAI\RAG\VectorStore;

use NeuronAI\RAG\Document;

interface VectorStoreInterface
{
    public function addDocument(Document $document): void;

    /**
     * @param  array<Document>  $documents
     */
    public function addDocuments(array $documents): void;

    /**
     * Return docs most similar to the embedding.
     *
     * @param  float[]  $embedding
     * @return array<Document>
     */
    public function similaritySearch(array $embedding, int $k = 4): iterable;
}
