<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotPharmacist
{
    /**
     * Handle an incoming request for web routes.
     * Redirects users who are not pharmacists/admins.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Regular users should not access web dashboard
        if ($request->user()->isUser()) {
            abort(403, 'Access denied. This dashboard is for pharmacy staff only.');
        }

        // Check if account is active
        if (!$request->user()->isActive()) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
