<?php

namespace Settled\MCP\RAG\VectorStore;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Settled\MCP\RAG\Document;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;

class ElasticsearchVectorStore implements VectorStoreInterface
{
    protected bool $vectorDimSet = false;

    /**
     * @throws \Exception
     */
    public function __construct(
        protected Client $client,
        protected string $index
    ) {
        /** @var Elasticsearch $existResponse */
        $existResponse = $client->indices()->exists(['index' => $index]);
        $existStatusCode = $existResponse->getStatusCode();

        if ($existStatusCode === 200) {
            return;
        }

        $client->indices()->create([
            'index' => $index,
            'body' => [
                'mappings' => [
                    'properties' => [
                        'content' => [
                            'type' => 'text',
                        ],
                        'sourceType' => [
                            'type' => 'keyword',
                        ],
                        'sourceName' => [
                            'type' => 'keyword',
                        ],
                        'hash' => [
                            'type' => 'keyword',
                        ],
                        'chunkNumber' => [
                            'type' => 'integer',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function addDocument(Document $document): void
    {
        if ($document->embedding === null) {
            throw new \Exception('document embedding must be set before adding a document');
        }

        $this->mapVectorDimension(\count($document->embedding));

        $this->client->index([
            'index' => $this->index,
            'body' => [
                'embedding' => $document->embedding,
                'content' => $document->content,
                'sourceType' => $document->sourceType,
                'sourceName' => $document->sourceName,
                'hash' => $document->hash,
                'chunkNumber' => $document->chunkNumber,
            ],
        ]);

        $this->client->indices()->refresh(['index' => $this->index]);
    }

    /**
     * @param  Document[]  $documents
     *
     * @throws \Exception
     */
    public function addDocuments(array $documents, int $numberOfDocumentsPerRequest = 0): void
    {
        if ($documents === []) {
            return;
        }

        if ($documents[0]->embedding === null) {
            throw new \Exception('document embedding must be set before adding a document');
        }

        /*
         * Map vector embeddings dimension on the fly adding a document.
         */
        $this->mapVectorDimension(count($documents[0]->embedding));

        /*
         * Generate a bulk payload
         */
        $params = ['body' => []];
        foreach ($documents as $document) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                ],
            ];
            $params['body'][] = [
                'embedding' => $document->embedding,
                'content' => $document->content,
                'sourceType' => $document->sourceType,
                'sourceName' => $document->sourceName,
                'hash' => $document->hash,
                'chunkNumber' => $document->chunkNumber,
            ];

        }
        $this->client->bulk($params);
        $this->client->indices()->refresh(['index' => $this->index]);
    }

    /**
     * {@inheritDoc}
     *
     * num_candidates are used to tune approximate kNN for speed or accuracy (see : https://www.elastic.co/guide/en/elasticsearch/reference/current/knn-search.html#tune-approximate-knn-for-speed-accuracy)
     * @return array
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        if (\array_key_exists('num_candidates', $additionalArguments)) {
            $numCandidates = $additionalArguments['num_candidates'];
        } else {
            $numCandidates = \max(50, $k * 4);
        }

        $searchParams = [
            'index' => $this->index,
            'body' => [
                'knn' => [
                    'field' => 'embedding',
                    'query_vector' => $embedding,
                    'k' => $k,
                    'num_candidates' => $numCandidates,
                ],
                'sort' => [
                    '_score' => [
                        'order' => 'desc',
                    ],
                ],
            ],
        ];

        if (\array_key_exists('filter', $additionalArguments)) {
            $searchParams['body']['knn']['filter'] = $additionalArguments['filter'];
        }

        $response = $this->client->search($searchParams);

        return \array_map(function (array $item) {
            $document = new Document($item['_source']['content']);
            $document->embedding = $item['_source']['embedding'];
            $document->sourceType = $item['_source']['sourceType'];
            $document->sourceName = $item['_source']['sourceName'];
            $document->hash = $item['_source']['hash'];
            $document->chunkNumber = $item['_source']['chunkNumber'];
            return $document;
        }, $response['hits']['hits']);
    }

    private function mapVectorDimension(int $dimension): void
    {
        if ($this->vectorDimSet) {
            return;
        }

        $response = $this->client->indices()->getFieldMapping([
            'index' => $this->index,
            'fields' => 'embedding',
        ]);

        $mappings = $response[$this->index]['mappings'];
        if (
            \array_key_exists('embedding', $mappings)
            && $mappings['embedding']['mapping']['embedding']['dims'] === $dimension
        ) {
            return;
        }

        $this->client->indices()->putMapping([
            'index' => $this->index,
            'body' => [
                'properties' => [
                    'embedding' => [
                        'type' => 'dense_vector',
                        //'element_type' => 'float', // it's float by default
                        'dims' => $dimension,
                        'index' => true,
                        'similarity' => 'cosine',
                    ],
                ],
            ],
        ]);

        $this->vectorDimSet = true;
    }
}
