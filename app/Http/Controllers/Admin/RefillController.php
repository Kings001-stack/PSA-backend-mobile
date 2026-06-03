<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefillRequest;
use App\Models\Inventory;
use App\Models\RefillAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RefillController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $urgentOnly = $request->boolean('urgent');
        
        $refills = RefillRequest::with(['user:id,name,email,phone', 'medication:id,name,dosage_form,strength'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, fn($q) => $q->whereHas('user', fn($query) => 
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($urgentOnly, fn($q) => $q->where('is_urgent', true))
            ->latest()
            ->paginate(15)
            ->through(fn ($refill) => [
                'id' => $refill->id,
                'user' => [
                    'id' => $refill->user->id,
                    'name' => $refill->user->name,
                    'email' => $refill->user->email,
                    'phone' => $refill->user->phone,
                ],
                'medication' => [
                    'id' => $refill->medication->id,
                    'name' => $refill->medication->name,
                    'dosage_form' => $refill->medication->dosage_form,
                    'strength' => $refill->medication->strength,
                ],
                'quantity' => $refill->quantity,
                'status' => $refill->status,
                'is_urgent' => $refill->is_urgent,
                'notes' => $refill->notes,
                'rejection_reason' => $refill->rejection_reason,
                'created_at' => $refill->created_at->format('M d, Y H:i'),
                'updated_at' => $refill->updated_at->diffForHumans(),
                'can_approve' => $refill->status === 'pending',
                'can_reject' => in_array($refill->status, ['pending', 'approved']),
                'can_mark_ready' => $refill->status === 'approved',
                'can_complete' => $refill->status === 'ready_for_pickup',
            ]);

        // Get status counts for filters
        $statusCounts = [
            'all' => RefillRequest::count(),
            'pending' => RefillRequest::where('status', 'pending')->count(),
            'approved' => RefillRequest::where('status', 'approved')->count(),
            'ready_for_pickup' => RefillRequest::where('status', 'ready_for_pickup')->count(),
            'completed' => RefillRequest::where('status', 'completed')->count(),
            'rejected' => RefillRequest::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/Refills/Index', [
            'refills' => $refills,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'urgent' => $urgentOnly,
            ],
            'statusCounts' => $statusCounts,
        ]);
    }

    public function approve(Request $request, RefillRequest $refill)
    {
        if ($refill->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending refills can be approved.']);
        }

        // Check inventory availability
        $inventory = Inventory::where('medication_id', $refill->medication_id)
            ->where('quantity', '>=', $refill->quantity)
            ->where('expiry_date', '>', now())
            ->first();

        if (!$inventory) {
            return back()->withErrors(['error' => 'Insufficient inventory to approve this refill.']);
        }

        DB::transaction(function () use ($refill, $request) {
            $refill->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            // Create audit log
            RefillAuditLog::create([
                'refill_request_id' => $refill->id,
                'user_id' => $request->user()->id,
                'action' => 'approved',
                'notes' => 'Refill request approved',
            ]);
        });

        return back()->with('success', 'Refill request approved successfully.');
    }

    public function reject(Request $request, RefillRequest $refill)
    {
        if (!in_array($refill->status, ['pending', 'approved'])) {
            return back()->withErrors(['error' => 'This refill cannot be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($refill, $request, $validated) {
            $refill->update([
                'status' => 'rejected',
                'rejection_reason' => $validated['rejection_reason'],
                'rejected_by' => $request->user()->id,
                'rejected_at' => now(),
            ]);

            // Create audit log
            RefillAuditLog::create([
                'refill_request_id' => $refill->id,
                'user_id' => $request->user()->id,
                'action' => 'rejected',
                'notes' => $validated['rejection_reason'],
            ]);
        });

        return back()->with('success', 'Refill request rejected.');
    }

    public function markReady(Request $request, RefillRequest $refill)
    {
        if ($refill->status !== 'approved') {
            return back()->withErrors(['error' => 'Only approved refills can be marked as ready.']);
        }

        DB::transaction(function () use ($refill, $request) {
            // Deduct from inventory
            $inventory = Inventory::where('medication_id', $refill->medication_id)
                ->where('quantity', '>=', $refill->quantity)
                ->where('expiry_date', '>', now())
                ->orderBy('expiry_date')
                ->first();

            if (!$inventory) {
                throw new \Exception('Insufficient inventory available.');
            }

            $inventory->decrement('quantity', $refill->quantity);

            $refill->update([
                'status' => 'ready_for_pickup',
                'prepared_by' => $request->user()->id,
                'prepared_at' => now(),
            ]);

            // Create audit log
            RefillAuditLog::create([
                'refill_request_id' => $refill->id,
                'user_id' => $request->user()->id,
                'action' => 'marked_ready',
                'notes' => 'Refill prepared and ready for pickup',
            ]);
        });

        return back()->with('success', 'Refill marked as ready for pickup.');
    }

    public function complete(Request $request, RefillRequest $refill)
    {
        if ($refill->status !== 'ready_for_pickup') {
            return back()->withErrors(['error' => 'Only refills ready for pickup can be completed.']);
        }

        DB::transaction(function () use ($refill, $request) {
            $refill->update([
                'status' => 'completed',
                'completed_by' => $request->user()->id,
                'completed_at' => now(),
            ]);

            // Create audit log
            RefillAuditLog::create([
                'refill_request_id' => $refill->id,
                'user_id' => $request->user()->id,
                'action' => 'completed',
                'notes' => 'Refill picked up by customer',
            ]);
        });

        return back()->with('success', 'Refill completed successfully.');
    }
}
