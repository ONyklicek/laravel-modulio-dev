<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModuleDeregistered;
use Spatie\Permission\PermissionRegistrar;

/**
 * Listener pro vyčištění cache modulů
 *
 * Vyčistí všechny cache související s modulem
 * při jeho deregistraci.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Clear Module Cache Listener
 *
 * Clears all caches related to module
 * on its deregistration.
 */
class ClearModuleCache
{
    /**
     * Zpracuje event deregistrace modulu
     * Handle module deregistration event
     *
     * @param ModuleDeregistered $event
     */
    public function handle(ModuleDeregistered $event): void
    {
        if (!config('modulio.cache_enabled', true)) {
            return;
        }

        $prefix = config('modulio.cache_prefix', 'modulio');
        $moduleName = $event->getModuleName();

        // Vyčistí obecné cache
        // Clear general caches
        cache()->forget("{$prefix}.modules");
        cache()->forget("{$prefix}.permissions");

        // Vyčistí cache specifické pro modul
        // Clear module-specific caches
        cache()->forget("{$prefix}.{$moduleName}");
        cache()->forget("{$prefix}.{$moduleName}.config");
        cache()->forget("{$prefix}.{$moduleName}.permissions");
        cache()->forget("{$prefix}.{$moduleName}.routes");

        // Vyčistí navigační cache
        // Clear navigation cache
        $availableMenus = config('modulio.available_menus', [
            'default', 'admin', 'sidebar', 'mobile'
        ]);

        foreach ($availableMenus as $menuName => $label) {
            cache()->forget("{$prefix}.navigation.{$menuName}");
        }

        // Vyčistí Spatie Permission cache pokud existuje
        // Clear Spatie Permission cache if exists
        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        if (config('modulio.debug_mode', false)) {
            Log::info('Module deregistration cache cleared', [
                'module_name' => $moduleName,
                'cleared_general_caches' => ['modules', 'permissions'],
                'cleared_navigation_menus' => array_keys($availableMenus),
            ]);
        }
    }
}