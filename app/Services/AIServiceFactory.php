<?php

namespace App\Services;

use App\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Factory for creating AI provider instances.
 * 
 * Resolves the correct AI provider based on the AI_PROVIDER configuration.
 */
class AIServiceFactory
{
    /**
     * Create an AI provider instance based on configuration.
     *
     * @return AIProviderInterface
     */
    public static function make(): AIProviderInterface
    {
        $provider = config('ai.provider', 'gemini');

        Log::info("AI Provider: {$provider}");

        return match ($provider) {
            'kimi' => app(KimiService::class),
            'openrouter' => app(OpenRouterService::class),
            default => app(GeminiService::class),
        };
    }

    /**
     * Get all available providers.
     *
     * @return array
     */
    public static function availableProviders(): array
    {
        return ['gemini', 'kimi', 'openrouter'];
    }

    /**
     * Check if a provider is valid.
     *
     * @param string $provider
     * @return bool
     */
    public static function isValidProvider(string $provider): bool
    {
        return in_array($provider, self::availableProviders());
    }
}
