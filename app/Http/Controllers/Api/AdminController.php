<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function dashboard(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // 1. Total distinct medications in the catalog
        $totalMedications = Medication::count();
        
        // 2. Aggregate Inventory Value across all stock
        $totalInventoryValue = DB::table('inventory')
            ->join('medications', 'inventory.medication_id', '=', 'medications.id')
            ->where('inventory.tenant_id', $tenantId)
            ->sum(DB::raw('inventory.quantity * medications.price'));

        // 3. Low Stock Analytics (Aggregated by medication)
        // Find medications where the combined quantity of all batches is low
        $lowStockMedications = Inventory::select('medication_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('medication_id')
            ->having('total_qty', '<', 20)
            ->with('medication:id,name')
            ->get();

        // 4. Batch Expiry Alerts
        $expiringSoonCount = Inventory::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();

        return response()->json([
            'stats' => [
                'total_medications' => $totalMedications,
                'low_stock_count' => $lowStockMedications->count(),
                'total_value' => round((float)$totalInventoryValue, 2),
                'expiring_soon' => $expiringSoonCount,
            ],
            'low_stock_alerts' => $lowStockMedications->map(fn($item) => [
                'id' => $item->medication_id,
                'quantity' => $item->total_qty,
                'medication' => $item->medication,
            ]),
        ]);
    }
}
