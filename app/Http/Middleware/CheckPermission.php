<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param string $resource The resource being accessed (e.g., 'users', 'admins')
     * @param string $action The action being performed (e.g., 'create', 'update', 'delete')
     */
    public function handle(Request $request, Closure $next, string $resource, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is suspended.',
            ], 403);
        }

        // Check permission
        $hasPermission = Permission::hasPermission(
            $user->tenant_id,
            $user->role,
            $resource,
            $action
        );

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized. You don't have permission to {$action} {$resource}.",
            ], 403);
        }

        return $next($request);
    }
}
