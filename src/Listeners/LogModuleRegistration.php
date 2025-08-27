<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModuleRegistered;

/**
 * Listener pro logování registrace modulů
 *
 * Loguje informace o úspěšné registraci modulu
 * do systémových logů pro auditní účely.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Module Registration Logging Listener
 *
 * Logs information about successful module registration
 * to system logs for audit purposes.
 */
class LogModuleRegistration
{
    /**
     * Zpracuje event registrace modulu
     * Handle module registration event
     *
     * @param ModuleRegistered $event
     */
    public function handle(ModuleRegistered $event): void
    {
        if (!config('modulio.log_events', true)) {
            return;
        }

        $context = $event->getLoggingContext();

        Log::channel(config('modulio.log_channel', 'daily'))
            ->info('Module registered successfully', $context);

        // Detailní logování pro debug mode
        // Detailed logging for debug mode
        if (config('modulio.debug_mode', false)) {
            Log::channel(config('modulio.log_channel', 'daily'))
                ->debug('Module registration details', [
                    'module_name' => $event->getModuleName(),
                    'version' => $event->getModuleVersion(),
                    'config_paths' => $event->module->getConfigPaths(),
                    'migration_paths' => $event->module->getMigrationPaths(),
                    'permissions' => $event->module->getPermissions(),
                    'routes' => $event->module->getRoutes(),
                    'metadata' => $event->module->getMetadata(),
                ]);
        }
    }
}


