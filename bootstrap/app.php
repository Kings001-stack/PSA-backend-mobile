<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::prefix('admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        
        $middleware->alias([
            'tenant.auth' => \App\Http\Middleware\TenantAuthentication::class,
            'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'api.rate.limit' => \App\Http\Middleware\ApiRateLimiter::class,
            'pharmacist' => \App\Http\Middleware\EnsureUserIsPharmacist::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'super.admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            // Web dashboard middleware
            'web.pharmacist' => \App\Http\Middleware\RedirectIfNotPharmacist::class,
            'web.super.admin' => \App\Http\Middleware\RedirectIfNotSuperAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
