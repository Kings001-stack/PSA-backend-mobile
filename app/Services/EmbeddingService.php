<?php

namespace App\Services;

use App\Models\FaqDocument;
use App\Models\Medication;
use App\Models\Tenant;

class EmbeddingService
{
    public function __construct(
        protected GeminiService $geminiService,
        protected VectorStoreService $vectorStore
    ) {
    }

    /**
     * Process and store embeddings for a medication.
     */
    public function processMedication(Medication $medication): bool
    {
        $text = $this->buildMedicationText($medication);
        $chunks = $this->chunkText($text);

        foreach ($chunks as $index => $chunk) {
            $embedding = $this->geminiService->generateEmbedding($chunk);

            $this->vectorStore->upsert(
                "medication_{$medication->id}_chunk_{$index}",
                $embedding,
                [
                    'type' => 'medication',
                    'medication_id' => $medication->id,
                    'content' => $chunk,
                    'title' => $medication->name,
                ],
                $medication->tenant->pinecone_namespace
            );
        }

        return true;
    }

    /**
     * Process and store embeddings for an FAQ document.
     */
    public function processFaqDocument(FaqDocument $document): bool
    {
        $chunks = $this->chunkText($document->content);

        foreach ($chunks as $index => $chunk) {
            $embedding = $this->geminiService->generateEmbedding($chunk);

            $vectorId = "faq_{$document->id}_chunk_{$index}";

            $this->vectorStore->upsert(
                $vectorId,
                $embedding,
                [
                    'type' => 'faq',
                    'document_id' => $document->id,
                    'content' => $chunk,
                    'title' => $document->title,
                    'category' => $document->category,
                ],
                $document->tenant->pinecone_namespace
            );
        }

        $document->update([
            'is_indexed' => true,
            'pinecone_id' => "faq_{$document->id}",
        ]);

        return true;
    }

    /**
     * Batch process multiple medications.
     */
    public function batchProcessMedications(Tenant $tenant): int
    {
        $medications = Medication::where('tenant_id', $tenant->id)->get();
        $processed = 0;

        foreach ($medications as $medication) {
            if ($this->processMedication($medication)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Build searchable text from medication data.
     */
    protected function buildMedicationText(Medication $medication): string
    {
        $parts = [
            "Medication: {$medication->name}",
        ];

        if ($medication->generic_name) {
            $parts[] = "Generic Name: {$medication->generic_name}";
        }

        if ($medication->description) {
            $parts[] = "Description: {$medication->description}";
        }

        if ($medication->usage_instructions) {
            $parts[] = "Usage: {$medication->usage_instructions}";
        }

        if ($medication->side_effects) {
            $parts[] = "Side Effects: {$medication->side_effects}";
        }

        if ($medication->warnings) {
            $parts[] = "Warnings: {$medication->warnings}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * Chunk text into smaller pieces for embedding.
     */
    protected function chunkText(string $text, int $maxChunkSize = 500): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (strlen($currentChunk) + strlen($sentence) > $maxChunkSize && ! empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $sentence;
            } else {
                $currentChunk .= ' '.$sentence;
            }
        }

        if (! empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Delete embeddings for a document.
     */
    public function deleteDocumentEmbeddings(FaqDocument $document): bool
    {
        $chunks = $this->chunkText($document->content);
        $ids = [];

        foreach ($chunks as $index => $chunk) {
            $ids[] = "faq_{$document->id}_chunk_{$index}";
        }

        return $this->vectorStore->delete($ids, $document->tenant->pinecone_namespace);
    }
}
