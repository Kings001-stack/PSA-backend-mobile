<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Medication;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = $request->input('per_page', 15);

        $query = Inventory::with('medication')
            ->when($search, function ($q) use ($search) {
                $q->whereHas('medication', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                })->orWhere('batch_number', 'like', "%{$search}%");
            })
            ->when($status === 'low_stock', function ($q) {
                $q->whereColumn('quantity', '<', 'reorder_level');
            })
            ->when($status === 'expiring', function ($q) {
                $q->where('expiry_date', '<=', now()->addDays(30))
                    ->where('expiry_date', '>', now());
            })
            ->when($status === 'expired', function ($q) {
                $q->where('expiry_date', '<=', now());
            });

        $inventory = $query->latest()->paginate($perPage)->through(function ($item) {
            return [
                'id' => $item->id,
                'medication_id' => $item->medication_id,
                'medication_name' => $item->medication?->name ?? 'Unknown',
                'medication_dosage' => $item->medication?->strength ?? '',
                'medication_form' => $item->medication?->dosage_form ?? '',
                'manufacturer' => $item->medication?->manufacturer ?? '',
                'quantity' => $item->quantity,
                'reorder_level' => $item->reorder_level ?? 10,
                'batch_number' => $item->batch_number,
                'expiry_date' => $item->expiry_date?->format('Y-m-d'),
                'supplier' => $item->supplier,
                'cost_price' => $item->cost_price,
                'selling_price' => $item->selling_price,
                'is_low_stock' => $item->quantity < ($item->reorder_level ?? 10),
                'is_expiring' => $item->expiry_date && $item->expiry_date <= now()->addDays(30) && $item->expiry_date > now(),
                'is_expired' => $item->expiry_date && $item->expiry_date <= now(),
                'updated_at' => $item->updated_at->format('M d, Y'),
            ];
        });

        $medications = Medication::select('id', 'name', 'strength as dosage', 'dosage_form as form')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->limit(100) // Limit to prevent timeout
            ->get();

        return Inertia::render('Admin/Inventory/Index', [
            'inventory' => $inventory,
            'medications' => $medications,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request)
    {
        // Check if we're creating a new medication or using existing
        if ($request->has('medication_name')) {
            // Sanitize and validate input
            $validated = $request->validate([
                'medication_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.]+$/'],
                'medication_dosage' => ['required', 'string', 'max:100', 'regex:/^[0-9]+(\.[0-9]+)?(mg|mcg|g|ml|L|IU|%|\\/[0-9]+ml)?$/i'],
                'medication_form' => ['required', 'string', 'in:Tablet,Capsule,Syrup,Injection,Cream,Drops,Inhaler,Powder'],
                'manufacturer' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.&]+$/'],
                'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
                'reorder_level' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'batch_number' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
                'expiry_date' => ['nullable', 'date', 'after:today'],
                'supplier' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.&]+$/'],
                'cost_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'selling_price' => ['nullable', 'numeric', 'min:0', 'max:1000000', 'gte:cost_price'],
            ], [
                'medication_name.regex' => 'Medication name can only contain letters, numbers, spaces, hyphens, and periods.',
                'medication_dosage.regex' => 'Dosage must be in format like: 500mg, 10ml, 250mcg',
                'medication_form.in' => 'Please select a valid medication form.',
                'manufacturer.regex' => 'Manufacturer name can only contain letters, numbers, spaces, hyphens, periods, and ampersands.',
                'batch_number.regex' => 'Batch number can only contain letters, numbers, and hyphens.',
                'expiry_date.after' => 'Expiry date must be in the future.',
                'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
                'quantity.min' => 'Quantity must be at least 1.',
                'quantity.max' => 'Quantity cannot exceed 1,000,000.',
            ]);

            // Sanitize inputs - Title Case for names, uppercase for batch/dosage
            $medicationName = ucwords(strtolower(trim($validated['medication_name'])));
            $dosage = strtoupper(trim($validated['medication_dosage']));
            $batchNumber = $validated['batch_number'] ? strtoupper(trim($validated['batch_number'])) : null;
            $manufacturer = $validated['manufacturer'] ? ucwords(strtolower(trim($validated['manufacturer']))) : null;
            $supplier = $validated['supplier'] ? ucwords(strtolower(trim($validated['supplier']))) : null;

            // Check for duplicate medication
            $existingMedication = Medication::where('tenant_id', auth()->user()->tenant_id)
                ->where('name', $medicationName)
                ->where('strength', $dosage)
                ->where('dosage_form', $validated['medication_form'])
                ->first();

            if ($existingMedication) {
                return back()->withErrors([
                    'medication_name' => 'This medication with the same dosage and form already exists. Use "Refill Inventory" to add more stock.'
                ])->withInput();
            }

            // Create the medication first with tenant_id
            $medication = Medication::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $medicationName,
                'strength' => $dosage,
                'dosage_form' => $validated['medication_form'],
                'manufacturer' => $manufacturer,
                'description' => null,
            ]);

            // Create inventory with the new medication and tenant_id
            Inventory::create([
                'tenant_id' => auth()->user()->tenant_id,
                'medication_id' => $medication->id,
                'quantity' => (int) $validated['quantity'],
                'reorder_level' => $validated['reorder_level'] ?? 10,
                'batch_number' => $batchNumber,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'supplier' => $supplier,
                'cost_price' => $validated['cost_price'] ?? null,
                'selling_price' => $validated['selling_price'] ?? null,
            ]);

            return redirect()->route('admin.inventory.index')
                ->with('success', "✅ {$medicationName} has been added to inventory successfully!");
        } else {
            // Original logic for existing medication_id
            $validated = $request->validate([
                'medication_id' => 'required|exists:medications,id',
                'quantity' => 'required|integer|min:1',
                'reorder_level' => 'nullable|integer|min:1',
                'batch_number' => 'nullable|string|max:255',
                'expiry_date' => 'nullable|date|after:today',
                'supplier' => 'nullable|string|max:255',
                'cost_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
            ]);

            Inventory::create([
                'tenant_id' => auth()->user()->tenant_id,
                ...$validated
            ]);

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory item added successfully.');
        }
    }

    public function update(Request $request, Inventory $inventory)
    {
        // Check if medication details are being updated
        if ($request->has('medication_name')) {
            $validated = $request->validate([
                'medication_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.]+$/'],
                'medication_dosage' => ['required', 'string', 'max:100', 'regex:/^[0-9]+(\.[0-9]+)?(mg|mcg|g|ml|L|IU|%|\\/[0-9]+ml)?$/i'],
                'medication_form' => ['required', 'string', 'in:Tablet,Capsule,Syrup,Injection,Cream,Drops,Inhaler,Powder'],
                'manufacturer' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.&]+$/'],
                'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
                'reorder_level' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'batch_number' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
                'expiry_date' => ['nullable', 'date'],
                'supplier' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-\.&]+$/'],
                'cost_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'selling_price' => ['nullable', 'numeric', 'min:0', 'max:1000000', 'gte:cost_price'],
            ], [
                'medication_name.regex' => 'Medication name can only contain letters, numbers, spaces, hyphens, and periods.',
                'medication_dosage.regex' => 'Dosage must be in format like: 500mg, 10ml, 250mcg',
                'selling_price.gte' => 'Selling price must be greater than or equal to cost price.',
            ]);

            // Sanitize inputs
            $medicationName = ucwords(strtolower(trim($validated['medication_name'])));
            $dosage = strtoupper(trim($validated['medication_dosage']));
            $batchNumber = $validated['batch_number'] ? strtoupper(trim($validated['batch_number'])) : null;
            $manufacturer = $validated['manufacturer'] ? ucwords(strtolower(trim($validated['manufacturer']))) : null;
            $supplier = $validated['supplier'] ? ucwords(strtolower(trim($validated['supplier']))) : null;

            // Update the medication details
            if ($inventory->medication) {
                $inventory->medication->update([
                    'name' => $medicationName,
                    'strength' => $dosage,
                    'dosage_form' => $validated['medication_form'],
                    'manufacturer' => $manufacturer,
                ]);
            }

            // Update inventory details
            $inventory->update([
                'quantity' => (int) $validated['quantity'],
                'reorder_level' => $validated['reorder_level'] ?? 10,
                'batch_number' => $batchNumber,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'supplier' => $supplier,
                'cost_price' => $validated['cost_price'] ?? null,
                'selling_price' => $validated['selling_price'] ?? null,
            ]);

            return redirect()->route('admin.inventory.index')
                ->with('success', "✅ {$medicationName} updated successfully!");
        } else {
            // Original logic for inventory-only updates
            $validated = $request->validate([
                'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
                'reorder_level' => ['nullable', 'integer', 'min:1', 'max:100000'],
                'batch_number' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
                'expiry_date' => ['nullable', 'date'],
                'supplier' => ['nullable', 'string', 'max:255'],
                'cost_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
                'selling_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            ]);

            // Sanitize batch number if provided
            if (isset($validated['batch_number'])) {
                $validated['batch_number'] = strtoupper(trim($validated['batch_number']));
            }

            $inventory->update($validated);

            return redirect()->route('admin.inventory.index')
                ->with('success', 'Inventory updated successfully.');
        }
    }

    public function destroy(Inventory $inventory)
    {
        // Get medication name before deleting
        $medicationName = $inventory->medication?->name ?? 'Unknown medication';
        $dosage = $inventory->medication?->strength ?? '';
        $form = $inventory->medication?->dosage_form ?? '';
        
        // Format the full name
        $fullName = $medicationName;
        if ($dosage) {
            $fullName .= " {$dosage}";
        }
        if ($form) {
            $fullName .= " ({$form})";
        }
        
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', "🗑️ {$fullName} has been deleted from inventory.");
    }
}
