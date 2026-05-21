<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushTokenController extends Controller
{
    protected PushNotificationService $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Register a push token for the authenticated user
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'push_token' => 'required|string',
            'platform' => 'required|in:ios,android',
            'device_name' => 'nullable|string|max:255',
        ]);

        try {
            $token = $this->pushService->registerToken(
                $request->user(),
                $request->push_token,
                $request->platform,
                $request->device_name
            );

            return response()->json([
                'message' => 'Push token registered successfully',
                'token_id' => $token->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to register push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a push token
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'push_token' => 'required|string',
        ]);

        try {
            $removed = $this->pushService->removeToken($request->push_token);

            if ($removed) {
                return response()->json([
                    'message' => 'Push token removed successfully',
                ]);
            } else {
                return response()->json([
                    'message' => 'Push token not found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove push token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's push tokens
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->pushTokens()
            ->active()
            ->select(['id', 'platform', 'device_name', 'last_used_at', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'tokens' => $tokens,
        ]);
    }

    /**
     * Send a test notification to the user
     */
    public function sendTest(Request $request): JsonResponse
    {
        try {
            $success = $this->pushService->sendToUser(
                $request->user(),
                'Test Notification',
                'This is a test notification from PrimeChem Pharmacy!',
                ['type' => 'test']
            );

            if ($success) {
                return response()->json([
                    'message' => 'Test notification sent successfully',
                ]);
            } else {
                return response()->json([
                    'message' => 'Failed to send test notification. No active tokens found.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}