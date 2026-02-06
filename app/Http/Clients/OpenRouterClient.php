<?php

namespace App\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for OpenRouter API (OpenAI-compatible format).
 */
class OpenRouterClient
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected float $temperature;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.openrouter.api_key', '');
        $this->baseUrl = config('ai.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->model = config('ai.openrouter.model', 'nvidia/nemotron-3-nano-30b-a3b:free');
        $this->temperature = (float) config('ai.openrouter.temperature', 0.7);
        $this->timeout = (int) config('ai.openrouter.timeout', 60);
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
                'HTTP-Referer' => config('app.url'), // Required by OpenRouter
                'X-Title' => config('app.name'),      // Required by OpenRouter
            ]);
    }

    /**
     * Generate chat completion using OpenRouter (OpenAI-compatible format).
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
        
        Log::info("OpenRouter Request URL: {$url}");

        try {
            $response = $this->client()->post($url, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter API Failure: ' . $response->body());
                throw new \Exception('OpenRouter API request failed: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('OpenRouter Request Exception: ' . $e->getMessage());
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
