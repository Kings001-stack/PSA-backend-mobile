<?php

namespace App\Jobs;

use App\Models\FaqDocument;
use App\Models\Medication;
use App\Services\EmbeddingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $modelType,
        public int $modelId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $embeddingService): void
    {
        try {
            if ($this->modelType === 'medication') {
                $medication = Medication::find($this->modelId);
                if ($medication) {
                    $embeddingService->processMedication($medication);
                    Log::info("Generated embeddings for medication: {$medication->name}");
                }
            } elseif ($this->modelType === 'faq') {
                $document = FaqDocument::find($this->modelId);
                if ($document) {
                    $embeddingService->processFaqDocument($document);
                    Log::info("Generated embeddings for FAQ: {$document->title}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Embedding generation failed: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Embedding job failed permanently: {$exception->getMessage()}", [
            'model_type' => $this->modelType,
            'model_id' => $this->modelId,
        ]);
    }
}
