<?php

namespace NyonCode\LaravelModulio;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;
use NyonCode\LaravelModulio\Contracts\ModuleManagerInterface;
use NyonCode\LaravelModulio\Events\ModuleDeregistered;
use NyonCode\LaravelModulio\Events\ModuleRegistered;
use NyonCode\LaravelModulio\Exceptions\DuplicateRouteException;
use NyonCode\LaravelModulio\Exceptions\ModuleNotFoundException;

/**
 * Správce modulů
 *
 * Třída zodpovědná za registraci, deregistraci a správu všech modulů v aplikaci.
 * Poskytuje fluent API pro konfiguraci modulů a automaticky spravuje cache.
 *
 * @package NyonCode\LaravelModulio
 *
 * ---
 *
 * Module Manager
 *
 * Class responsible for registering, deregistering and managing all modules in the application.
 * Provides fluent API for module configuration and automatically manages cache.
 */
class ModuleManager implements ModuleManagerInterface
{
    /**
     * Kolekce registrovaných modulů
     * Collection of registered modules
     *
     * @var Collection<string, ModuleInterface>
     */
    protected Collection $modules;

    /**
     * Cache klíč pro moduly
     * Cache key for modules
     *
     * @var string
     */
    protected string $cacheKey = 'modulio.modules';

    /**
     * Cache TTL v minutách
     * Cache TTL in minutes
     *
     * @var int
     */
    protected int $cacheTtl = 60;

    public function __construct()
    {
        $this->modules = new Collection();
        $this->loadModulesFromCache();
    }

    /**
     * Registruje nový modul
     * Registers a new module
     *
     * @param string $name Název modulu / Module name
     * @return Module
     */
    public function register(string $name): Module
    {
        $module = new Module($name);

        return $module->onRegistered(function (Module $registeredModule) {
            $this->addModule($registeredModule);
        });
    }

    /**
     * Přidá modul do kolekce a cache
     * Adds module to collection and cache
     *
     * @param ModuleInterface $module
     * @throws DuplicateRouteException
     */
    protected function addModule(ModuleInterface $module): void
    {
        // Kontrola duplikátních rout
        // Check for duplicate routes
        $this->validateUniqueRoutes($module);

        $this->modules->put($module->getName(), $module);

        // Automaticky spustí migrace pokud je to povoleno
        // Automatically run migrations if enabled
        if ($module->shouldRunMigrations()) {
            $this->runModuleMigrations($module);
        }

        // Automaticky vytvoří oprávnění
        // Automatically create permissions
        $this->createModulePermissions($module);

        // Aktualizuje cache
        // Update cache
        $this->updateCache();

        // Vyvolá event
        // Fire event
        Event::dispatch(new ModuleRegistered($module));
    }

    /**
     * Odregistruje modul
     * Deregisters a module
     *
     * @param string $name
     * @throws ModuleNotFoundException
     */
    public function deregister(string $name): void
    {
        if (!$this->modules->has($name)) {
            throw new ModuleNotFoundException("Module '{$name}' not found.");
        }

        $module = $this->modules->get($name);

        // Rollback migrací pokud je to povoleno
        // Rollback migrations if enabled
        if ($module->shouldRollbackMigrations()) {
            $this->rollbackModuleMigrations($module);
        }

        $this->modules->forget($name);
        $this->updateCache();

        Event::dispatch(new ModuleDeregistered($module));
    }

    /**
     * Vrací všechny registrované moduly
     * Returns all registered modules
     *
     * @return Collection<string, ModuleInterface>
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    /**
     * Vrací modul podle názvu
     * Returns module by name
     *
     * @param string $name
     * @return ModuleInterface|null
     */
    public function getModule(string $name): ?ModuleInterface
    {
        return $this->modules->get($name);
    }

    /**
     * Kontroluje zda je modul registrován
     * Checks if module is registered
     *
     * @param string $name
     * @return bool
     */
    public function hasModule(string $name): bool
    {
        return $this->modules->has($name);
    }

