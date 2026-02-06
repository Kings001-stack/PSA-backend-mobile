<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Http\Clients\KimiClient;
use Illuminate\Support\Facades\Log;

/**
 * AI Service for Kimi K2 (OpenAI-compatible API).
 */
class KimiService implements AIProviderInterface
{
    protected int $maxRetries = 3;
    protected int $retryDelay = 1000; // milliseconds

    public function __construct(protected KimiClient $client)
    {
    }

    /**
     * Generate a response from Kimi K2.
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
            Log::error('Kimi API error: ' . $e->getMessage());

            // Check if it's a rate limit issue
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'rate')) {
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
        return 'kimi';
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

        throw new \Exception('Invalid response format from Kimi API');
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
        $notice = "⚠️ **System Notice:** I'm currently experiencing high traffic and my primary AI connection is rate-limited. I'm operating in 'Essential Mode' using my backup knowledge.\n\n";

        if (str_contains($prompt, 'hour') || str_contains($prompt, 'when') || str_contains($prompt, 'open')) {
            return $notice . "Our Pharmacy Hours are:\n- Mon - Fri: 8:00 AM - 9:00 PM\n- Sat - Sun: 10:00 AM - 6:00 PM";
        }

        if (str_contains($prompt, 'where') || str_contains($prompt, 'address') || str_contains($prompt, 'location')) {
            return $notice . "We are located at **123 Health Ave, Medical District**.";
        }

        if (str_contains($prompt, 'hello') || str_contains($prompt, 'hi ')) {
            return $notice . "Hello! I am **CareBot**. I'm in power-saving mode due to high demand, but I can still tell you about our hours, location, or general stock levels.";
        }

        if (str_contains($prompt, 'stock') || str_contains($prompt, 'inventory') || str_contains($prompt, 'have')) {
            return $notice . "I can see we have Amoxicillin and Ibuprofen in the local cache. For live stock levels, please try again in a few minutes when my AI link restores.";
        }

        return $notice . "I process your request about '{$prompt}', but I need my full AI connection to give a detailed answer. Please check back in 1-2 minutes!";
    }
}
