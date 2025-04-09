<?php

namespace Settled\MCP\RAG;

use Settled\MCP\Agent;
use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Events\InstructionsChanged;
use Settled\MCP\Events\InstructionsChanging;
use Settled\MCP\Events\VectorStoreResult;
use Settled\MCP\Events\VectorStoreSearching;
use Settled\MCP\Exceptions\MissingCallbackParameter;
use Settled\MCP\Exceptions\ToolCallableNotSet;
use Settled\MCP\RAG\Embeddings\EmbeddingsProviderInterface;
use Settled\MCP\RAG\VectorStore\VectorStoreInterface;
use Settled\MCP\SystemPrompt;

class RAG extends Agent
{
    /**
     * @var VectorStoreInterface
     */
    protected VectorStoreInterface $store;

    /**
     * The embeddings provider.
     *
     * @var EmbeddingsProviderInterface
     */
    protected EmbeddingsProviderInterface $embeddingsProvider;

    /**
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     */
    public function answer(Message $question, int $k = 4): Message
    {
        $this->notify('rag-start');

        $this->retrieval($question, $k);

        $response = $this->chat($question);

        $this->notify('rag-stop');
        return $response;
    }

    public function streamAnswer(Message $question, int $k = 4): \Generator
    {
        $this->notify('rag-start');

        $this->retrieval($question, $k);

        yield from $this->stream($question);

        $this->notify('rag-stop');
    }

    protected function retrieval(Message $question, int $k = 4): void
    {
        $this->notify(
            'rag-vectorstore-searching',
            new VectorStoreSearching($question)
        );
        $documents = $this->searchDocuments($question->getContent(), $k);
        $this->notify(
            'rag-vectorstore-result',
            new VectorStoreResult($question, $documents)
        );

        $originalInstructions = $this->instructions();
        $this->notify(
            'rag-instructions-changing',
            new InstructionsChanging($originalInstructions)
        );
        $this->setSystemMessage($documents, $k);
        $this->notify(
            'rag-instructions-changed',
            new InstructionsChanged($originalInstructions, $this->instructions())
        );
    }

    /**
     * Set the system message based on the context.
     *
     * @param array<Document> $documents
     * @param int $k
     * @return \NeuronAI\AgentInterface|RAG
     */
    public function setSystemMessage(array $documents, int $k)
    {
        $context = '';
        $i = 0;
        foreach ($documents as $document) {
            if ($i >= $k) {
                break;
            }
            $i++;
            $context .= $document->content.' ';
        }

        return $this->setInstructions(
            new SystemPrompt(
                background: ["You are an AI Agent used for Retrieval Augmented Generation."],
                steps: ["Use the following pieces of context to answer the user question. "],
                output: ["If you don't know the answer, just say that you don't know, don't try to make up an answer."],
                context: [$context]
            )
        );
    }

    /**
     * Retrieve relevant documents from the vector store.
     *
     * @param string $question
     * @param int $k
     * @return array<Document>
     */
    private function searchDocuments(string $question, int $k): array
    {
        $embedding = $this->embeddings()->embedText($question);
        $docs = $this->vectorStore()->similaritySearch($embedding, $k);

        $retrievedDocs = [];

        foreach ($docs as $doc) {
            //md5 for removing duplicates
            $retrievedDocs[\md5($doc->content)] = $doc;
        }

        return \array_values($retrievedDocs);
    }

    public function setEmbeddingsProvider(EmbeddingsProviderInterface $provider): self
    {
        $this->embeddingsProvider = $provider;
        return $this;
    }

    public function embeddings(): EmbeddingsProviderInterface
    {
        return $this->embeddingsProvider;
    }

    public function setVectorStore(VectorStoreInterface $store): self
    {
        $this->store = $store;
        return $this;
    }

    public function vectorStore(): VectorStoreInterface
    {
        return $this->store;
    }
}
