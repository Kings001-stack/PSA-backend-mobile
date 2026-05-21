<?php

namespace App\Providers;

use App\Contracts\AIProviderInterface;
use App\Services\AIServiceFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind AIProviderInterface to the factory-resolved provider
        $this->app->bind(AIProviderInterface::class, function ($app) {
            return AIServiceFactory::make();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::define('admin', function ($user) {
            $allowedRoles = ['admin', 'pharmacist'];
            $isAllowed = in_array(strtolower($user->role), $allowedRoles);
            
            \Log::info('Checking Admin Gate', [
                'user_id' => $user->id, 
                'role' => $user->role,
                'result' => $isAllowed ? 'ALLOWED' : 'DENIED'
            ]);
            
            return $isAllowed;
        });
    }
}
