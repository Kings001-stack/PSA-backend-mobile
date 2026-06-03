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
        
        // Calculate stats immediately (not lazy)
        $stats = [
            'totalMedications' => Medication::count(),
            'totalInventory' => Inventory::sum('quantity') ?? 0,
            'lowStockAlerts' => Inventory::whereColumn('quantity', '<', 'reorder_level')->count(),
            'expiringSoon' => Inventory::whereBetween('expiry_date', [now(), now()->addDays(30)])->count(),
        ];
        
        // Get alert items immediately
        $alertItems = Inventory::with('medication:id,name')
            ->select('id', 'medication_id', 'quantity', 'reorder_level', 'expiry_date', 'batch_number', 'updated_at')
            ->where(function ($query) {
                $query->whereColumn('quantity', '<', 'reorder_level')
                    ->orWhereBetween('expiry_date', [now(), now()->addDays(30)]);
            })
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'medication_name' => $item->medication?->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'reorder_level' => $item->reorder_level ?? 10,
                'expiry_date' => $item->expiry_date,
                'batch_number' => $item->batch_number,
                'is_low_stock' => $item->quantity < ($item->reorder_level ?? 10),
                'is_expiring' => $item->expiry_date && $item->expiry_date <= now()->addDays(30),
                'updated_at' => $item->updated_at?->diffForHumans(),
            ]);
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'alertItems' => $alertItems,
        ]);
    }
}

