<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\RefillRequest;
use App\Models\Prescription;
use App\Models\Inventory;
use App\Models\RefillAuditLog;
use App\Services\RefillValidationService;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefillController extends Controller
{
    protected RefillValidationService $validationService;
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        RefillValidationService $validationService,
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->validationService = $validationService;
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Get the current user's refill requests.
     */
    public function index(Request $request)
    {
        $refills = RefillRequest::where('user_id', $request->user()->id)
            ->with([
                'medication:id,name',
                'prescription:id,prescription_number,refills_remaining',
                'reviewedBy:id,name'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($refills);
    }

    /**
     * Get user's active prescriptions for refill selection.
     */
    public function prescriptions(Request $request)
    {
        $prescriptions = Prescription::where('user_id', $request->user()->id)
            ->active()
            ->verified()
            ->with('medication:id,name,dosage_form,strength')
            ->get()
            ->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'prescription_number' => $prescription->prescription_number,
                    'medication' => $prescription->medication,
                    'dosage' => $prescription->dosage,
                    'frequency' => $prescription->frequency,
                    'refills_remaining' => $prescription->refills_remaining,
                    'expiration_date' => $prescription->expiration_date->format('Y-m-d'),
                    'is_controlled' => $prescription->is_controlled,
                    'can_refill' => $prescription->canRefill(),
                ];
            });

        return response()->json($prescriptions);
    }

    /**
     * Submit a new refill request (USER).
     */
    public function store(Request $request)
    {
        $request->validate([
            'medication_id' => 'required|exists:medications,id',
            'prescription_id' => 'nullable|exists:prescriptions,id',
            'quantity' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
            'preferred_pickup_time' => 'nullable|date|after:now',
            'is_urgent' => 'boolean',
            'prescription_document' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
        ]);

        $user = $request->user();

        // Validate the refill request
        $validation = $this->validationService->validateRefillRequest(
            $user->id,
            $request->medication_id,
            $request->prescription_id,
            $request->quantity
        );

        if (!$validation['valid']) {
            return response()->json([
                'message' => 'Refill request validation failed.',
                'errors' => $validation['errors'],
            ], 422);
        }

        // Handle prescription document upload
        $documentPath = null;
        if ($request->hasFile('prescription_document')) {
            $file = $request->file('prescription_document');
            $filename = time() . '_' . $user->id . '_prescription.' . $file->getClientOriginalExtension();
            $documentPath = $file->storeAs('prescription_documents', $filename, 'public');
        }

        // Create refill request
        $refill = RefillRequest::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'medication_id' => $request->medication_id,
            'prescription_id' => $request->prescription_id,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'prescription_document_path' => $documentPath,
            'preferred_pickup_time' => $request->preferred_pickup_time,
            'is_urgent' => $request->is_urgent ?? false,
            'status' => $validation['requires_manual_review'] ? 'under_review' : 'pending',
        ]);

        $refill->load('medication:id,name', 'prescription:id,prescription_number');

        // Send push notification to admins/pharmacists about new refill request
        $this->pushNotificationService->sendNewRefillRequestNotification(
            $user->name,
            $refill->medication->name,
            $refill->id
        );

        return response()->json([
            'message' => 'Your refill request has been submitted and is awaiting pharmacist review.',
            'refill' => $refill,
            'warnings' => $validation['warnings'] ?? [],
        ], 201);
    }

    /**
     * Cancel a refill request (USER).
     */
    public function cancel(Request $request, int $id)
    {
        $refill = RefillRequest::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if (!in_array($refill->status, ['pending', 'under_review'])) {
            return response()->json([
                'message' => 'Cannot cancel a refill request that has already been processed.',
            ], 422);
        }

        $refill->transitionTo('cancelled', 'Cancelled by user');

        return response()->json([
            'message' => 'Refill request cancelled successfully.',
        ]);
    }

    /**
     * Get all refill requests for pharmacist/admin.
     */
    public function pharmacistIndex(Request $request)
    {
        $status = $request->query('status');
        $urgent = $request->query('urgent');

        $query = RefillRequest::with([
            'user:id,name,email,phone',
            'medication:id,name,dosage_form,strength,requires_prescription',
            'prescription:id,prescription_number,refills_remaining,is_controlled,controlled_schedule',
            'reviewedBy:id,name'
        ])->orderBy('is_urgent', 'desc')
          ->orderBy('created_at', 'asc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($urgent === 'true') {
            $query->urgent();
        }

        $refills = $query->get();

        $stats = [
            'total' => RefillRequest::count(),
            'pending' => RefillRequest::where('status', 'pending')->whereNull('viewed_at')->count(),
            'under_review' => RefillRequest::where('status', 'under_review')->count(),
            'approved' => RefillRequest::where('status', 'approved')->count(),
            'ready_for_pickup' => RefillRequest::where('status', 'ready_for_pickup')->count(),
            'rejected' => RefillRequest::where('status', 'rejected')->count(),
            'collected' => RefillRequest::where('status', 'collected')->count(),
            'urgent' => RefillRequest::where('is_urgent', true)
                ->whereIn('status', ['pending', 'under_review'])
                ->whereNull('viewed_at')
                ->count(),
        ];

        return response()->json([
            'refills' => $refills,
            'stats' => $stats,
        ]);
    }

    /**
     * Get single refill request details with audit log (PHARMACIST).
     */
    public function show(Request $request, int $id)
    {
        $refill = RefillRequest::with([
            'user:id,name,email,phone',
            'medication',
            'prescription.medication',
            'reviewedBy:id,name',
            'collectedBy:id,name',
            'auditLogs.user:id,name'
        ])->findOrFail($id);

        // Mark as viewed by admin/pharmacist
        if (!$refill->viewed_at) {
            $refill->update([
                'viewed_at' => now(),
                'viewed_by' => $request->user()->id,
            ]);
        }

        // Check inventory availability
        $inventory = Inventory::where('medication_id', $refill->medication_id)->first();

        return response()->json([
            'refill' => $refill,
            'inventory_available' => $inventory ? $inventory->quantity : 0,
            'can_fulfill' => $inventory && $inventory->quantity >= $refill->quantity,
            'prescription_document_url' => $refill->prescription_document_path 
                ? asset('storage/' . $refill->prescription_document_path) 
                : null,
        ]);
    }

    /**
     * Approve refill request (PHARMACIST ONLY).
     */
    public function approve(Request $request, int $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $refill = RefillRequest::findOrFail($id);

        if (!in_array($refill->status, ['pending', 'under_review'])) {
            return response()->json([
                'message' => 'This refill request has already been processed.',
            ], 422);
        }

        // Validate approval
        $validation = $this->validationService->validateApproval($refill);
        if (!$validation['valid']) {
            return response()->json([
                'message' => 'Cannot approve refill request.',
                'errors' => $validation['errors'],
            ], 422);
        }

        DB::transaction(function () use ($refill, $request) {
            // Update refill status
            $refill->admin_notes = $request->admin_notes;
            $refill->save();
            
            $transitioned = $refill->transitionTo('approved', 'Approved by pharmacist');
            
            if (!$transitioned) {
                \Log::error('Failed to transition refill status', [
                    'refill_id' => $refill->id,
                    'current_status' => $refill->status,
                    'target_status' => 'approved'
                ]);
                throw new \Exception('Failed to update refill status');
            }

            // Decrement prescription refills if applicable
            if ($refill->prescription_id) {
                $refill->prescription->decrementRefills();
            }

            // Reserve inventory (optional - you can deduct now or on pickup)
            // Inventory::where('medication_id', $refill->medication_id)
            //     ->decrement('quantity', $refill->quantity);
        });

        $refill->load(['user:id,name,email', 'medication:id,name']);

        // Send email notification to user
        $this->notificationService->sendRefillApproved($refill);
        
        // Send push notification to user
        $this->pushNotificationService->sendRefillStatusUpdate(
            $refill->user,
            $refill->medication->name,
            'approved',
            $refill->id
        );

        return response()->json([
            'message' => 'Refill request approved successfully.',
            'refill' => $refill,
        ]);
    }

    /**
     * Reject refill request (PHARMACIST ONLY).
     */
    public function reject(Request $request, int $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $refill = RefillRequest::findOrFail($id);

        if (!in_array($refill->status, ['pending', 'under_review'])) {
            return response()->json([
                'message' => 'This refill request has already been processed.',
            ], 422);
        }

        $refill->rejection_reason = $request->rejection_reason;
        $refill->admin_notes = $request->admin_notes;
        $refill->save();
        
        $transitioned = $refill->transitionTo('rejected', 'Rejected by pharmacist: ' . $request->rejection_reason);
        
        if (!$transitioned) {
            \Log::error('Failed to transition refill status to rejected', [
                'refill_id' => $refill->id,
                'current_status' => $refill->status,
                'target_status' => 'rejected'
            ]);
            throw new \Exception('Failed to update refill status');
        }

        $refill->load(['user:id,name,email', 'medication:id,name']);

        // Send email notification to user
        $this->notificationService->sendRefillRejected($refill);
        
        // Send push notification to user
        $this->pushNotificationService->sendRefillStatusUpdate(
            $refill->user,
            $refill->medication->name,
            'rejected',
            $refill->id
        );

        return response()->json([
            'message' => 'Refill request rejected.',
            'refill' => $refill,
        ]);
    }

    /**
     * Mark refill as viewed by user
     */
    public function markAsViewed(Request $request, int $id)
    {
        $refill = RefillRequest::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $refill->update([
            'user_viewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Refill marked as viewed.',
        ]);
    }

    /**
     * Mark refill as ready for pickup (PHARMACIST).
     */
    public function markReady(Request $request, int $id)
    {
        $refill = RefillRequest::findOrFail($id);

        if ($refill->status !== 'approved') {
            return response()->json([
                'message' => 'Only approved refills can be marked as ready for pickup.',
            ], 422);
        }

        DB::transaction(function () use ($refill) {
            // Deduct inventory when marking ready
            $inventory = Inventory::where('medication_id', $refill->medication_id)->first();
            if ($inventory && $inventory->quantity >= $refill->quantity) {
                $inventory->decrement('quantity', $refill->quantity);
            }

            $refill->transitionTo('ready_for_pickup', 'Medication prepared and ready for pickup');
        });

        $refill->load(['user:id,name,email', 'medication:id,name']);

        // Send email notification to user
        $this->notificationService->sendReadyForPickup($refill);
        
        // Send push notification to user
        $this->pushNotificationService->sendRefillStatusUpdate(
            $refill->user,
            $refill->medication->name,
            'ready_for_pickup',
            $refill->id
        );

        return response()->json([
            'message' => 'Refill marked as ready for pickup.',
            'refill' => $refill,
        ]);
    }

    /**
     * Mark refill as collected (PHARMACIST).
     */
    public function markCollected(Request $request, int $id)
    {
        $refill = RefillRequest::findOrFail($id);

        if ($refill->status !== 'ready_for_pickup') {
            return response()->json([
                'message' => 'Only refills ready for pickup can be marked as collected.',
            ], 422);
        }

        $refill->transitionTo('collected', 'Medication collected by patient');

        $refill->load(['user:id,name,email', 'medication:id,name']);

        // Send push notification to user
        $this->pushNotificationService->sendRefillStatusUpdate(
            $refill->user,
            $refill->medication->name,
            'collected',
            $refill->id
        );

        return response()->json([
            'message' => 'Refill marked as collected.',
            'refill' => $refill,
        ]);
    }

    /**
     * Get medications for the refill dropdown.
     */
    public function medications(Request $request)
    {
        $medications = Medication::select('id', 'name', 'dosage_form', 'strength', 'requires_prescription')
            ->orderBy('name')
            ->get();

        return response()->json($medications);
    }

    /**
     * Get audit log for a refill request (PHARMACIST/ADMIN).
     */
    public function auditLog(int $id)
    {
        $logs = RefillAuditLog::where('refill_request_id', $id)
            ->with('user:id,name,role')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
