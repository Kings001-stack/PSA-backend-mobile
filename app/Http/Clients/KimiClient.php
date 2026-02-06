<?php

namespace App\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for Kimi K2 API (OpenAI-compatible format).
 */
class KimiClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected float $temperature;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.kimi.api_key', '');
        $this->baseUrl = config('ai.kimi.base_url', 'https://api.moonshot.ai/v1');
        $this->model = config('ai.kimi.model', 'kimi-k2-turbo-preview');
        $this->temperature = (float) config('ai.kimi.temperature', 0.7);
        $this->timeout = (int) config('ai.kimi.timeout', 60);
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
                'Authorization' => "Bearer {$this->apiKey}",
            ]);
    }

    /**
     * Generate chat completion using Kimi K2 (OpenAI-compatible format).
     *
     * @param array $messages Array of messages in OpenAI format
     * @param array $options Optional parameters (model, temperature, etc.)
     * @return array The API response
     */
    public function chatCompletion(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->model;
        $temperature = $options['temperature'] ?? $this->temperature;

        $url = "{$this->baseUrl}/chat/completions";
        
        Log::info("Kimi Request URL: {$url}");

        try {
            $response = $this->client()->post($url, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

            if ($response->failed()) {
                Log::error('Kimi API Failure: ' . $response->body());
                throw new \Exception('Kimi API request failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Kimi Request Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if the client is properly configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
