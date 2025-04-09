<?php

namespace Settled\MCP\RAG\VectorStore;

use Settled\MCP\Exceptions\SimilarityCalculationException;
use Settled\MCP\RAG\Document;

class MemoryVectorStore implements VectorStoreInterface
{
    /** @var array<Document> */
    private array $documents = [];

    public function addDocument(Document $document): void
    {
        $this->documents[] = $document;
    }

    public function addDocuments(array $documents): void
    {
        $this->documents = \array_merge($this->documents, $documents);
    }

    /**
     * @throws SimilarityCalculationException
     */
    public function similaritySearch(array $embedding, int $k = 4): array
    {
        $distances = [];

        foreach ($this->documents as $index => $document) {
            if ($document->embedding === null) {
                throw new \Exception("Document with the following content has no embedding: {$document->content}");
            }
            $dist = $this->cosineSimilarity($embedding, $document->embedding);
            $distances[$index] = $dist;
        }

        \asort($distances); // Sort by distance (ascending).

        $topKIndices = \array_slice(\array_keys($distances), 0, $k, true);

        return \array_reduce($topKIndices, function ($carry, $index) {
            $carry[] = $this->documents[$index];
            return $carry;
        }, []);
    }


    public function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (\count($vector1) !== \count($vector2)) {
            throw new SimilarityCalculationException('Arrays must have the same length to apply cosine similarity.');
        }

        // Calculate the dot product of the two vectors
        $dotProduct = \array_sum(\array_map(fn (float $a, float $b): float => $a * $b, $vector1, $vector2));

        // Calculate the magnitudes of each vector
        $magnitude1 = \sqrt(\array_sum(\array_map(fn (float $a): float => $a * $a, $vector1)));

        $magnitude2 = \sqrt(\array_sum(\array_map(fn (float $a): float => $a * $a, $vector2)));

        // Avoid division by zero
        if ($magnitude1 * $magnitude2 == 0) {
            return 0;
        }

        // Calculate the cosine distance
        return 1 - $dotProduct / ($magnitude1 * $magnitude2);
    }
}
