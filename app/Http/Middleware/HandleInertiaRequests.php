<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'avatar_url' => $request->user()->avatar_url,
                    'account_status' => $request->user()->account_status,
                    'is_super_admin' => $request->user()->isSuperAdmin(),
                    'is_pharmacist' => $request->user()->isPharmacist(),
                    'is_admin' => $request->user()->isAdmin(),
                ] : null,
                'tenant' => $request->user()?->tenant ? [
                    'id' => $request->user()->tenant->id,
                    'name' => $request->user()->tenant->name,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            // Use pre-generated ziggy routes instead of generating on each request
            'ziggy' => function () use ($request) {
                return [
                    ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                    'location' => $request->url(),
                ];
            },
        ]);
    }
}
