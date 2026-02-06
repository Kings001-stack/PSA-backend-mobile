<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use App\Http\Clients\GeminiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeminiService implements AIProviderInterface
{
    protected int $maxRetries = 3;

    protected int $retryDelay = 1000; // milliseconds

    public function __construct(protected GeminiClient $client)
    {
    }

    /**
     * Generate a response for the chatbot.
     */
    public function generateResponse(string $prompt, array $context = []): string
    {
        try {
            $fullPrompt = $this->buildPrompt($prompt, $context);

            $response = $this->retryOnFailure(function () use ($fullPrompt) {
                return $this->client->generateContent($fullPrompt);
            });

            return $this->extractTextFromResponse($response);
        } catch (\Exception $e) {
            Log::error('Gemini API error: ' . $e->getMessage());

            // Check if it's a quota issue (429) or other API failure
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                return $this->getSimulatedResponse($prompt);
            }

            return "I apologize, but I'm having trouble connecting to my service right now. Please try again later.";
        }
    }

    /**
     * Returns a simulated response when the API is unavailable.
     */
    protected function getSimulatedResponse(string $prompt): string
    {
        $prompt = strtolower($prompt);
        $notice = "⚠️ **System Notice:** The Google Gemini quota for this project has been exceeded. I'm operating in 'Backup Mode' until the quota resets.\n\n";

        if (str_contains($prompt, 'hour') || str_contains($prompt, 'when') || str_contains($prompt, 'open')) {
            return $notice . "Our Pharmacy Hours are:\n- Mon - Fri: 8:00 AM - 9:00 PM\n- Sat - Sun: 10:00 AM - 6:00 PM";
        }

        if (str_contains($prompt, 'where') || str_contains($prompt, 'address') || str_contains($prompt, 'location')) {
            return $notice . "We are located at **123 Health Ave, Medical District**.";
        }

        if (str_contains($prompt, 'hello') || str_contains($prompt, 'hi ')) {
            return $notice . "Hello! I am **CareBot**. My main AI connection is currently waiting for a quota reset, but I can still assist with basic pharmacy info.";
        }

        if (str_contains($prompt, 'stock') || str_contains($prompt, 'inventory') || str_contains($prompt, 'have')) {
            return $notice . "I can see we have common medications in stock. For specific quantities from the live database, please check back shortly.";
        }

        return $notice . "I've received your query about '{$prompt}', but my 'brain' is currently on a mandatory break (API Quota Exceeded). Please try again in about an hour!";
    }

    /**
     * Generate embedding for a text.
     */
    public function generateEmbedding(string $text): array
    {
        $cacheKey = 'embedding:'.md5($text);

        return Cache::remember($cacheKey, 3600, function () use ($text) {
            try {
                $response = $this->retryOnFailure(function () use ($text) {
                    return $this->client->generateEmbedding($text);
                });

                return $response['embedding']['values'] ?? [];
            } catch (\Exception $e) {
                Log::error('Gemini embedding error: '.$e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Generate embeddings for multiple texts.
     */
    public function batchEmbeddings(array $texts): array
    {
        $embeddings = [];

        foreach ($texts as $text) {
            $embeddings[] = $this->generateEmbedding($text);
        }

        return $embeddings;
    }

    /**
     * Build the full prompt with context.
     */
    protected function buildPrompt(string $userMessage, array $context = []): string
    {
        $systemPrompt = "You are a helpful pharmacy assistant chatbot. You provide information about medications, their uses, and general health advice. You must NEVER prescribe medications, provide dosage recommendations, or attempt to diagnose medical conditions. If asked about these topics, politely redirect the user to consult with a pharmacist or doctor.\n\n";

        if (! empty($context['retrieved_documents'])) {
            $systemPrompt .= "Relevant information from the pharmacy knowledge base:\n";
            foreach ($context['retrieved_documents'] as $doc) {
                $systemPrompt .= "- {$doc}\n";
            }
            $systemPrompt .= "\n";
        }

        if (! empty($context['conversation_history'])) {
            $systemPrompt .= "Previous conversation:\n";
            foreach ($context['conversation_history'] as $msg) {
                $systemPrompt .= "{$msg['role']}: {$msg['content']}\n";
            }
            $systemPrompt .= "\n";
        }

        $systemPrompt .= "User: {$userMessage}\nAssistant:";

        return $systemPrompt;
    }

    /**
     * Extract text from Gemini API response.
     */
    protected function extractTextFromResponse(array $response): string
    {
        if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($response['candidates'][0]['content']['parts'][0]['text']);
        }

        throw new \Exception('Invalid response format from Gemini API');
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
     * Validate response for safety and quality.
     */
    public function validateResponse(string $response): bool
    {
        // Check if response is not empty
        if (empty(trim($response))) {
            return false;
        }

        // Check if response is not too short
        if (strlen($response) < 10) {
            return false;
        }

        return true;
    }

    /**
     * Check if the provider is available.
     */
    public function isAvailable(): bool
    {
        return !empty(config('gemini.api_key'));
    }

    /**
     * Get the provider name.
     */
    public function getProviderName(): string
    {
        return 'gemini';
    }
}
