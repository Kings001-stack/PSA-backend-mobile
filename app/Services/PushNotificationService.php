<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';
    private const MAX_BATCH_SIZE = 100;

    /**
     * Send push notification to a single user
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $tokens = $user->pushTokens()->active()->pluck('token')->toArray();
        
        if (empty($tokens)) {
            Log::info("No active push tokens found for user {$user->id}");
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): bool
    {
        $tokens = PushToken::whereIn('user_id', $userIds)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("No active push tokens found for users: " . implode(', ', $userIds));
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to users with specific roles
     */
    public function sendToRole(string $role, string $title, string $body, array $data = []): bool
    {
        $userIds = User::where('role', $role)->pluck('id')->toArray();
        return $this->sendToUsers($userIds, $title, $body, $data);
    }

    /**
     * Send push notification to specific tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (empty($tokens)) {
            return false;
        }

        // Remove invalid tokens (must start with ExponentPushToken)
        $validTokens = array_filter($tokens, function ($token) {
            return strpos($token, 'ExponentPushToken[') === 0;
        });

        if (empty($validTokens)) {
            Log::warning('No valid Expo push tokens found');
            return false;
        }

        // Split tokens into batches
        $batches = array_chunk($validTokens, self::MAX_BATCH_SIZE);
        $allSuccessful = true;

        foreach ($batches as $batch) {
            $messages = [];
            
            foreach ($batch as $token) {
                $messages[] = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                ];
            }

            $success = $this->sendBatch($messages);
            if (!$success) {
                $allSuccessful = false;
            }
        }

        return $allSuccessful;
    }

    /**
     * Send a batch of push notifications
     */
    private function sendBatch(array $messages): bool
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Accept-encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::EXPO_PUSH_URL, $messages);

            if ($response->successful()) {
                $responseData = $response->json();
                $this->handlePushResponse($responseData, $messages);
                return true;
            } else {
                Log::error('Push notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Push notification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Handle push notification response and update token status
     */
    private function handlePushResponse(array $responseData, array $messages): void
    {
        if (!isset($responseData['data'])) {
            return;
        }

        foreach ($responseData['data'] as $index => $result) {
            $token = $messages[$index]['to'] ?? null;
            
            if (!$token) {
                continue;
            }

            if (isset($result['status']) && $result['status'] === 'error') {
                $error = $result['details']['error'] ?? 'unknown';
                
                // Handle different error types
                if (in_array($error, ['DeviceNotRegistered', 'InvalidCredentials'])) {
                    // Deactivate invalid tokens
                    PushToken::where('token', $token)->update(['is_active' => false]);
                    Log::info("Deactivated invalid push token: {$token}");
                }
                
                Log::warning("Push notification error for token {$token}: {$error}");
            } else {
                // Mark successful tokens as used
                PushToken::where('token', $token)->update(['last_used_at' => now()]);
            }
        }
    }

    /**
     * Register a push token for a user
     */
    public function registerToken(User $user, string $token, string $platform, ?string $deviceName = null): PushToken
    {
        // Deactivate existing tokens for this user and device
        PushToken::where('user_id', $user->id)
            ->where('token', $token)
            ->update(['is_active' => false]);

        // Create new token
        return PushToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'platform' => $platform,
            'device_name' => $deviceName,
            'is_active' => true,
            'last_used_at' => now(),
        ]);
    }

    /**
     * Remove a push token
     */
    public function removeToken(string $token): bool
    {
        return PushToken::where('token', $token)->delete() > 0;
    }

    /**
     * Remove all tokens for a user
     */
    public function removeUserTokens(User $user): int
    {
        return PushToken::where('user_id', $user->id)->delete();
    }

    /**
     * Clean up inactive tokens (older than 30 days)
     */
    public function cleanupInactiveTokens(): int
    {
        return PushToken::where('is_active', false)
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Send refill status update notification
     */
    public function sendRefillStatusUpdate(User $user, string $medicationName, string $status, int $refillId): bool
    {
        $statusMessages = [
            'approved' => "Your refill request for {$medicationName} has been approved!",
            'rejected' => "Your refill request for {$medicationName} was rejected. Please contact the pharmacy.",
            'ready_for_pickup' => "Your {$medicationName} refill is ready for pickup!",
            'collected' => "Thank you for collecting your {$medicationName} refill.",
        ];

        $title = 'Refill Status Update';
        $body = $statusMessages[$status] ?? "Your refill request status has been updated.";
        
        $data = [
            'type' => 'refill_status_update',
            'refillId' => $refillId,
            'status' => $status,
            'medicationName' => $medicationName,
        ];

        return $this->sendToUser($user, $title, $body, $data);
    }

    /**
     * Send new refill request notification to admins/pharmacists
     */
    public function sendNewRefillRequestNotification(string $userName, string $medicationName, int $refillId): bool
    {
        $title = 'New Refill Request';
        $body = "{$userName} has requested a refill for {$medicationName}";
        
        $data = [
            'type' => 'new_refill_request',
            'refillId' => $refillId,
            'userName' => $userName,
            'medicationName' => $medicationName,
        ];

        // Send to both admins and pharmacists
        $adminSuccess = $this->sendToRole('admin', $title, $body, $data);
        $pharmacistSuccess = $this->sendToRole('pharmacist', $title, $body, $data);

        return $adminSuccess || $pharmacistSuccess;
    }
}