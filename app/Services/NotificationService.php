<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefillRequest;
use App\Mail\RefillApprovedMail;
use App\Mail\RefillRejectedMail;
use App\Mail\ReadyForPickupMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send refill approved notification
     */
    public function sendRefillApproved(RefillRequest $refill): void
    {
        try {
            $user = $refill->user;
            $medication = $refill->medication;

            // Log notification
            Log::info('Sending refill approved notification', [
                'user_id' => $user->id,
                'refill_id' => $refill->id,
                'medication' => $medication->name,
            ]);

            // TODO: Send push notification
            // $this->sendPushNotification($user, [
            //     'title' => 'Refill Request Approved ✓',
            //     'body' => "Your refill for {$medication->name} has been approved. We'll notify you when it's ready for pickup.",
            //     'data' => [
            //         'type' => 'refill_approved',
            //         'refill_id' => $refill->id,
            //     ],
            // ]);

            // Send email notification
            Mail::to($user->email)->send(new RefillApprovedMail($refill));

            // TODO: Send SMS notification (optional)
            // if ($user->phone) {
            //     $this->sendSMS($user->phone, "Your refill for {$medication->name} has been approved.");
            // }

        } catch (\Exception $e) {
            Log::error('Failed to send refill approved notification', [
                'error' => $e->getMessage(),
                'refill_id' => $refill->id,
            ]);
        }
    }

    /**
     * Send refill rejected notification
     */
    public function sendRefillRejected(RefillRequest $refill): void
    {
        try {
            $user = $refill->user;
            $medication = $refill->medication;
            $reason = $refill->rejection_reason;

            Log::info('Sending refill rejected notification', [
                'user_id' => $user->id,
                'refill_id' => $refill->id,
                'medication' => $medication->name,
                'reason' => $reason,
            ]);

            // TODO: Send push notification
            // $this->sendPushNotification($user, [
            //     'title' => 'Refill Request Update',
            //     'body' => "Your refill for {$medication->name} requires attention. Please contact us.",
            //     'data' => [
            //         'type' => 'refill_rejected',
            //         'refill_id' => $refill->id,
            //         'reason' => $reason,
            //     ],
            // ]);

            // Send email notification
            Mail::to($user->email)->send(new RefillRejectedMail($refill));

        } catch (\Exception $e) {
            Log::error('Failed to send refill rejected notification', [
                'error' => $e->getMessage(),
                'refill_id' => $refill->id,
            ]);
        }
    }

    /**
     * Send ready for pickup notification
     */
    public function sendReadyForPickup(RefillRequest $refill): void
    {
        try {
            $user = $refill->user;
            $medication = $refill->medication;

            Log::info('Sending ready for pickup notification', [
                'user_id' => $user->id,
                'refill_id' => $refill->id,
                'medication' => $medication->name,
            ]);

            // TODO: Send push notification
            // $this->sendPushNotification($user, [
            //     'title' => 'Medication Ready for Pickup 📦',
            //     'body' => "Your {$medication->name} is ready! Visit us during business hours.",
            //     'data' => [
            //         'type' => 'ready_for_pickup',
            //         'refill_id' => $refill->id,
            //     ],
            // ]);

            // Send email notification
            Mail::to($user->email)->send(new ReadyForPickupMail($refill));

            // TODO: Send SMS notification
            // if ($user->phone) {
            //     $this->sendSMS($user->phone, "Your {$medication->name} is ready for pickup at PrimeChem Pharmacy.");
            // }

        } catch (\Exception $e) {
            Log::error('Failed to send ready for pickup notification', [
                'error' => $e->getMessage(),
                'refill_id' => $refill->id,
            ]);
        }
    }

    /**
     * Send pickup reminder notification (24h after ready)
     */
    public function sendPickupReminder(RefillRequest $refill): void
    {
        try {
            $user = $refill->user;
            $medication = $refill->medication;

            Log::info('Sending pickup reminder notification', [
                'user_id' => $user->id,
                'refill_id' => $refill->id,
                'medication' => $medication->name,
            ]);

            // TODO: Send push notification
            // $this->sendPushNotification($user, [
            //     'title' => 'Pickup Reminder',
            //     'body' => "Your medication is still waiting for pickup. Please collect it soon.",
            //     'data' => [
            //         'type' => 'pickup_reminder',
            //         'refill_id' => $refill->id,
            //     ],
            // ]);

        } catch (\Exception $e) {
            Log::error('Failed to send pickup reminder notification', [
                'error' => $e->getMessage(),
                'refill_id' => $refill->id,
            ]);
        }
    }

    /**
     * Send push notification (to be implemented with Expo)
     */
    private function sendPushNotification(User $user, array $notification): void
    {
        // TODO: Implement Expo Push Notification
        // This requires:
        // 1. User's Expo push token stored in database
        // 2. Expo SDK for PHP or HTTP client
        // 3. Send to Expo Push API
        
        Log::info('Push notification queued', [
            'user_id' => $user->id,
            'notification' => $notification,
        ]);
    }

    /**
     * Send SMS notification (to be implemented with Twilio)
     */
    private function sendSMS(string $phone, string $message): void
    {
        // TODO: Implement Twilio SMS
        // This requires:
        // 1. Twilio credentials in .env
        // 2. Twilio SDK
        // 3. Send SMS via Twilio API
        
        Log::info('SMS notification queued', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}
