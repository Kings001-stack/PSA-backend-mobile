<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\InventoryImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Gate;

class InventoryImportController extends Controller
{
    /**
     * Import inventory from CSV.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        // 1. Authorization
        if (!Gate::allows('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // 2. Validation
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048', // 2MB max
        ]);

        try {
            $import = new InventoryImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'imported' => $import->importedCount,
                'failed' => $import->failedCount,
                'errors' => $import->errors
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $errors = [];
             
             foreach ($failures as $failure) {
                 $errors[] = [
                     'row' => $failure->row(),
                     'reason' => implode(', ', $failure->errors())
                 ];
             }

             return response()->json([
                 'success' => false,
                 'imported' => 0,
                 'failed' => count($failures),
                 'errors' => $errors
             ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
