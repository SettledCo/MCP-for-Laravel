<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\RAG\Document;

class VectorStoreResult
{
    /**
     * @param array<Document> $documents
     */
    public function __construct(
        public Message $question,
        public array $documents,
    ) {}
}
