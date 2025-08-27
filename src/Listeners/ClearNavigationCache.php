<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModuleRegistered;

/**
 * Listener pro vyčištění cache při registraci modulu
 *
 * Automaticky vyčistí relevantní cache klíče
 * po úspěšné registraci modulu.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Clear Navigation Cache Listener
 *
 * Automatically clears relevant cache keys
 * after successful module registration.
 */
class ClearNavigationCache
{
    /**
     * Zpracuje event registrace modulu
     * Handle module registration event
     *
     * @param ModuleRegistered $event
     */
    public function handle(ModuleRegistered $event): void
    {
        if (!config('modulio.cache_enabled', true)) {
            return;
        }

        $prefix = config('modulio.cache_prefix', 'modulio');

        // Vyčistí cache navigace pro všechna menu
        // Clear navigation cache for all menus
        $availableMenus = config('modulio.available_menus', [
            'default' => 'Default menu',
            'admin' => 'Admin menu',
            'sidebar' => 'Sidebar menu',
            'mobile' => 'Mobile menu',
        ]);

        foreach ($availableMenus as $menuName => $label) {
            cache()->forget("{$prefix}.navigation.{$menuName}");
        }

        // Vyčistí hlavní cache modulů
        // Clear main modules cache
        cache()->forget("{$prefix}.modules");

        // Vyčistí cache oprávnění
        // Clear permissions cache
        cache()->forget("{$prefix}.permissions");

        if (config('modulio.debug_mode', false)) {
            Log::info('Module registration cache cleared', [
                'module_name' => $event->getModuleName(),
                'cleared_caches' => array_keys($availableMenus),
            ]);
        }
    }
}