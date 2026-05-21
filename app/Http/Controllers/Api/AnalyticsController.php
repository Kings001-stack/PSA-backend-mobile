<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Inventory;
use App\Models\LowStockAlert;
use App\Models\Medication;
use App\Models\Message;
use App\Models\RefillRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive analytics data for admin dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Date range filter (default: last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->startOfDay());
        $endDate = $request->input('end_date', now()->endOfDay());

        return response()->json([
            'overview' => $this->getOverviewStats($tenantId),
            'conversations' => $this->getConversationStats($tenantId, $startDate, $endDate),
            'inventory' => $this->getInventoryStats($tenantId),
            'refills' => $this->getRefillStats($tenantId, $startDate, $endDate),
            'medications' => $this->getMedicationStats($tenantId),
            'users' => $this->getUserStats($tenantId, $startDate, $endDate),
            'trends' => $this->getTrendData($tenantId, $startDate, $endDate),
            'alerts' => $this->getAlertStats($tenantId),
        ]);
    }

    /**
     * Overview statistics.
     */
    protected function getOverviewStats($tenantId): array
    {
        return [
            'total_medications' => Medication::where('tenant_id', $tenantId)->count(),
            'total_inventory_items' => Inventory::where('tenant_id', $tenantId)->sum('quantity'),
            'low_stock_count' => Inventory::where('tenant_id', $tenantId)
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->count(),
            'total_conversations' => Conversation::where('tenant_id', $tenantId)->count(),
            'active_conversations' => Conversation::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count(),
            'escalated_conversations' => Conversation::where('tenant_id', $tenantId)
                ->where('requires_escalation', true)
                ->count(),
            'total_users' => User::where('tenant_id', $tenantId)->count(),
            'pending_refills' => RefillRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count(),
            'total_messages' => Message::whereHas('conversation', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })->count(),
        ];
    }

    /**
     * Conversation statistics.
     */
    protected function getConversationStats($tenantId, $startDate, $endDate): array
    {
        $conversations = Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $byStatus = $conversations->groupBy('status')->map->count();
        $escalationReasons = $conversations->where('requires_escalation', true)
            ->groupBy('escalation_reason')
            ->map->count();

        // Daily conversation count
        $dailyConversations = Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total' => $conversations->count(),
            'by_status' => $byStatus,
            'escalation_rate' => $conversations->count() > 0 
                ? round(($conversations->where('requires_escalation', true)->count() / $conversations->count()) * 100, 2)
                : 0,
            'escalation_reasons' => $escalationReasons,
            'daily_conversations' => $dailyConversations,
            'avg_messages_per_conversation' => $conversations->count() > 0
                ? round($conversations->sum(fn($c) => $c->messages()->count()) / $conversations->count(), 2)
                : 0,
        ];
    }

    /**
     * Inventory statistics.
     */
    protected function getInventoryStats($tenantId): array
    {
        $inventory = Inventory::where('tenant_id', $tenantId)->with('medication')->get();

        $totalValue = $inventory->sum(function ($item) {
            // Use unit_price from inventory if available, otherwise fall back to medication price
            $price = $item->unit_price ?? $item->medication->price ?? 0;
            return $item->quantity * $price;
        });

        $lowStock = $inventory->filter(fn($item) => $item->quantity <= $item->reorder_level);
        $expiringSoon = $inventory->filter(function ($item) {
            return $item->expiry_date && $item->expiry_date->lte(now()->addDays(30));
        });

        // Top medications by quantity
        $topMedications = $inventory->sortByDesc('quantity')->take(10)->map(function ($item) {
            $price = $item->unit_price ?? $item->medication->price ?? 0;
            return [
                'name' => $item->medication->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'value' => $item->quantity * $price,
            ];
        })->values();

        return [
            'total_items' => $inventory->sum('quantity'),
            'total_value' => round($totalValue, 2),
            'low_stock_items' => $lowStock->count(),
            'expiring_soon' => $expiringSoon->count(),
            'top_medications' => $topMedications,
            'stock_distribution' => [
                'in_stock' => $inventory->filter(fn($i) => $i->quantity > $i->reorder_level)->count(),
                'low_stock' => $lowStock->count(),
                'out_of_stock' => $inventory->filter(fn($i) => $i->quantity == 0)->count(),
            ],
        ];
    }

    /**
     * Refill request statistics.
     */
    protected function getRefillStats($tenantId, $startDate, $endDate): array
    {
        $refills = RefillRequest::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $byStatus = $refills->groupBy('status')->map->count();

        // Daily refill requests
        $dailyRefills = RefillRequest::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top requested medications
        $topRequested = RefillRequest::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('medication_id', DB::raw('count(*) as count'))
            ->groupBy('medication_id')
            ->orderByDesc('count')
            ->limit(10)
            ->with('medication')
            ->get()
            ->map(fn($r) => [
                'name' => $r->medication->name ?? 'Unknown',
                'count' => $r->count,
            ]);

        return [
            'total' => $refills->count(),
            'by_status' => $byStatus,
            'daily_refills' => $dailyRefills,
            'top_requested' => $topRequested,
            'approval_rate' => $refills->count() > 0
                ? round(($refills->where('status', 'approved')->count() / $refills->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Medication statistics.
     */
    protected function getMedicationStats($tenantId): array
    {
        $medications = Medication::where('tenant_id', $tenantId)->get();

        return [
            'total' => $medications->count(),
            'requires_prescription' => $medications->where('requires_prescription', true)->count(),
            'over_the_counter' => $medications->where('requires_prescription', false)->count(),
            'by_dosage_form' => $medications->groupBy('dosage_form')->map->count(),
            'avg_price' => round($medications->avg('price'), 2),
        ];
    }

    /**
     * User statistics.
     */
    protected function getUserStats($tenantId, $startDate, $endDate): array
    {
        $users = User::where('tenant_id', $tenantId)->get();
        $newUsers = User::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Daily new users
        $dailyUsers = User::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total' => $users->count(),
            'new_users' => $newUsers->count(),
            'by_role' => $users->groupBy('role')->map->count(),
            'daily_new_users' => $dailyUsers,
        ];
    }

    /**
     * Trend data for charts.
     */
    protected function getTrendData($tenantId, $startDate, $endDate): array
    {
        // Last 7 days trend
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $last7Days[] = [
                'date' => $date,
                'conversations' => Conversation::where('tenant_id', $tenantId)
                    ->whereDate('created_at', $date)
                    ->count(),
                'messages' => Message::whereHas('conversation', function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                })->whereDate('created_at', $date)->count(),
                'refills' => RefillRequest::where('tenant_id', $tenantId)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return [
            'last_7_days' => $last7Days,
        ];
    }

    /**
     * Alert statistics.
     */
    protected function getAlertStats($tenantId): array
    {
        $alerts = LowStockAlert::where('tenant_id', $tenantId)->get();
        $unresolved = $alerts->where('is_resolved', false);

        return [
            'total_alerts' => $alerts->count(),
            'unresolved' => $unresolved->count(),
            'by_type' => $unresolved->groupBy('alert_type')->map->count(),
            'resolved_today' => $alerts->where('is_resolved', true)
                ->where('resolved_at', '>=', now()->startOfDay())
                ->count(),
        ];
    }
}
