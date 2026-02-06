<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Http\Clients\OpenRouterClient;
use Illuminate\Support\Facades\Log;

/**
 * AI Service for OpenRouter.
 */
class OpenRouterService implements AIProviderInterface
{
    protected int $maxRetries = 3;
    protected int $retryDelay = 1000; // milliseconds

    public function __construct(protected OpenRouterClient $client)
    {
    }

    /**
     * Generate a response from OpenRouter.
     */
    public function generateResponse(string $prompt, array $context = []): string
    {
        try {
            $messages = $this->buildMessages($prompt, $context);

            $response = $this->retryOnFailure(function () use ($messages) {
                return $this->client->chatCompletion($messages);
            });

            return $this->extractTextFromResponse($response);
        } catch (\Exception $e) {
            Log::error('OpenRouter API error: ' . $e->getMessage());

            // Check if it's a rate limit issue
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota') || str_contains($e->getMessage(), 'rate')) {
                return $this->getSimulatedResponse($prompt);
            }

            return "I apologize, but I'm having trouble connecting to my service right now. Please try again later.";
        }
    }

    /**
     * Check if the provider is available.
     */
    public function isAvailable(): bool
    {
        return $this->client->isConfigured();
    }

    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'openrouter';
    }

    /**
     * Build messages array in OpenAI chat format.
     */
    protected function buildMessages(string $userMessage, array $context = []): array
    {
         // If the message already contains "You are CareBot" or "You are the AI assistant", 
        // it means the controller has already built a full prompt with its own instructions.
        if (str_contains($userMessage, 'You are')) {
            return [
                ['role' => 'user', 'content' => $userMessage]
            ];
        }

        $messages = [];
        $systemContent = "You are CareBot, a helpful pharmacy assistant for MediCare Pharmacy. You provide information about medications, prices, and pharmacy hours.";

        if (!empty($context['retrieved_documents'])) {
            $systemContent .= "\n\nRelevant information:\n";
            foreach ($context['retrieved_documents'] as $doc) {
                $systemContent .= "- {$doc}\n";
            }
        }

        $messages[] = ['role' => 'system', 'content' => $systemContent];

        if (!empty($context['conversation_history'])) {
            foreach ($context['conversation_history'] as $msg) {
                $messages[] = [
                    'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $msg['content'],
                ];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    /**
     * Extract text from OpenAI-style response.
     */
    protected function extractTextFromResponse(array $response): string
    {
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        }

        throw new \Exception('Invalid response format from OpenRouter API');
    }

    /**
     * Retry a function on failure.
     */
    protected function retryOnFailure(callable $callback)
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $attempts++;

                if ($attempts >= $this->maxRetries) {
                    throw $e;
                }

                usleep($this->retryDelay * 1000 * $attempts);
            }
        }
    }

    /**
     * Returns a simulated response when the API is unavailable.
     */
    protected function getSimulatedResponse(string $prompt): string
    {
        $prompt = strtolower($prompt);
        $notice = "⚠️ **System Notice:** OpenRouter is currently rate-limited. Operating in 'Essential Mode'.\n\n";

        if (str_contains($prompt, 'hour') || str_contains($prompt, 'when') || str_contains($prompt, 'open')) {
            return $notice . "Our Pharmacy Hours are:\n- Mon - Fri: 8:00 AM - 9:00 PM\n- Sat - Sun: 10:00 AM - 6:00 PM";
        }

        if (str_contains($prompt, 'where') || str_contains($prompt, 'address') || str_contains($prompt, 'location')) {
            return $notice . "We are located at **123 Health Ave, Medical District**.";
        }

        if (str_contains($prompt, 'hello') || str_contains($prompt, 'hi ')) {
            return $notice . "Hello! I am **CareBot**. I'm in power-saving mode, but I can still help with basic pharmacy info.";
        }

        return $notice . "I've received your query, but my AI link is throttled. Please try again soon!";
    }
}
