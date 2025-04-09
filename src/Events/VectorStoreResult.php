<?php

namespace Settled\MCP\Events;

use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\RAG\Document;

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
