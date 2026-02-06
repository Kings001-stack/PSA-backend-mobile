<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');

        if (! $tenant) {
            return response()->json([
                'error' => 'Tenant not found',
            ], 401);
        }

        $key = 'chat:'.$tenant->id;

        $perMinute = config('gemini.rate_limit.per_minute', 60);
        $perHour = config('gemini.rate_limit.per_hour', 1000);

        // Check per-minute limit
        if (RateLimiter::tooManyAttempts($key.':minute', $perMinute)) {
            return response()->json([
                'error' => 'Too many requests. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key.':minute'),
            ], 429);
        }

        // Check per-hour limit
        if (RateLimiter::tooManyAttempts($key.':hour', $perHour)) {
            return response()->json([
                'error' => 'Hourly rate limit exceeded. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key.':hour'),
            ], 429);
        }

        RateLimiter::hit($key.':minute', 60);
        RateLimiter::hit($key.':hour', 3600);

        return $next($request);
    }
}
