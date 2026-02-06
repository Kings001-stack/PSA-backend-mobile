<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VectorStoreService
{
    protected string $apiKey;

    protected string $environment;

    protected string $indexName;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('pinecone.api_key');
        $this->environment = config('pinecone.environment');
        $this->indexName = config('pinecone.index_name');
        $this->timeout = config('pinecone.timeout', 30);
    }

    /**
     * Get the Pinecone index host URL.
     */
    protected function getIndexHost(): string
    {
        return "https://{$this->indexName}-{$this->environment}.svc.{$this->environment}.pinecone.io";
    }

    /**
     * Store a vector embedding in Pinecone.
     */
    public function upsert(string $id, array $vector, array $metadata, string $namespace): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->getIndexHost()}/vectors/upsert", [
                    'vectors' => [
                        [
                            'id' => $id,
                            'values' => $vector,
                            'metadata' => $metadata,
                        ],
                    ],
                    'namespace' => $namespace,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pinecone upsert error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Batch upsert multiple vectors.
     */
    public function batchUpsert(array $vectors, string $namespace): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->getIndexHost()}/vectors/upsert", [
                    'vectors' => $vectors,
                    'namespace' => $namespace,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pinecone batch upsert error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Query similar vectors from Pinecone.
     */
    public function query(array $vector, string $namespace, int $topK = 5, array $filter = []): array
    {
        try {
            $payload = [
                'vector' => $vector,
                'topK' => $topK,
                'namespace' => $namespace,
                'includeMetadata' => true,
            ];

            if (! empty($filter)) {
                $payload['filter'] = $filter;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->getIndexHost()}/query", $payload);

            if ($response->successful()) {
                return $response->json()['matches'] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Pinecone query error: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Delete vectors by ID.
     */
    public function delete(array $ids, string $namespace): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->getIndexHost()}/vectors/delete", [
                    'ids' => $ids,
                    'namespace' => $namespace,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pinecone delete error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Delete all vectors in a namespace.
     */
    public function deleteNamespace(string $namespace): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->getIndexHost()}/vectors/delete", [
                    'deleteAll' => true,
                    'namespace' => $namespace,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Pinecone delete namespace error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Get index statistics.
     */
    public function getStats(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Api-Key' => $this->apiKey,
                ])
                ->post("{$this->getIndexHost()}/describe_index_stats");

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Pinecone stats error: '.$e->getMessage());

            return [];
        }
    }
}
