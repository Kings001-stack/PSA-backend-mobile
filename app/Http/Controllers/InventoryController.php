<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryRequest;
use App\Models\Inventory;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \Log::info('Accessing Inventory index');
        try {
            $inventory = Inventory::with('medication')->latest()->paginate(10);
            $medications = Medication::all();
            \Log::info('Inventory count: ' . $inventory->total());
            
            return Inertia::render('Inventory/Index', [
                'inventory' => $inventory,
                'medications' => $medications,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in InventoryController@index: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryRequest $request)
    {
        $user = auth()->user();
        
        if (!$user->tenant_id) {
            return redirect()->back()->withErrors(['tenant' => 'Your account is not associated with a pharmacy. Please contact support.']);
        }
        
        try {
            $data = $request->validated();
            $data['tenant_id'] = $user->tenant_id;

            Inventory::create($data);

            return redirect()->back()->with('success', 'Inventory added successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create inventory: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Failed to add inventory stock. Please try again.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInventoryRequest $request, Inventory $inventory)
    {
        $inventory->update($request->validated());

        return redirect()->back()->with('success', 'Inventory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return redirect()->back()->with('success', 'Inventory removed successfully.');
    }
}
