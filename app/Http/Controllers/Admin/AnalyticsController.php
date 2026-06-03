<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Medication;
use App\Models\RefillRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Total Medications & Stock
        $totalMedications = Medication::count();
        $totalInventoryUnits = Inventory::sum('quantity') ?? 0;
        
        // Stock Health Analytics
        $healthyStock = Inventory::whereColumn('quantity', '>=', 'reorder_level')->count();
        $lowStock = Inventory::whereColumn('quantity', '<', 'reorder_level')->count();
        
        // Expiry Analytics
        $secureExpiry = Inventory::where(function($query) {
            $query->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now()->addDays(30));
        })->count();
        
        $expiringSoon = Inventory::whereBetween('expiry_date', [now(), now()->addDays(30)])->count();
        $expired = Inventory::where('expiry_date', '<', now())->count();
        
        // Medication Categories Distribution
        $categoryDistribution = Medication::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
        
        // Top 10 Medications by Quantity
        $topMedications = Inventory::with('medication:id,name')
            ->select('medication_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('medication_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'name' => $item->medication->name ?? 'Unknown',
                'quantity' => $item->total_quantity,
            ]);
        
        // Refill Request Analytics
        $refillStats = [
            'pending' => RefillRequest::where('status', 'pending')->count(),
            'approved' => RefillRequest::where('status', 'approved')->count(),
            'rejected' => RefillRequest::where('status', 'rejected')->count(),
            'completed' => RefillRequest::where('status', 'completed')->count(),
        ];
        
        // User Role Distribution
        $usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();
        
        // Monthly Trends (Last 6 Months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyTrends[] = [
                'month' => $date->format('M Y'),
                'refills' => RefillRequest::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'low_stock_alerts' => Inventory::whereYear('updated_at', $date->year)
                    ->whereMonth('updated_at', $date->month)
                    ->whereColumn('quantity', '<', 'reorder_level')
                    ->count(),
            ];
        }
        
        // Critical Alerts Summary
        $criticalAlerts = [
            'out_of_stock' => Inventory::where('quantity', 0)->count(),
            'critical_stock' => Inventory::whereColumn('quantity', '<', DB::raw('reorder_level / 2'))->count(),
            'expired_batches' => $expired,
            'expiring_this_week' => Inventory::whereBetween('expiry_date', [now(), now()->addDays(7)])->count(),
        ];
        
        return Inertia::render('Admin/Analytics', [
            'overview' => [
                'totalMedications' => $totalMedications,
                'totalInventoryUnits' => $totalInventoryUnits,
                'healthyStock' => $healthyStock,
                'lowStock' => $lowStock,
                'secureExpiry' => $secureExpiry,
                'expiringSoon' => $expiringSoon,
                'expired' => $expired,
            ],
            'categoryDistribution' => $categoryDistribution,
            'topMedications' => $topMedications,
            'refillStats' => $refillStats,
            'usersByRole' => $usersByRole,
            'monthlyTrends' => $monthlyTrends,
            'criticalAlerts' => $criticalAlerts,
        ]);
    }
}
