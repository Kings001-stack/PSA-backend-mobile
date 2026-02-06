<?php

namespace App\Services;

use App\Models\Medication;
use App\Models\Tenant;

class RagPipelineService
{
    public function __construct(
        protected EntityExtractor $entityExtractor,
        protected VectorStoreService $vectorStore,
        protected GeminiService $geminiService
    ) {
    }

    public function process(string $message, Tenant $tenant, array $conversationHistory = []): array
    {
        $entities = $this->extractEntities($message, $tenant->id);
        $messageEmbedding = $this->geminiService->generateEmbedding($message);
        $relevantDocs = $this->vectorSearch($messageEmbedding, $tenant->pinecone_namespace);
        $context = $this->buildContext($relevantDocs, $entities, $conversationHistory);
        $prompt = $this->composePrompt($message, $context);

        return [
            'entities' => $entities,
            'relevant_documents' => $relevantDocs,
            'context' => $context,
            'prompt' => $prompt,
        ];
    }

    public function extractEntities(string $message, int $tenantId): array
    {
        return $this->entityExtractor->extractAll($message, $tenantId);
    }

    public function vectorSearch(array $embedding, string $namespace, int $limit = 5): array
    {
        $results = $this->vectorStore->query($embedding, $namespace, $limit);
        $documents = [];

        foreach ($results as $result) {
            if (isset($result['metadata'])) {
                $documents[] = [
                    'id' => $result['id'],
                    'score' => $result['score'] ?? 0,
                    'content' => $result['metadata']['content'] ?? '',
                    'title' => $result['metadata']['title'] ?? '',
                    'type' => $result['metadata']['type'] ?? 'unknown',
                ];
            }
        }

        return $documents;
    }

    public function buildContext(array $documents, array $entities, array $conversationHistory = []): array
    {
        $context = [
            'retrieved_documents' => [],
            'medications' => [],
            'conversation_history' => $conversationHistory,
        ];

        foreach ($documents as $doc) {
            if ($doc['score'] > 0.7) {
                $context['retrieved_documents'][] = $doc['content'];
            }
        }

        if (! empty($entities['medications'])) {
            foreach ($entities['medications'] as $med) {
                $medication = Medication::find($med['id']);
                if ($medication) {
                    $context['medications'][] = [
                        'name' => $medication->name,
                        'generic_name' => $medication->generic_name,
                        'description' => $medication->description,
                        'usage' => $medication->usage_instructions,
                        'warnings' => $medication->warnings,
                    ];
                }
            }
        }

        return $context;
    }

    public function composePrompt(string $userMessage, array $context): string
    {
        $prompt = "You are a helpful pharmacy assistant.\n\n";

        if (! empty($context['retrieved_documents'])) {
            $prompt .= "Relevant Information:\n";
            foreach ($context['retrieved_documents'] as $doc) {
                $prompt .= "- {$doc}\n";
            }
            $prompt .= "\n";
        }

        if (! empty($context['medications'])) {
            $prompt .= "Medication Information:\n";
            foreach ($context['medications'] as $med) {
                $prompt .= "Medication: {$med['name']}\n";
                if ($med['generic_name']) {
                    $prompt .= "Generic: {$med['generic_name']}\n";
                }
                $prompt .= "\n";
            }
        }

        $prompt .= "User Question: {$userMessage}\n\n";
        $prompt .= "Provide a helpful response based on the context above.";

        return $prompt;
    }
}
