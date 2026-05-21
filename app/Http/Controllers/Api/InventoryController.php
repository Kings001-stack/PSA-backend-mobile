<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        
        $inventory = Inventory::with('medication')
            ->orderBy('id', 'desc')
            ->paginate(15);
        
        // Hide prices from non-admin users
        if (!$isAdmin) {
            $inventory->getCollection()->each(function ($item) {
                $item->makeHidden(['unit_price']);
            });
        }
            
        return response()->json($inventory);
    }

    /**
     * Store a new resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'medication_id' => 'nullable|exists:medications,id',
            'medication_name' => 'required_without:medication_id|string',
            'quantity' => 'required|integer|min:1',
            'batch_number' => 'required|string',
            'expiry_date' => 'required|date',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $medicationId = $request->medication_id;

        // If medication_id is not provided, find or create by name
        if (!$medicationId && $request->medication_name) {
            $medication = \App\Models\Medication::firstOrCreate(
                ['name' => $request->medication_name, 'tenant_id' => $user->tenant_id],
                ['price' => 0] // Default price if creating new
            );
            $medicationId = $medication->id;
        }

        $inventory = Inventory::create([
            'tenant_id' => $user->tenant_id,
            'medication_id' => $medicationId,
            'quantity' => $request->quantity,
            'batch_number' => $request->batch_number,
            'expiry_date' => $request->expiry_date,
            'unit_price' => $request->unit_price,
        ]);

        return response()->json([
            'message' => 'Stock added successfully',
            'inventory' => $inventory->load('medication'),
        ], 201);
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|nullable|integer|min:0',
            'batch_number' => 'sometimes|string',
            'expiry_date' => 'sometimes|date',
            'unit_price' => 'sometimes|nullable|numeric|min:0',
        ]);

        // Convert empty strings to null for nullable fields
        if (isset($validated['reorder_level']) && $validated['reorder_level'] === '') {
            $validated['reorder_level'] = null;
        }
        if (isset($validated['unit_price']) && $validated['unit_price'] === '') {
            $validated['unit_price'] = null;
        }

        $inventory->update($validated);

        return response()->json([
            'message' => 'Stock updated successfully',
            'inventory' => $inventory->load('medication'),
        ]);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        
        $inventory->delete();

        return response()->json([
            'message' => 'Stock item removed successfully',
        ]);
    }
}
