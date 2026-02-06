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
        $inventory = Inventory::with('medication')
            ->orderBy('id', 'desc')
            ->paginate(15);
            
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

        $request->validate([
            'quantity' => 'sometimes|integer|min:0',
            'reorder_level' => 'sometimes|integer|min:0',
            'batch_number' => 'sometimes|string',
            'expiry_date' => 'sometimes|date',
        ]);

        $inventory->update($request->all());

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
