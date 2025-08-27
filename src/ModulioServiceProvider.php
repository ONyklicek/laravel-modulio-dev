<?php

namespace NyonCode\LaravelModulio;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NyonCode\LaravelModulio\Commands\ModulioClearCacheCommand;
use NyonCode\LaravelModulio\Commands\ModulioInstallCommand;
use NyonCode\LaravelModulio\Commands\ModulioListCommand;

/**
 * Aktualizovaný hlavní service provider pro Laravel Modulio
 *
 * Vylepšená auto-discovery logika, která preferuje modulio-providers
 * před standardními Laravel providers pro čistší řešení.
 *
 * @package NyonCode\LaravelModulio
 *
 * ---
 *
 * Updated main service provider for Laravel Modulio
 *
 * Enhanced auto-discovery logic that prefers modulio-providers
 * over standard Laravel providers for cleaner solution.
 */
class ModulioServiceProvider extends ServiceProvider
{
    /**
     * Registruje služby
     * Register services
     */
    public function register(): void
    {
        parent::register();

        // Registrace konfigurace
        // Register configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/modulio.php',
            'modulio'
        );

        // Registrace hlavního service provideru
        // Register main service provider
        $this->app->singleton(ModuleManager::class, function () {
            return new ModuleManager();
        });

        // Alias pro snadnější použití
        // Alias for easier usage
        $this->app->alias(ModuleManager::class, 'modulio');

        // Registrace blade direktiv
        // Register blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Bootstrap služby
     * Bootstrap services
     */
    public function boot(): void
    {
        parent::boot();

        $this->publishAssets();
        $this->loadViews();
        $this->registerCommands();
        $this->loadRoutes();
        $this->registerLivewireComponents();
        $this->registerMiddleware();

        // Registrace event service provideru
        // Register event service provider
        $this->app->register(\NyonCode\LaravelModulio\Providers\ModulioEventServiceProvider::class);

        // Auto-discovery modulů - vylepšená logika
        // Auto-discovery of modules - enhanced logic
        $this->discoverModules();
    }

