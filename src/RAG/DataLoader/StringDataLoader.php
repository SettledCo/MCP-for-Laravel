<?php

namespace Settled\MCP\RAG\DataLoader;

use Settled\MCP\RAG\Document;
use Settled\MCP\RAG\Splitters\DocumentSplitter;

class StringDataLoader extends AbstractDataLoader
{
    public function __construct(protected string $content) {}

    public function getDocuments(): array
    {
        return DocumentSplitter::splitDocument(new Document($this->content));
    }
}
