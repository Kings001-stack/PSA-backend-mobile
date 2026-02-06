<?php

namespace App\Contracts;

/**
 * Interface for AI providers.
 * 
 * All AI providers (Gemini, Kimi K2, etc.) must implement this interface
 * to ensure consistent behavior across the application.
 */
interface AIProviderInterface
{
    /**
     * Generate a response from the AI provider.
     *
     * @param string $prompt The user's message or prompt
     * @param array $context Optional context (conversation history, documents, etc.)
     * @return string The AI-generated response
     */
    public function generateResponse(string $prompt, array $context = []): string;

    /**
     * Check if the provider is available and properly configured.
     *
     * @return bool True if the provider can be used
     */
    public function isAvailable(): bool;

    /**
     * Get the name of this AI provider.
     *
     * @return string Provider name (e.g., 'gemini', 'kimi')
     */
    public function getProviderName(): string;
}
