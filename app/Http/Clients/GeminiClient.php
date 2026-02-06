<?php

namespace App\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GeminiClient
{
    protected string $apiKey;

    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->baseUrl = config('gemini.base_url');
        $this->timeout = config('gemini.timeout', 30);
    }

    /**
     * Get HTTP client with authentication.
     */
    protected function client(): PendingRequest
    {
        return Http::withoutVerifying()
            ->timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ]);
    }

    /**
     * Generate text content using Gemini.
     */
    public function generateContent(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? config('gemini.model');
        $temperature = $options['temperature'] ?? config('gemini.temperature');
        $maxTokens = $options['max_tokens'] ?? config('gemini.max_tokens');

        $url = "{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}";
        \Log::info("Gemini Request URL: " . str_replace($this->apiKey, 'HIDDEN', $url));

        try {
            $response = $this->client()->post(
                $url,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxTokens,
                    ],
                ]
            );

            if ($response->failed()) {
                \Log::error('Gemini API Failure body: ' . $response->body());
                throw new \Exception('Gemini API request failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            \Log::error('Gemini Request Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate embeddings for text.
     */
    public function generateEmbedding(string $text): array
    {
        $model = config('gemini.embedding_model');

        $response = $this->client()->post(
            "{$this->baseUrl}/models/{$model}:embedContent?key={$this->apiKey}",
            [
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            throw new \Exception('Gemini embedding request failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Batch generate embeddings for multiple texts.
     */
    public function batchEmbeddings(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->generateEmbedding($text);
        }

        return $embeddings;
    }
}
