<?php

namespace NeuronAI\RAG\DataLoader;

use NeuronAI\RAG\Document;

interface DataLoaderInterface
{
    /**
     * @return array<Document>
     */
    public function getDocuments(): array;
}
