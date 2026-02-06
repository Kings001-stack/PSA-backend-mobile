<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAuthentication
{
    public function __construct(protected TenantService $tenantService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Tenant-Token') ?? $request->input('tenant_token');

        if (! $token) {
            return response()->json([
                'error' => 'Tenant token is required',
            ], 401);
        }

        $tenant = $this->tenantService->validateToken($token);

        if (! $tenant) {
            return response()->json([
                'error' => 'Invalid or inactive tenant token',
            ], 401);
        }

        // Store tenant in request for later use
        $request->merge(['tenant' => $tenant]);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
