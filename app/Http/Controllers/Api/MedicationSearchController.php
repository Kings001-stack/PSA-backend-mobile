<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MedicationSearchController extends Controller
{
    /**
     * Search for medication availability.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query');
        
        $medications = Medication::query()
            ->with(['inventory'])
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('generic_name', 'like', "%{$query}%");
                });
            })
            ->get()
            ->map(function (Medication $medication) {
                $totalQuantity = $medication->inventory->sum('quantity');
                
                // Availability logic:
                // Quantity > threshold (10) → available
                // Quantity low (1-10) → limited
                // Quantity = 0 → out_of_stock
                $availability = 'available';
                if ($totalQuantity <= 0) {
                    $availability = 'out_of_stock';
                } elseif ($totalQuantity <= 10) {
                    $availability = 'limited';
                }
                
                return [
                    'id' => $medication->id,
                    'name' => $medication->name,
                    'dosage' => $medication->strength,
                    'form' => $medication->dosage_form,
                    'availability' => $availability,
                ];
            });
            
        return response()->json($medications);
    }
}
