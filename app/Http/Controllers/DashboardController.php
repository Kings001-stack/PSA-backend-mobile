<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get pharmacy stats
        $totalMedications = Medication::count();
        $totalInventory = Inventory::sum('quantity');
        $lowStockCount = Inventory::whereColumn('quantity', '<', 'reorder_level')->count();
        $expiringSoonCount = Inventory::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->count();

        $stats = [
            'totalMedications' => $totalMedications,
            'totalInventory' => $totalInventory,
            'lowStockAlerts' => $lowStockCount,
            'expiringSoon' => $expiringSoonCount,
        ];

        // Get low stock items for the table
        $lowStockItems = Inventory::with('medication')
            ->whereColumn('quantity', '<', 'reorder_level')
            ->orWhere(function ($query) {
                $query->where('expiry_date', '<=', now()->addDays(30))
                    ->where('expiry_date', '>', now());
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'medication_name' => $item->medication?->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'reorder_level' => $item->reorder_level ?? 10,
                    'expiry_date' => $item->expiry_date,
                    'batch_number' => $item->batch_number,
                    'is_low_stock' => $item->quantity < ($item->reorder_level ?? 10),
                    'is_expiring' => $item->expiry_date && $item->expiry_date <= now()->addDays(30),
                    'updated_at' => $item->updated_at,
                ];
            });

        return Inertia::render('Dashboard', [
            'user' => $user,
            'tenant' => $user->tenant,
            'stats' => $stats,
            'alertItems' => $lowStockItems,
        ]);
    }
}

