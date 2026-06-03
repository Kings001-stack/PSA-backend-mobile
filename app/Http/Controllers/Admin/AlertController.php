<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LowStockAlert;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlertController extends Controller
{
    public function index(): Response
    {
        $alerts = LowStockAlert::with('inventory.medication')
            ->where('is_resolved', false)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Alerts/Index', [
            'alerts' => $alerts,
        ]);
    }

    public function resolve(LowStockAlert $alert)
    {
        $alert->update(['is_resolved' => true]);
        return back()->with('success', 'Alert resolved.');
    }
}
