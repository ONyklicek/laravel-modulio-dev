<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModulePermissionsCreated;
use Spatie\Permission\Models\Role;

/**
 * Listener pro synchronizaci oprávnění s rolemi
 *
 * Automaticky přiřadí nová oprávnění určeným rolím
 * podle konfigurace.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Permission Role Sync Listener
 *
 * Automatically assigns new permissions to designated roles
 * based on configuration.
 */
class SyncPermissionsWithRoles
{
    /**
     * Zpracuje event vytvoření oprávnění
     * Handle permissions created event
     *
     * @param ModulePermissionsCreated $event
     */
    public function handle(ModulePermissionsCreated $event): void
    {
        if (!config('modulio.auto_sync_permissions_with_roles', false)) {
            return;
        }

        if (!class_exists(Role::class)) {
            return;
        }

        $modulePermissionMappings = config('modulio.permission_role_mappings', []);
        $moduleName = $event->module->getName();

        // Kontrola zda má modul definovaná mapování
        // Check if module has defined mappings
        if (!isset($modulePermissionMappings[$moduleName])) {
            return;
        }

        $mappings = $modulePermissionMappings[$moduleName];

        foreach ($mappings as $roleName => $permissionPatterns) {
            try {
                $role = Role::findByName($roleName);
                $this->assignMatchingPermissions($role, $event->createdPermissions, $permissionPatterns);
            } catch (\Exception $e) {
                Log::error('Failed to sync permissions with role', [
                    'role' => $roleName,
                    'module' => $moduleName,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Přiřadí odpovídající oprávnění roli
     * Assign matching permissions to role
     *
     * @param Role $role
     * @param array $permissions
     * @param array $patterns
     */
    protected function assignMatchingPermissions(Role $role, array $permissions, array $patterns): void
    {
        $matchingPermissions = [];

        foreach ($permissions as $permission) {
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $permission)) {
                    $matchingPermissions[] = $permission;
                    break;
                }
            }
        }

        if (!empty($matchingPermissions)) {
            $role->givePermissionTo($matchingPermissions);

            Log::info('Permissions synced with role', [
                'role' => $role->name,
                'permissions' => $matchingPermissions,
            ]);
        }
    }
}