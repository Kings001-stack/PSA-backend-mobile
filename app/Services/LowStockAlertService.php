<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\LowStockAlert;
use App\Models\Medication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LowStockAlertService
{
    /**
     * Check all inventory items for low stock and create alerts
     */
    public function checkLowStockLevels(): array
    {
        $alerts = [];
        $inventoryItems = Inventory::with('medication')->get();

        foreach ($inventoryItems as $item) {
            // Check for low stock
            if ($item->quantity <= $item->reorder_level && $item->quantity > 0) {
                $alert = $this->createAlert($item, 'low_stock');
                if ($alert) {
                    $alerts[] = $alert;
                }
            }

            // Check for out of stock
            if ($item->quantity <= 0) {
                $alert = $this->createAlert($item, 'out_of_stock');
                if ($alert) {
                    $alerts[] = $alert;
                }
            }

            // Check for expiring soon (within 30 days)
            if ($item->expiry_date) {
                $daysUntilExpiry = Carbon::now()->diffInDays($item->expiry_date, false);
                
                if ($daysUntilExpiry <= 30 && $daysUntilExpiry > 0) {
                    $alert = $this->createAlert($item, 'expiring_soon');
                    if ($alert) {
                        $alerts[] = $alert;
                    }
                }

                // Check for expired
                if ($daysUntilExpiry <= 0) {
                    $alert = $this->createAlert($item, 'expired');
                    if ($alert) {
                        $alerts[] = $alert;
                    }
                }
            }
        }

        return $alerts;
    }

    /**
     * Create an alert if it doesn't already exist
     */
    private function createAlert(Inventory $item, string $alertType): ?LowStockAlert
    {
        // Check if unresolved alert already exists
        $existingAlert = LowStockAlert::where('inventory_id', $item->id)
            ->where('alert_type', $alertType)
            ->where('is_resolved', false)
            ->first();

        if ($existingAlert) {
            return null; // Alert already exists
        }

        // Create new alert
        $alert = LowStockAlert::create([
            'tenant_id' => $item->tenant_id,
            'inventory_id' => $item->id,
            'medication_id' => $item->medication_id,
            'current_quantity' => $item->quantity,
            'reorder_level' => $item->reorder_level,
            'alert_type' => $alertType,
            'is_resolved' => false,
        ]);

        // Mark inventory as alerted
        if ($alertType === 'low_stock' || $alertType === 'out_of_stock') {
            $item->update(['low_stock_alert_sent' => true]);
        }

        Log::info("Low stock alert created", [
            'alert_id' => $alert->id,
            'medication' => $item->medication->name,
            'type' => $alertType,
            'quantity' => $item->quantity,
        ]);

        return $alert;
    }

    /**
     * Resolve an alert
     */
    public function resolveAlert(int $alertId, int $userId): bool
    {
        $alert = LowStockAlert::findOrFail($alertId);

        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $userId,
        ]);

        // Reset the alert flag on inventory if it's a stock alert
        if (in_array($alert->alert_type, ['low_stock', 'out_of_stock'])) {
            $alert->inventory->update(['low_stock_alert_sent' => false]);
        }

        return true;
    }

    /**
     * Get all unresolved alerts
     */
    public function getUnresolvedAlerts(?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = LowStockAlert::with(['medication', 'inventory'])
            ->unresolved()
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->ofType($type);
        }

        return $query->get();
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(): array
    {
        return [
            'total_unresolved' => LowStockAlert::unresolved()->count(),
            'low_stock' => LowStockAlert::unresolved()->ofType('low_stock')->count(),
            'out_of_stock' => LowStockAlert::unresolved()->ofType('out_of_stock')->count(),
            'expiring_soon' => LowStockAlert::unresolved()->ofType('expiring_soon')->count(),
            'expired' => LowStockAlert::unresolved()->ofType('expired')->count(),
        ];
    }

    /**
     * Auto-resolve alerts when inventory is restocked
     */
    public function autoResolveOnRestock(Inventory $item): void
    {
        // Resolve low stock and out of stock alerts if quantity is now above reorder level
        if ($item->quantity > $item->reorder_level) {
            LowStockAlert::where('inventory_id', $item->id)
                ->whereIn('alert_type', ['low_stock', 'out_of_stock'])
                ->where('is_resolved', false)
                ->update([
                    'is_resolved' => true,
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                ]);

            $item->update(['low_stock_alert_sent' => false]);
        }
    }
}
