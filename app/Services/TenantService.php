<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new tenant with unique token and Pinecone namespace.
     */
    public function createTenant(array $data): Tenant
    {
        $data['tenant_token'] = $this->generateUniqueToken();
        $data['pinecone_namespace'] = $this->generatePineconeNamespace();

        return Tenant::create($data);
    }

    /**
     * Validate a tenant token and return the tenant.
     */
    public function validateToken(string $token): ?Tenant
    {
        return Tenant::where('tenant_token', $token)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get tenant context by token.
     */
    public function getTenantContext(string $token): ?Tenant
    {
        return $this->validateToken($token);
    }

    /**
     * Generate a unique tenant token.
     */
    protected function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Tenant::where('tenant_token', $token)->exists());

        return $token;
    }

    /**
     * Generate a unique Pinecone namespace for the tenant.
     */
    protected function generatePineconeNamespace(): string
    {
        do {
            $namespace = 'tenant_'.Str::uuid();
        } while (Tenant::where('pinecone_namespace', $namespace)->exists());

        return $namespace;
    }

    /**
     * Update tenant settings.
     */
    public function updateSettings(Tenant $tenant, array $settings): Tenant
    {
        $tenant->update(['settings' => $settings]);

        return $tenant->fresh();
    }

    /**
     * Deactivate a tenant.
     */
    public function deactivate(Tenant $tenant): bool
    {
        return $tenant->update(['is_active' => false]);
    }

    /**
     * Activate a tenant.
     */
    public function activate(Tenant $tenant): bool
    {
        return $tenant->update(['is_active' => true]);
    }

    /**
     * Regenerate tenant token.
     */
    public function regenerateToken(Tenant $tenant): Tenant
    {
        $tenant->update(['tenant_token' => $this->generateUniqueToken()]);

        return $tenant->fresh();
    }
}
