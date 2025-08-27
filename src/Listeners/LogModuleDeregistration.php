<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\ModuleDeregistered;

/**
 * Listener pro logování deregistrace modulů
 *
 * Loguje informace o deregistraci modulu
 * a provádí cleanup operace.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Module Deregistration Logging Listener
 *
 * Logs information about module deregistration
 * and performs cleanup operations.
 */
class LogModuleDeregistration
{
    /**
     * Zpracuje event deregistrace modulu
     * Handle module deregistration event
     *
     * @param ModuleDeregistered $event
     */
    public function handle(ModuleDeregistered $event): void
    {
        if (!config('modulio.log_events', true)) {
            return;
        }

        $context = $event->getLoggingContext();

        Log::channel(config('modulio.log_channel', 'daily'))
            ->warning('Module deregistered', $context);

        // Detailní logování pro audit
        // Detailed logging for audit
        if (config('modulio.debug_mode', false)) {
            Log::channel(config('modulio.log_channel', 'daily'))
                ->debug('Module deregistration details', [
                    'module_name' => $event->getModuleName(),
                    'permissions_removed' => $event->module->getPermissions(),
                    'routes_removed' => $event->module->getRoutes(),
                    'reason' => $event->reason,
                    'deregistered_at' => $event->deregisteredAt->toISOString(),
                ]);
        }
    }
}