<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    /**
     * Get user's prescriptions.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');

        $query = Prescription::where('user_id', $request->user()->id)
            ->with('medication:id,name,dosage_form,strength');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->get();

        return response()->json($prescriptions);
    }

    /**
     * Upload prescription document (USER).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'medication_id' => 'required|exists:medications,id',
            'prescriber_name' => 'required|string|max:255',
            'prescribed_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $file = $request->file('document');
        
        // Store file
        $path = $file->store('prescriptions/' . $user->tenant_id, 'public');

        // Create prescription record (pending verification)
        $prescription = Prescription::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'medication_id' => $request->medication_id,
            'prescription_number' => 'RX-' . strtoupper(Str::random(10)),
            'prescriber_name' => $request->prescriber_name,
            'prescribed_date' => $request->prescribed_date,
            'expiration_date' => now()->addYear(), // Default 1 year, pharmacist will update
            'refills_allowed' => 0, // Pharmacist will set
            'refills_remaining' => 0,
            'dosage' => 'Pending verification',
            'frequency' => 'Pending verification',
            'instructions' => $request->notes,
            'document_path' => $path,
            'status' => 'active',
            'is_verified' => false,
        ]);

        $prescription->load('medication');

        return response()->json([
            'message' => 'Prescription uploaded successfully. A pharmacist will verify it shortly.',
            'prescription' => $prescription,
        ], 201);
    }

    /**
     * Get prescription details (USER - own prescriptions only).
     */
    public function show(Request $request, int $id)
    {
        $prescription = Prescription::where('user_id', $request->user()->id)
            ->with(['medication', 'verifiedBy:id,name'])
            ->findOrFail($id);

        return response()->json($prescription);
    }

    /**
     * Get all prescriptions for pharmacist review.
     */
    public function pharmacistIndex(Request $request)
    {
        $verified = $request->query('verified');

        $query = Prescription::with([
            'user:id,name,email,phone',
            'medication:id,name',
            'verifiedBy:id,name'
        ]);

        if ($verified === 'false') {
            $query->where('is_verified', false);
        } elseif ($verified === 'true') {
            $query->where('is_verified', true);
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => Prescription::count(),
            'pending_verification' => Prescription::where('is_verified', false)->count(),
            'verified' => Prescription::where('is_verified', true)->count(),
            'expired' => Prescription::expired()->count(),
            'controlled' => Prescription::controlled()->count(),
        ];

        return response()->json([
            'prescriptions' => $prescriptions,
            'stats' => $stats,
        ]);
    }

    /**
     * Verify and update prescription details (PHARMACIST ONLY).
     */
    public function verify(Request $request, int $id)
    {
        $request->validate([
            'prescriber_license' => 'nullable|string|max:255',
            'expiration_date' => 'required|date|after:today',
            'refills_allowed' => 'required|integer|min:0|max:12',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:1000',
            'is_controlled' => 'boolean',
            'controlled_schedule' => 'nullable|string|in:II,III,IV,V',
        ]);

        $prescription = Prescription::findOrFail($id);

        if ($prescription->is_verified) {
            return response()->json([
                'message' => 'This prescription has already been verified.',
            ], 422);
        }

        $prescription->update([
            'prescriber_license' => $request->prescriber_license,
            'expiration_date' => $request->expiration_date,
            'refills_allowed' => $request->refills_allowed,
            'refills_remaining' => $request->refills_allowed,
            'dosage' => $request->dosage,
            'frequency' => $request->frequency,
            'instructions' => $request->instructions,
            'is_controlled' => $request->is_controlled ?? false,
            'controlled_schedule' => $request->controlled_schedule,
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $prescription->load(['user:id,name,email', 'medication']);

        // TODO: Send notification to user

        return response()->json([
            'message' => 'Prescription verified successfully.',
            'prescription' => $prescription,
        ]);
    }

    /**
     * Download prescription document.
     */
    public function download(int $id)
    {
        $prescription = Prescription::findOrFail($id);

        // Check authorization
        if (auth()->user()->role === 'user' && $prescription->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$prescription->document_path || !Storage::disk('public')->exists($prescription->document_path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return Storage::disk('public')->download($prescription->document_path);
    }
}
