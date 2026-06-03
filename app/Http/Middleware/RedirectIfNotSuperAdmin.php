<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotSuperAdmin
{
    /**
     * Handle an incoming request for super admin web routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Access denied. Super Admin privileges required.');
        }

        if (!$request->user()->isActive()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