    /**
     * Načte moduly z cache
     * Load modules from cache
     */
    protected function loadModulesFromCache(): void
    {
        if (config('modulio.cache_enabled', true)) {
            $cachedModules = Cache::remember($this->cacheKey, $this->cacheTtl * 60, function () {
                return $this->modules->toArray();
            });

            if (is_array($cachedModules)) {
                $this->modules = collect($cachedModules)->mapInto(Module::class);
            }
        }
    }

    /**
     * Aktualizuje cache
     * Updates cache
     */
    protected function updateCache(): void
    {
        if (config('modulio.cache_enabled', true)) {
            Cache::put($this->cacheKey, $this->modules->toArray(), $this->cacheTtl * 60);
        }
    }

    /**
     * Smaže cache
     * Clears cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
        Cache::forget('modulio.navigation');
        Cache::forget('modulio.permissions');
    }

    /**
     * Validuje jedinečnost rout
     * Validates route uniqueness
     *
     * @param ModuleInterface $module
     * @throws DuplicateRouteException
     */
    protected function validateUniqueRoutes(ModuleInterface $module): void
    {
        $existingRoutes = $this->modules->flatMap(function ($existingModule) {
            return collect($existingModule->getRoutes())->pluck('name');
        });

        $newRoutes = collect($module->getRoutes())->pluck('name');

        $duplicates = $existingRoutes->intersect($newRoutes);

        if ($duplicates->isNotEmpty()) {
            throw new DuplicateRouteException(
                "Duplicate routes detected: " . $duplicates->implode(', ')
            );
        }
    }

    /**
     * Spustí migrace modulu
     * Run module migrations
     *
     * @param ModuleInterface $module
     */
    protected function runModuleMigrations(ModuleInterface $module): void
    {
        $migrationPaths = $module->getMigrationPaths();

        if (empty($migrationPaths)) {
            return;
        }

        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                \Artisan::call('migrate', [
                    '--path' => $path,
                    '--force' => true
                ]);
            }
        }
    }

    /**
     * Provede rollback migrací modulu
     * Rollback module migrations
     *
     * @param ModuleInterface $module
     */
    protected function rollbackModuleMigrations(ModuleInterface $module): void
    {
        $migrationPaths = $module->getMigrationPaths();

        if (empty($migrationPaths)) {
            return;
        }

        foreach ($migrationPaths as $path) {
            if (is_dir($path)) {
                \Artisan::call('migrate:rollback', [
                    '--path' => $path,
                    '--force' => true
                ]);
            }
        }
    }

    /**
     * Vytvoří oprávnění pro modul
     * Create permissions for module
     *
     * @param ModuleInterface $module
     */
    protected function createModulePermissions(ModuleInterface $module): void
    {
        $permissions = $module->getPermissions();

        if (empty($permissions)) {
            return;
        }

        foreach ($permissions as $permission) {
            if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
                \Spatie\Permission\Models\Permission::create(['name' => $permission]);
            }
        }
    }

    /**
     * Vrací navigační položky pro dané menu
     * Returns navigation items for given menu
     *
     * @param string $menuName
     * @return Collection
     */
    public function getNavigationItems(string $menuName = 'default'): Collection
    {
        $cacheKey = "modulio.navigation.{$menuName}";

        return Cache::remember($cacheKey, $this->cacheTtl * 60, function () use ($menuName) {
            return $this->modules
                ->flatMap(function ($module) use ($menuName) {
                    return $module->getNavigationItems($menuName);
                })
                ->sortBy('order')
                ->groupBy('group')
                ->map(function ($items, $group) {
                    // Řešení konfliktů stejného pořadí - řazení podle abecedy
                    // Resolve same order conflicts - sort alphabetically
                    return $items->groupBy('order')->map(function ($sameOrderItems) {
                        return $sameOrderItems->sortBy('title');
                    })->flatten(1);
                })
                ->flatten(1);
        });
    }

    /**
     * Vrací všechna oprávnění modulů
     * Returns all module permissions
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        $cacheKey = "modulio.permissions";

        return Cache::remember($cacheKey, $this->cacheTtl * 60, function () {
            return $this->modules
                ->flatMap(function ($module) {
                    return $module->getPermissions();
                })
                ->unique()
                ->values();
        });
    }
}