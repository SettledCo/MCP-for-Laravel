<?php

namespace Settled\MCP\RAG\DataLoader;

use Settled\MCP\RAG\Document;

interface DataLoaderInterface
{
    /**
     * @return array<Document>
     */
    public function getDocuments(): array;
}
