<?php

namespace NyonCode\LaravelModulio\Providers;

use Exception;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Log;
use NyonCode\LaravelModulio\Events\AfterModuleMigrations;
use NyonCode\LaravelModulio\Events\ModuleCacheUpdated;
use NyonCode\LaravelModulio\Events\ModuleDeregistered;
use NyonCode\LaravelModulio\Events\ModulePermissionsCreated;
use NyonCode\LaravelModulio\Events\ModuleRegistered;
use NyonCode\LaravelModulio\Listeners\CleanupModulePermissions;
use NyonCode\LaravelModulio\Listeners\ClearModuleCache;
use NyonCode\LaravelModulio\Listeners\ClearNavigationCache;
use NyonCode\LaravelModulio\Listeners\IndexModuleMigrations;
use NyonCode\LaravelModulio\Listeners\LogModuleDeregistration;
use NyonCode\LaravelModulio\Listeners\LogModuleRegistration;
use NyonCode\LaravelModulio\Listeners\SendModuleRegistrationNotification;
use NyonCode\LaravelModulio\Listeners\SyncPermissionsWithRoles;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Event Service Provider pro Modulio
 *
 * Registruje všechny event listenery pro modulární systém
 * a umožňuje jejich konfiguraci.
 *
 * @package NyonCode\LaravelModulio\Providers
 *
 * ---
 *
 * Event Service Provider for Modulio
 *
 * Registers all event listeners for modular system
 * and allows their configuration.
 */
class ModulioEventServiceProvider extends ServiceProvider
{
    /**
     * Mapování eventů na listenery
     * Event to listener mappings
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Registrace modulu
        // Module registration
        ModuleRegistered::class => [
            LogModuleRegistration::class,
            ClearNavigationCache::class,
            SendModuleRegistrationNotification::class,
        ],

        // Deregistrace modulu
        // Module deregistration
        ModuleDeregistered::class => [
            LogModuleDeregistration::class,
            ClearModuleCache::class,
            CleanupModulePermissions::class,
        ],

        // Vytvoření oprávnění
        // Permissions created
        ModulePermissionsCreated::class => [
            SyncPermissionsWithRoles::class,
        ],

        // Dokončení migrací
        // Migrations completed
        AfterModuleMigrations::class => [
            IndexModuleMigrations::class,
        ],
    ];

    /**
     * Bootstrap eventů
     * Bootstrap events
     */
    public function boot(): void
    {
        parent::boot();

        // Registrace dynamických listenerů z konfigurace
        // Register dynamic listeners from configuration
        $this->registerDynamicListeners();

        // Registrace custom eventů
        // Register custom events
        $this->registerCustomEvents();
    }

    /**
     * Registruje dynamické listenery z konfigurace
     * Register dynamic listeners from configuration
     */
    protected function registerDynamicListeners(): void
    {
        $customListeners = config('modulio.event_listeners', []);

        foreach ($customListeners as $eventClass => $listeners) {
            foreach ($listeners as $listenerClass) {
                if (class_exists($listenerClass)) {
                    $this->app['events']->listen($eventClass, $listenerClass);
                }
            }
        }
    }

    /**
     * Registruje custom eventy
     * Register custom events
     */
    protected function registerCustomEvents(): void
    {
        // Event pro změnu cache
        // Cache change event
        $this->app['events']->listen('cache:cleared', function ($cacheName) {
            if (str_contains($cacheName, 'modulio')) {
                event(new ModuleCacheUpdated('clear', [$cacheName]));
            }
        });

        // Event pro Laravel application boot
        // Laravel application boot event
        $this->app['events']->listen('bootstrapped: Illuminate\Foundation\Bootstrap\BootProviders', function () {
            // Trigger po dokončení bootování všech providerů
            // Trigger after all providers are booted
            if (config('modulio.auto_discovery', true)) {
                $this->discoverModulesAfterBoot();
            }
        });
    }

    /**
     * Objeví moduly po dokončení bootování
     * Discover modules after boot completion
     */
    protected function discoverModulesAfterBoot(): void
    {
        // Tato metoda může být použita pro post-boot discovery
        // This method can be used for post-boot discovery

        try {
            $moduleManager = app(ModuleManager::class);

            if (config('modulio.debug_mode', false)) {
                Log::info('Modulio auto-discovery completed', [
                    'modules_count' => $moduleManager->getModules()->count(),
                    'modules' => $moduleManager->getModules()->keys()->toArray(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Modulio post-boot discovery failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Určuje zda mají být eventy automaticky objeveny
     * Determine if events should be automatically discovered
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return config('modulio.auto_discover_events', false);
    }

    /**
     * Získá cestu pro auto-discovery eventů
     * Get path for event auto-discovery
     *
     * @return string
     */
    protected function discoverEventsWithin(): string
    {
        return app_path('Listeners');
    }
}