<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $action = $request->input('action');
        
        $logs = SystemActivityLog::with('user')
            ->when($search, fn($q) => $q->where('description', 'like', "%{$search}%"))
            ->when($action, fn($q) => $q->where('action', $action))
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Activity/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'action' => $action,
            ],
        ]);
    }

    public function export()
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Export functionality coming soon.');
    }
}
