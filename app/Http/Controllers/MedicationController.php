<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicationRequest;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Medications/Index', [
            'medications' => Medication::latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicationRequest $request)
    {
        $user = auth()->user();
        
        if (!$user->tenant_id) {
            return redirect()->back()->withErrors(['tenant' => 'Your account is not associated with a pharmacy. Please contact support.']);
        }
        
        try {
            $data = $request->validated();
            $data['tenant_id'] = $user->tenant_id;
            
            Medication::create($data);

            return redirect()->back()->with('success', 'Medication created successfully.');
        } catch (\Exception $e) {
            \Log::error('Failed to create medication: ' . $e->getMessage());
            return redirect()->back()->withErrors(['general' => 'Failed to create medication. Please try again.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMedicationRequest $request, Medication $medication)
    {
        // Global scope ensures we can only update our own tenant's medication
        $medication->update($request->validated());

        return redirect()->back()->with('success', 'Medication updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medication $medication)
    {
        $medication->delete();

        return redirect()->back()->with('success', 'Medication deleted successfully.');
    }
}
