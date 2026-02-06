<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateEmbeddingsJob;
use App\Models\FaqDocument;
use App\Models\Medication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    /**
     * Upload medications CSV.
     */
    public function uploadMedications(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $user = $request->user();
        $file = $request->file('file');
        $imported = 0;

        DB::beginTransaction();

        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);

                $medication = Medication::create([
                    'tenant_id' => $user->tenant_id,
                    'name' => $data['name'] ?? '',
                    'generic_name' => $data['generic_name'] ?? null,
                    'description' => $data['description'] ?? null,
                    'dosage_form' => $data['dosage_form'] ?? null,
                    'strength' => $data['strength'] ?? null,
                    'usage_instructions' => $data['usage_instructions'] ?? null,
                    'side_effects' => $data['side_effects'] ?? null,
                    'warnings' => $data['warnings'] ?? null,
                    'price' => $data['price'] ?? null,
                    'requires_prescription' => ($data['requires_prescription'] ?? 'no') === 'yes',
                ]);

                GenerateEmbeddingsJob::dispatch('medication', $medication->id);
                $imported++;
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} medications",
                'imported' => $imported,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload FAQ document.
     */
    public function uploadFaq(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        $document = FaqDocument::create([
            'tenant_id' => $user->tenant_id,
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'category' => $request->input('category'),
        ]);

        GenerateEmbeddingsJob::dispatch('faq', $document->id);

        return response()->json([
            'success' => true,
            'message' => 'FAQ document uploaded successfully',
            'document' => $document,
        ]);
    }

    /**
     * Get upload status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'medications' => Medication::where('tenant_id', $user->tenant_id)->count(),
            'faq_documents' => FaqDocument::where('tenant_id', $user->tenant_id)->count(),
            'indexed_documents' => FaqDocument::where('tenant_id', $user->tenant_id)
                ->where('is_indexed', true)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Upload inventory CSV.
     */
    public function uploadInventory(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $user = $request->user();
        $file = $request->file('file');
        $imported = 0;

        DB::beginTransaction();

        try {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);

                $medication = Medication::where('tenant_id', $user->tenant_id)
                    ->where('name', $data['medication_name'])
                    ->first();

                if (! $medication) {
                    continue;
                }

                \App\Models\Inventory::create([
                    'tenant_id' => $user->tenant_id,
                    'medication_id' => $medication->id,
                    'quantity' => (int) ($data['quantity'] ?? 0),
                    'reorder_level' => (int) ($data['reorder_level'] ?? 10),
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'batch_number' => $data['batch_number'] ?? null,
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} inventory items",
                'imported' => $imported,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download CSV templates.
     */
    public function downloadTemplate(Request $request, string $type): JsonResponse
    {
        $csvParser = new \App\Services\CsvParser;

        $content = match ($type) {
            'medications' => $csvParser->generateMedicationTemplate(),
            'inventory' => $csvParser->generateInventoryTemplate(),
            default => null,
        };

        if (! $content) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid template type',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'content' => $content,
            'filename' => "{$type}_template.csv",
        ]);
    }
}
