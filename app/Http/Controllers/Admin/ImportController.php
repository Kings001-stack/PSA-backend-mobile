<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Inventory/Import', [
            // Add import history here when implemented
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        // TODO: Implement CSV import logic
        
        return back()->with('success', 'CSV import functionality coming soon.');
    }
}
