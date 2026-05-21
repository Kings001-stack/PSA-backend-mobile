<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = [
        'tenant_id',
        'role',
        'resource',
        'action',
        'allowed',
        'conditions',
    ];

    protected $casts = [
        'allowed' => 'boolean',
        'conditions' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if a role has permission for a resource and action
     */
    public static function hasPermission(int $tenantId, string $role, string $resource, string $action): bool
    {
        $permission = self::where('tenant_id', $tenantId)
            ->where('role', $role)
            ->where('resource', $resource)
            ->where('action', $action)
            ->first();

        // If no explicit permission exists, use default role hierarchy rules
        if (!$permission) {
            return self::defaultPermission($role, $resource, $action);
        }

        return $permission->allowed;
    }

    /**
     * Default permission rules based on role hierarchy
     */
    private static function defaultPermission(string $role, string $resource, string $action): bool
    {
        // Super Admin has all permissions
        if ($role === 'super_admin') {
            return true;
        }

        // Admin permissions
        if ($role === 'admin') {
            $adminAllowed = [
                'users' => ['read', 'update', 'suspend'],
                'inventory' => ['create', 'read', 'update', 'delete'],
                'refills' => ['read', 'update', 'approve', 'reject'],
                'analytics' => ['read'],
                'adverts' => ['create', 'read', 'update', 'delete'],
            ];
            
            return isset($adminAllowed[$resource]) && in_array($action, $adminAllowed[$resource]);
        }

        // Pharmacist permissions
        if ($role === 'pharmacist') {
            $pharmacistAllowed = [
                'inventory' => ['read'],
                'refills' => ['read', 'update', 'approve', 'reject'],
            ];
            
            return isset($pharmacistAllowed[$resource]) && in_array($action, $pharmacistAllowed[$resource]);
        }

        return false;
    }
}