    /**
     * Publikuje assety
     * Publish assets
     */
    protected function publishAssets(): void
    {
        // Publikace konfigurace
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/modulio.php' => config_path('modulio.php'),
        ], 'modulio-config');

        // Publikace migrací
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'modulio-migrations');

        // Publikace views
        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/modulio'),
        ], 'modulio-views');

        // Publikace assets
        // Publish assets
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('vendor/modulio'),
        ], 'modulio-assets');
    }

    /**
     * Načte views
     * Load views
     */
    protected function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'modulio');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Registruje příkazy
     * Register commands
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModulioListCommand::class,
                ModulioClearCacheCommand::class,
                ModulioInstallCommand::class,
            ]);
        }
    }

    /**
     * Načte routy
     * Load routes
     */
    protected function loadRoutes(): void
    {
        if (!config('modulio.load_routes', true)) {
            return;
        }

        Route::group([
            'prefix' => config('modulio.route_prefix', 'modulio'),
            'middleware' => config('modulio.route_middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        // Načtení API rout
        // Load API routes
        if (config('modulio.load_api_routes', false)) {
            Route::group([
                'prefix' => 'api/' . config('modulio.route_prefix', 'modulio'),
                'middleware' => config('modulio.api_middleware', ['api']),
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            });
        }
    }

    /**
     * Registruje Livewire komponenty
     * Register Livewire components
     */
    protected function registerLivewireComponents(): void
    {
        if (!class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('modulio.navigation',
            \NyonCode\LaravelModulio\Livewire\NavigationComponent::class
        );

        \Livewire\Livewire::component('modulio.module-list',
            \NyonCode\LaravelModulio\Livewire\ModuleListComponent::class
        );

        \Livewire\Livewire::component('modulio.module-stats',
            \NyonCode\LaravelModulio\Livewire\ModuleStatsComponent::class
        );

        \Livewire\Livewire::component('modulio.module-install',
            \NyonCode\LaravelModulio\Livewire\ModuleInstallComponent::class
        );
    }

    /**
     * Registruje middleware
     * Register middleware
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware(
            'modulio.permission',
            \NyonCode\LaravelModulio\Middleware\ModulioPermissionMiddleware::class
        );

        $router->aliasMiddleware(
            'modulio.rate-limit',
            \NyonCode\LaravelModulio\Middleware\ModulioRateLimitMiddleware::class
        );
    }

    /**
     * Registruje Blade direktivy
     * Register Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        \Blade::directive('modulioNavigation', function ($expression) {
            return "<?php echo app('modulio')->renderNavigation({$expression}); ?>";
        });

        \Blade::directive('modulioPermission', function ($expression) {
            return "<?php if(\\NyonCode\\LaravelModulio\\Facades\\Modulio::hasPermission({$expression})): ?>";
        });

        \Blade::directive('endModulioPermission', function () {
            return "<?php endif; ?>";
        });

        \Blade::directive('modulioModule', function ($expression) {
            return "<?php if(app('modulio')->hasModule({$expression})): ?>";
        });

        \Blade::directive('endModulioModule', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * VYLEPŠENÁ Auto-discovery modulů z nainstalovaných balíčků
     * ENHANCED Auto-discovery of modules from installed packages
     */
    protected function discoverModules(): void
    {
        if (!config('modulio.auto_discovery', true)) {
            return;
        }

        $composerFile = base_path('composer.json');
        $composerLockFile = base_path('composer.lock');

        if (!file_exists($composerFile) || !file_exists($composerLockFile)) {
            return;
        }

        $composerData = json_decode(file_get_contents($composerLockFile), true);

        if (!isset($composerData['packages'])) {
            return;
        }

        foreach ($composerData['packages'] as $package) {
            $this->discoverPackageModules($package);
        }
    }

    /**
     * VYLEPŠENÁ Objeví moduly v konkrétním balíčku
     * ENHANCED Discover modules in specific package
     *
     * @param array $package
     */
    protected function discoverPackageModules(array $package): void
    {
        if (!isset($package['extra']['laravel'])) {
            return;
        }

        $laravelExtra = $package['extra']['laravel'];

        // PRIORITA 1: Zkus modulio-providers (preferované řešení)
        // PRIORITY 1: Try modulio-providers (preferred solution)
        if (isset($laravelExtra['modulio-providers'])) {
            $this->registerModulioProviders($laravelExtra['modulio-providers']);
            return; // Pokud najde modulio-providers, nepokračuje dále
        }

        // PRIORITA 2: Zkus standardní providers, ale pouze ty které implementují ModuleRegistrarInterface
        // PRIORITY 2: Try standard providers, but only those that implement ModuleRegistrarInterface
        if (isset($laravelExtra['providers'])) {
            $this->registerStandardProvidersWithModulioSupport($laravelExtra['providers']);
        }
    }

    /**
     * Registruje modulio-specific providers
     * Register modulio-specific providers
     *
     * @param array|string $providers
     */
    protected function registerModulioProviders($providers): void
    {
        if (!is_array($providers)) {
            $providers = [$providers];
        }

        foreach ($providers as $providerClass) {
            if (!class_exists($providerClass)) {
                continue;
            }

            try {
                $provider = new $providerClass($this->app);

                if (method_exists($provider, 'registerModule')) {
                    $moduleManager = app(ModuleManager::class);
                    $provider->registerModule($moduleManager);

                    if (config('modulio.debug_mode', false)) {
                        \Log::info("Modulio: Registered module via {$providerClass}");
                    }
                }
            } catch (\Exception $e) {
                if (config('modulio.debug_mode', false)) {
                    \Log::warning("Modulio: Failed to register module via {$providerClass}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Registruje standardní providers s Modulio podporou
     * Register standard providers with Modulio support
     *
     * @param array|string $providers
     */
    protected function registerStandardProvidersWithModulioSupport($providers): void
    {
        if (!is_array($providers)) {
            $providers = [$providers];
        }

        foreach ($providers as $providerClass) {
            if (!class_exists($providerClass)) {
                continue;
            }

            try {
                // Kontrola zda provider implementuje ModuleRegistrarInterface
                // Check if provider implements ModuleRegistrarInterface
                $reflection = new \ReflectionClass($providerClass);

                if (!$reflection->implementsInterface(\NyonCode\LaravelModulio\Contracts\ModuleRegistrarInterface::class)) {
                    continue; // Přeskočí providery bez Modulio podpory
                }

                $provider = new $providerClass($this->app);

                if (method_exists($provider, 'registerModule')) {
                    $moduleManager = app(ModuleManager::class);
                    $provider->registerModule($moduleManager);

                    if (config('modulio.debug_mode', false)) {
                        \Log::info("Modulio: Registered module via standard provider {$providerClass}");
                    }
                }
            } catch (\Exception $e) {
                if (config('modulio.debug_mode', false)) {
                    \Log::warning("Modulio: Failed to register module via standard provider {$providerClass}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Poskytuje služby
     * Provides services
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            ModuleManager::class,
            'modulio',
        ];
    }

    /**
     * Vrací název balíčku
     * Returns package name
     *
     * @return string
     */
    protected function getPackageName(): string
    {
        return 'modulio';
    }

    /**
     * Vrací cestu k balíčku
     * Returns package path
     *
     * @return string
     */
    protected function getPackagePath(): string
    {
        return __DIR__ . '/..';
    }
}