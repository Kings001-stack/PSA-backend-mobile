<?php

namespace App\Services;

use App\Models\Medication;

class EntityExtractor
{
    /**
     * Extract medication names from user message.
     */
    public function extractMedications(string $message, int $tenantId): array
    {
        $message = strtolower($message);
        $medications = [];

        // Get all medications for this tenant
        $allMedications = Medication::where('tenant_id', $tenantId)
            ->get(['id', 'name', 'generic_name']);

        foreach ($allMedications as $medication) {
            $name = strtolower($medication->name);
            $genericName = strtolower($medication->generic_name ?? '');

            // Check if medication name appears in message
            if (str_contains($message, $name)) {
                $medications[] = [
                    'id' => $medication->id,
                    'name' => $medication->name,
                    'type' => 'brand_name',
                    'confidence' => 0.9,
                ];
            } elseif ($genericName && str_contains($message, $genericName)) {
                $medications[] = [
                    'id' => $medication->id,
                    'name' => $medication->name,
                    'generic_name' => $medication->generic_name,
                    'type' => 'generic_name',
                    'confidence' => 0.85,
                ];
            }
        }

        return array_unique($medications, SORT_REGULAR);
    }

    /**
     * Extract dosage information from message.
     */
    public function extractDosage(string $message): ?array
    {
        $patterns = [
            '/(\d+)\s*(mg|g|ml|mcg|units?)/i',
            '/(\d+)\s*(milligrams?|grams?|milliliters?|micrograms?)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return [
                    'amount' => $matches[1],
                    'unit' => $matches[2],
                    'raw' => $matches[0],
                ];
            }
        }

        return null;
    }

    /**
     * Extract medical conditions from message.
     */
    public function extractConditions(string $message): array
    {
        $commonConditions = [
            'headache', 'fever', 'cold', 'flu', 'cough', 'pain',
            'allergy', 'allergies', 'diabetes', 'hypertension',
            'asthma', 'arthritis', 'infection', 'inflammation',
        ];

        $message = strtolower($message);
        $found = [];

        foreach ($commonConditions as $condition) {
            if (str_contains($message, $condition)) {
                $found[] = $condition;
            }
        }

        return $found;
    }

    /**
     * Extract all entities from message.
     */
    public function extractAll(string $message, int $tenantId): array
    {
        return [
            'medications' => $this->extractMedications($message, $tenantId),
            'dosage' => $this->extractDosage($message),
            'conditions' => $this->extractConditions($message),
        ];
    }
}
