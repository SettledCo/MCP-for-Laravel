<?php

namespace NeuronAI\Tests\VectorStore;

use NeuronAI\RAG\Document;
use NeuronAI\RAG\VectorStore\MemoryVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use PHPUnit\Framework\TestCase;

class MemoryVectorStoreTest extends TestCase
{
    protected array $embedding;

    protected function setUp(): void
    {
        // embedding "Hello World!"
        $this->embedding = json_decode(file_get_contents(__DIR__ . '/../stubs/hello-world.embeddings'), true);
    }

    public function test_memory_store_instance()
    {
        $store = new MemoryVectorStore();
        $this->assertInstanceOf(VectorStoreInterface::class, $store);
    }

    public function test_add_document_and_search()
    {
        $document = new Document('Hello World!');
        $document->embedding = $this->embedding;
        $document->hash = \hash('sha256', 'Hello World!' . time());

        $store = new MemoryVectorStore();
        $store->addDocument($document);

        $results = $store->similaritySearch($this->embedding);
        $this->assertIsArray($results);
    }
}
