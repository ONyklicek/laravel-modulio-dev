<?php

namespace NyonCode\LaravelModulio\Concerns;

/**
 * Trait pro práci s permissions v modulech
 *
 * Rozšířené metody pro správu oprávnění
 * v kontextu modulárního systému.
 *
 * @package NyonCode\LaravelModulio\Traits
 *
 * ---
 *
 * Module Permissions Trait
 *
 * Extended methods for permission management
 * in modular system context.
 */
trait HasModulePermissions
{
    /**
     * Vytvoří oprávnění pro modul
     * Creates permissions for module
     *
     * @param string $moduleName
     * @param array $permissions
     * @param string|null $guardName
     * @return array Vytvořená oprávnění / Created permissions
     */
    protected function createModulePermissions(string $moduleName, array $permissions, ?string $guardName = null): array
    {
        $guardName = $guardName ?? config('modulio.permission_guard', 'web');
        $prefix = config('modulio.permission_prefix', 'module.');
        $created = [];

        foreach ($permissions as $permission) {
            $fullPermissionName = $prefix . $moduleName . '.' . $permission;

            if (!\Spatie\Permission\Models\Permission::where('name', $fullPermissionName)->exists()) {
                \Spatie\Permission\Models\Permission::create([
                    'name' => $fullPermissionName,
                    'guard_name' => $guardName,
                ]);

                $created[] = $fullPermissionName;
            }
        }

        return $created;
    }

    /**
     * Smaže oprávnění pro modul
     * Deletes permissions for module
     *
     * @param string $moduleName
     * @param array|null $permissions Pokud null, smaže všechna oprávnění modulu
     * @return array Smazaná oprávnění / Deleted permissions
     */
    protected function deleteModulePermissions(string $moduleName, ?array $permissions = null): array
    {
        $prefix = config('modulio.permission_prefix', 'module.');
        $deleted = [];

        if ($permissions === null) {
            // Smaže všechna oprávnění modulu
            // Delete all module permissions
            $modulePermissions = \Spatie\Permission\Models\Permission::where('name', 'like', $prefix . $moduleName . '.%')->get();

            foreach ($modulePermissions as $permission) {
                $deleted[] = $permission->name;
                $permission->delete();
            }
        } else {
            foreach ($permissions as $permission) {
                $fullPermissionName = $prefix . $moduleName . '.' . $permission;

                $permissionModel = \Spatie\Permission\Models\Permission::where('name', $fullPermissionName)->first();
                if ($permissionModel) {
                    $deleted[] = $fullPermissionName;
                    $permissionModel->delete();
                }
            }
        }

        return $deleted;
    }

    /**
     * Synchronizuje oprávnění modulu
     * Synchronizes module permissions
     *
     * @param string $moduleName
     * @param array $permissions
     * @param string|null $guardName
     * @return array ['created' => [], 'deleted' => []]
     */
    protected function syncModulePermissions(string $moduleName, array $permissions, ?string $guardName = null): array
    {
        $guardName = $guardName ?? config('modulio.permission_guard', 'web');
        $prefix = config('modulio.permission_prefix', 'module.');

        // Získá existující oprávnění modulu
        // Get existing module permissions
        $existingPermissions = \Spatie\Permission\Models\Permission::where('name', 'like', $prefix . $moduleName . '.%')
            ->pluck('name')
            ->map(fn($name) => str_replace($prefix . $moduleName . '.', '', $name))
            ->toArray();

        $toCreate = array_diff($permissions, $existingPermissions);
        $toDelete = array_diff($existingPermissions, $permissions);

        $created = $this->createModulePermissions($moduleName, $toCreate, $guardName);
        $deleted = $this->deleteModulePermissions($moduleName, $toDelete);

        return compact('created', 'deleted');
    }

    /**
     * Přiřadí oprávnění role
     * Assigns permissions to role
     *
     * @param string $roleName
     * @param string $moduleName
     * @param array $permissions
     * @return bool
     */
    protected function assignModulePermissionsToRole(string $roleName, string $moduleName, array $permissions): bool
    {
        try {
            $role = \Spatie\Permission\Models\Role::findByName($roleName);
            $prefix = config('modulio.permission_prefix', 'module.');

            $fullPermissions = array_map(
                fn($permission) => $prefix . $moduleName . '.' . $permission,
                $permissions
            );

            $role->givePermissionTo($fullPermissions);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Kontroluje oprávnění pro více akcí najednou
     * Checks permissions for multiple actions at once
     *
     * @param string $moduleName
     * @param array $permissions
     * @param bool $requireAll Pokud true, vyžaduje všechna oprávnění
     * @return bool
     */
    protected function hasModulePermissions(string $moduleName, array $permissions, bool $requireAll = true): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $prefix = config('modulio.permission_prefix', 'module.');
        $fullPermissions = array_map(
            fn($permission) => $prefix . $moduleName . '.' . $permission,
            $permissions
        );

        $user = auth()->user();

        if ($requireAll) {
            return $user->hasAllPermissions($fullPermissions);
        } else {
            return $user->hasAnyPermission($fullPermissions);
        }
    }
}