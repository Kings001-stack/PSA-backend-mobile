<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LowStockAlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    protected LowStockAlertService $alertService;

    public function __construct(LowStockAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Get all unresolved alerts
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $alerts = $this->alertService->getUnresolvedAlerts($type);

        return response()->json([
            'alerts' => $alerts,
            'statistics' => $this->alertService->getAlertStatistics(),
        ]);
    }

    /**
     * Get alert statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->alertService->getAlertStatistics();

        return response()->json($stats);
    }

    /**
     * Resolve an alert
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        try {
            $this->alertService->resolveAlert($id, $request->user()->id);

            return response()->json([
                'message' => 'Alert resolved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resolve alert',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manually trigger alert check (for testing/admin use)
     */
    public function checkAlerts(): JsonResponse
    {
        try {
            $alerts = $this->alertService->checkLowStockLevels();

            return response()->json([
                'message' => 'Alert check completed',
                'new_alerts' => count($alerts),
                'alerts' => $alerts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to check alerts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
