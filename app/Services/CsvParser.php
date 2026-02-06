<?php

namespace App\Services;

class CsvParser
{
    /**
     * Parse CSV file and return array of data.
     */
    public function parse(string $filePath, bool $hasHeader = true): array
    {
        if (! file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $data = [];
        $handle = fopen($filePath, 'r');

        if (! $handle) {
            throw new \Exception("Unable to open file: {$filePath}");
        }

        $header = $hasHeader ? fgetcsv($handle) : null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($header) {
                $data[] = array_combine($header, $row);
            } else {
                $data[] = $row;
            }
        }

        fclose($handle);

        return $data;
    }

    /**
     * Validate CSV data against required fields.
     */
    public function validate(array $data, array $requiredFields): array
    {
        $errors = [];

        foreach ($data as $index => $row) {
            foreach ($requiredFields as $field) {
                if (! isset($row[$field]) || empty(trim($row[$field]))) {
                    $errors[] = "Row {$index}: Missing required field '{$field}'";
                }
            }
        }

        return $errors;
    }

    /**
     * Parse medications CSV.
     */
    public function parseMedications(string $filePath): array
    {
        $data = $this->parse($filePath);

        $requiredFields = ['name'];
        $errors = $this->validate($data, $requiredFields);

        if (! empty($errors)) {
            throw new \Exception('Validation errors: '.implode(', ', $errors));
        }

        return array_map(function ($row) {
            return [
                'name' => trim($row['name']),
                'generic_name' => $row['generic_name'] ?? null,
                'description' => $row['description'] ?? null,
                'dosage_form' => $row['dosage_form'] ?? null,
                'strength' => $row['strength'] ?? null,
                'usage_instructions' => $row['usage_instructions'] ?? null,
                'side_effects' => $row['side_effects'] ?? null,
                'warnings' => $row['warnings'] ?? null,
                'price' => isset($row['price']) ? (float) $row['price'] : null,
                'requires_prescription' => ($row['requires_prescription'] ?? 'no') === 'yes',
            ];
        }, $data);
    }

    /**
     * Parse inventory CSV.
     */
    public function parseInventory(string $filePath): array
    {
        $data = $this->parse($filePath);

        $requiredFields = ['medication_name', 'quantity'];
        $errors = $this->validate($data, $requiredFields);

        if (! empty($errors)) {
            throw new \Exception('Validation errors: '.implode(', ', $errors));
        }

        return array_map(function ($row) {
            return [
                'medication_name' => trim($row['medication_name']),
                'quantity' => (int) $row['quantity'],
                'reorder_level' => isset($row['reorder_level']) ? (int) $row['reorder_level'] : 10,
                'expiry_date' => $row['expiry_date'] ?? null,
                'batch_number' => $row['batch_number'] ?? null,
            ];
        }, $data);
    }

    /**
     * Generate sample CSV template.
     */
    public function generateMedicationTemplate(): string
    {
        $headers = [
            'name',
            'generic_name',
            'description',
            'dosage_form',
            'strength',
            'usage_instructions',
            'side_effects',
            'warnings',
            'price',
            'requires_prescription',
        ];

        $sample = [
            'Aspirin',
            'Acetylsalicylic Acid',
            'Pain reliever and fever reducer',
            'Tablet',
            '325mg',
            'Take 1-2 tablets every 4-6 hours as needed',
            'Stomach upset, heartburn, nausea',
            'Do not use if allergic to aspirin',
            '5.99',
            'no',
        ];

        return implode(',', $headers)."\n".implode(',', $sample);
    }

    /**
     * Generate inventory CSV template.
     */
    public function generateInventoryTemplate(): string
    {
        $headers = [
            'medication_name',
            'quantity',
            'reorder_level',
            'expiry_date',
            'batch_number',
        ];

        $sample = [
            'Aspirin',
            '100',
            '20',
            '2025-12-31',
            'BATCH001',
        ];

        return implode(',', $headers)."\n".implode(',', $sample);
    }
}
