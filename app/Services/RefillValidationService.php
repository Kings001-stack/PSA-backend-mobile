<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Medication;
use App\Models\Inventory;
use App\Models\RefillRequest;
use Carbon\Carbon;

class RefillValidationService
{
    /**
     * Validate a refill request before submission.
     *
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public function validateRefillRequest(
        int $userId,
        int $medicationId,
        ?int $prescriptionId,
        int $quantity
    ): array {
        $errors = [];
        $warnings = [];
        $requiresManualReview = false;

        // Check for existing pending request
        $existingPending = RefillRequest::where('user_id', $userId)
            ->where('medication_id', $medicationId)
            ->whereIn('status', ['pending', 'under_review', 'approved'])
            ->first();

        if ($existingPending) {
            $errors[] = 'You already have an active refill request for this medication.';
        }

        // Get medication details
        $medication = Medication::find($medicationId);
        if (!$medication) {
            $errors[] = 'Medication not found.';
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Check if prescription is required
        if ($medication->requires_prescription) {
            if (!$prescriptionId) {
                $errors[] = 'This medication requires a valid prescription.';
            } else {
                $prescription = Prescription::find($prescriptionId);
                
                if (!$prescription) {
                    $errors[] = 'Prescription not found.';
                } else {
                    // Validate prescription
                    $prescriptionValidation = $this->validatePrescription($prescription);
                    
                    if (!$prescriptionValidation['valid']) {
                        $errors = array_merge($errors, $prescriptionValidation['errors']);
                    }
                    
                    if (!empty($prescriptionValidation['warnings'])) {
                        $warnings = array_merge($warnings, $prescriptionValidation['warnings']);
                        $requiresManualReview = true;
                    }
                }
            }
        }

        // Check inventory availability
        $inventoryCheck = $this->checkInventoryAvailability($medicationId, $quantity);
        if (!$inventoryCheck['available']) {
            $warnings[] = $inventoryCheck['message'];
            $requiresManualReview = true;
        }

        // Check for controlled substance
        if ($prescriptionId) {
            $prescription = Prescription::find($prescriptionId);
            if ($prescription && $prescription->is_controlled) {
                $warnings[] = 'This is a controlled substance and requires pharmacist verification.';
                $requiresManualReview = true;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'requires_manual_review' => $requiresManualReview,
        ];
    }

    /**
     * Validate prescription details.
     */
    private function validatePrescription(Prescription $prescription): array
    {
        $errors = [];
        $warnings = [];

        // Check if prescription is active
        if ($prescription->status !== 'active') {
            $errors[] = 'Prescription is not active.';
        }

        // Check expiration
        if ($prescription->isExpired()) {
            $errors[] = 'Prescription has expired.';
        } elseif (Carbon::parse($prescription->expiration_date)->diffInDays(now()) <= 30) {
            $warnings[] = 'Prescription expires soon. Please consult your doctor for renewal.';
        }

        // Check refills remaining
        if (!$prescription->hasRefillsRemaining()) {
            $errors[] = 'No refills remaining on this prescription.';
        }

        // Check verification
        if (!$prescription->is_verified) {
            $warnings[] = 'Prescription requires pharmacist verification.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Check inventory availability.
     */
    private function checkInventoryAvailability(int $medicationId, int $quantity): array
    {
        $inventory = Inventory::where('medication_id', $medicationId)
            ->where('quantity', '>=', $quantity)
            ->first();

        if (!$inventory) {
            return [
                'available' => false,
                'message' => 'Insufficient stock. Pharmacist will review availability.',
            ];
        }

        if ($inventory->quantity < ($quantity * 2)) {
            return [
                'available' => true,
                'message' => 'Low stock available. Your request may take longer to process.',
            ];
        }

        return [
            'available' => true,
            'message' => 'Stock available.',
        ];
    }

    /**
     * Validate pharmacist approval action.
     */
    public function validateApproval(RefillRequest $refillRequest): array
    {
        $errors = [];

        // Check if prescription exists and is valid
        if ($refillRequest->prescription_id) {
            $prescription = $refillRequest->prescription;
            
            if (!$prescription->canRefill()) {
                $errors[] = 'Prescription cannot be refilled (expired, no refills remaining, or not verified).';
            }
        }

        // Check inventory
        $inventoryCheck = $this->checkInventoryAvailability(
            $refillRequest->medication_id,
            $refillRequest->quantity
        );

        if (!$inventoryCheck['available']) {
            $errors[] = 'Insufficient inventory to fulfill this request.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
