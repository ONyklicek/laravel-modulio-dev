<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModuleDeregistered;
use NyonCode\LaravelModulio\ModuleManager;
use Spatie\Permission\Models\Permission;

/**
 * Listener pro cleanup oprávnění při deregistraci
 *
 * Volitelně maže oprávnění modulu při jeho deregistraci
 * podle konfigurace.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Module Permission Cleanup Listener
 *
 * Optionally deletes module permissions on deregistration
 * based on configuration.
 */
class CleanupModulePermissions
{
    /**
     * Zpracuje event deregistrace modulu
     * Handle module deregistration event
     *
     * @param ModuleDeregistered $event
     */
    public function handle(ModuleDeregistered $event): void
    {
        if (!config('modulio.auto_delete_permissions', false)) {
            return;
        }

        if (!class_exists(Permission::class)) {
            return;
        }

        $permissions = $event->module->getPermissions();
        $deletedPermissions = [];

        foreach ($permissions as $permissionName) {
            try {
                $permission = Permission::where('name', $permissionName)->first();

                if ($permission) {
                    // Kontrola zda není oprávnění používáno jinými moduly
                    // Check if permission is not used by other modules
                    if ($this->isPermissionUsedByOtherModules($permissionName, $event->getModuleName())) {
                        Log::info('Permission not deleted - used by other modules', [
                            'permission' => $permissionName,
                            'module' => $event->getModuleName(),
                        ]);
                        continue;
                    }

                    $permission->delete();
                    $deletedPermissions[] = $permissionName;
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete permission', [
                    'permission' => $permissionName,
                    'module' => $event->getModuleName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (!empty($deletedPermissions)) {
            Log::info('Module permissions cleaned up', [
                'module' => $event->getModuleName(),
                'deleted_permissions' => $deletedPermissions,
            ]);
        }
    }

    /**
     * Kontroluje zda je oprávnění používáno jinými moduly
     * Checks if permission is used by other modules
     *
     * @param string $permissionName
     * @param string $excludeModuleName
     * @return bool
     */
    protected function isPermissionUsedByOtherModules(string $permissionName, string $excludeModuleName): bool
    {
        $moduleManager = app(ModuleManager::class);
        $modules = $moduleManager->getModules();

        foreach ($modules as $module) {
            if ($module->getName() === $excludeModuleName) {
                continue;
            }

            if (in_array($permissionName, $module->getPermissions())) {
                return true;
            }
        }

        return false;
    }
}