<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Medication;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class InventoryImport implements ToCollection, WithHeadingRow, WithValidation
{
    public array $errors = [];
    public int $importedCount = 0;
    public int $failedCount = 0;

    public function collection(Collection $rows)
    {
        $tenantId = auth()->user()->tenant_id;

        foreach ($rows as $index => $row) {
            try {
                // Determine row number for error reporting (heading row is 1, data starts at 2)
                $rowNumber = $index + 2;

                // 1. Find or create Medication
                // Mapping: brand -> name, name -> generic_name
                $medication = Medication::where('tenant_id', $tenantId)
                    ->where('name', $row['brand'])
                    ->where('strength', $row['dosage'])
                    ->first();

                if (!$medication) {
                    $medication = Medication::create([
                        'tenant_id' => $tenantId,
                        'name' => $row['brand'],
                        'generic_name' => $row['name'],
                        'strength' => $row['dosage'],
                        'dosage_form' => $row['form'],
                    ]);
                }

                // 2. Find or create Inventory record (match by medication and expiry/batch is not specified in requirement but usually part of inventory)
                // The requirement says: "If medication exists (match by name + dosage): Update inventory quantity"
                // Usually inventory is tracked by batches, but I'll follow the requirement to match by medication.
                $inventory = Inventory::where('tenant_id', $tenantId)
                    ->where('medication_id', $medication->id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity', (int)$row['quantity']);
                    if (isset($row['expiry_date'])) {
                        $inventory->expiry_date = $row['expiry_date'];
                        $inventory->save();
                    }
                } else {
                    Inventory::create([
                        'tenant_id' => $tenantId,
                        'medication_id' => $medication->id,
                        'quantity' => (int)$row['quantity'],
                        'expiry_date' => $row['expiry_date'] ?? null,
                        'batch_number' => 'IMPORT-' . date('Ymd'),
                    ]);
                }

                $this->importedCount++;

            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = [
                    'row' => $rowNumber ?? ($index + 2),
                    'reason' => $e->getMessage()
                ];
                Log::error("Import failed at row " . ($index + 2) . ": " . $e->getMessage());
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'brand' => 'required|string',
            'dosage' => 'required|string',
            'form' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
        ];
    }
}
