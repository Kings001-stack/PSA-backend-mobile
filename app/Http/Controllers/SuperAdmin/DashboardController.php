<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Inventory;
use App\Models\RefillRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_pharmacists' => User::where('role', 'pharmacist')->count(),
            'total_inventory' => Inventory::sum('quantity'),
            'pending_refills' => RefillRequest::where('status', 'pending')->count(),
        ];

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
