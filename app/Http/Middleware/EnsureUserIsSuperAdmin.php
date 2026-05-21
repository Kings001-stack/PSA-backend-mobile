<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin access required.',
            ], 403);
        }

        if (!$request->user()->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended.',
            ], 403);
        }

        return $next($request);
    }
}
