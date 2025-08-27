<?php

namespace NyonCode\LaravelModulio\Facades;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;
use NyonCode\LaravelModulio\Module;

/**
 * Facade pro ModuleManager
 *
 * Poskytuje snadný přístup k funkcionalitě ModuleManageru
 * přes statické metody.
 *
 * @package NyonCode\LaravelModulio\Facades
 *
 * ---
 *
 * ModuleManager Facade
 *
 * Provides easy access to ModuleManager functionality
 * via static methods.
 *
 * @method static Module register(string $name)
 * @method static void deregister(string $name)
 * @method static Collection getModules()
 * @method static ModuleInterface|null getModule(string $name)
 * @method static bool hasModule(string $name)
 * @method static Collection getNavigationItems(string $menuName = 'default')
 * @method static Collection getAllPermissions()
 * @method static void clearCache()
 */
class Modulio extends Facade
{
    /**
     * Vrací název bindingu ve service containeru
     * Returns binding name in service container
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'modulio';
    }

    /**
     * Vykreslí navigaci
     * Renders navigation
     *
     * @param string $menuName
     * @param string|null $template
     * @param array $options
     * @return string
     */
    public static function renderNavigation(string $menuName = 'default', ?string $template = null, array $options = []): string
    {
        $template = $template ?? config('modulio.navigation_template', 'default');
        $templatePath = config("modulio.navigation_templates.{$template}", 'modulio::navigation.default');

        $moduleManager = app(static::getFacadeAccessor());
        $navigation = $moduleManager->getNavigationItems($menuName);

        // Filtrování podle oprávnění
        // Filter by permissions
        if (auth()->check()) {
            $userPermissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();

            $navigation = $navigation->filter(function ($item) use ($userPermissions) {
                if (method_exists($item, 'isAuthorized')) {
                    return $item->isAuthorized($userPermissions);
                }
                return true;
            });
        }

        return view($templatePath, array_merge([
            'navigation' => $navigation,
            'menuName' => $menuName,
        ], $options))->render();
    }

    /**
     * Kontroluje oprávnění uživatele
     * Checks user permission
     *
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can($permission);
    }

    /**
     * Získá statistiky modulů
     * Gets module statistics
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        $moduleManager = app(static::getFacadeAccessor());
        $modules = $moduleManager->getModules();

        return [
            'total_modules' => $modules->count(),
            'total_permissions' => $modules->sum(fn($module) => count($module->getPermissions())),
            'total_routes' => $modules->sum(fn($module) => count($module->getRoutes())),
            'modules_with_navigation' => $modules->filter(fn($module) => !empty($module->getNavigations()))->count(),
            'modules_with_migrations' => $modules->filter(fn($module) => !empty($module->getMigrationPaths()))->count(),
            'cache_enabled' => config('modulio.cache_enabled', true),
            'auto_discovery' => config('modulio.auto_discovery', true),
        ];
    }

    /**
     * Ověří integritu modulů
     * Verify module integrity
     *
     * @return array
     */
    public static function verifyIntegrity(): array
    {
        $moduleManager = app(static::getFacadeAccessor());
        $modules = $moduleManager->getModules();
        $issues = [];

        foreach ($modules as $module) {
            $moduleIssues = [];

            // Kontrola migračních cest
            // Check migration paths
            foreach ($module->getMigrationPaths() as $path) {
                if (!is_dir($path)) {
                    $moduleIssues[] = "Migration path does not exist: {$path}";
                }
            }

            // Kontrola konfiguračních cest
            // Check configuration paths
            foreach ($module->getConfigPaths() as $path) {
                if (!file_exists($path)) {
                    $moduleIssues[] = "Configuration file does not exist: {$path}";
                }
            }

            // Kontrola duplikátních oprávnění
            // Check duplicate permissions
            $permissions = $module->getPermissions();
            $duplicates = array_diff_assoc($permissions, array_unique($permissions));
            if (!empty($duplicates)) {
                $moduleIssues[] = "Duplicate permissions found: " . implode(', ', $duplicates);
            }

            if (!empty($moduleIssues)) {
                $issues[$module->getName()] = $moduleIssues;
            }
        }

        return $issues;
    }

    /**
     * Exportuje konfiguraci modulů
     * Exports module configuration
     *
     * @param array $moduleNames Názvy modulů k exportu (prázdné = všechny)
     * @return array
     */
    public static function exportConfiguration(array $moduleNames = []): array
    {
        $moduleManager = app(static::getFacadeAccessor());
        $modules = $moduleManager->getModules();

        if (!empty($moduleNames)) {
            $modules = $modules->filter(fn($module) => in_array($module->getName(), $moduleNames));
        }

        return $modules->mapWithKeys(function ($module) {
            return [$module->getName() => [
                'name' => $module->getName(),
                'version' => $module->getVersion(),
                'description' => $module->getDescription(),
                'permissions' => $module->getPermissions(),
                'routes' => $module->getRoutes(),
                'metadata' => $module->getMetadata(),
                'config_paths' => $module->getConfigPaths(),
                'migration_paths' => $module->getMigrationPaths(),
                'should_run_migrations' => $module->shouldRunMigrations(),
                'should_rollback_migrations' => $module->shouldRollbackMigrations(),
            ]];
        })->toArray();
    }

    /**
     * Importuje konfiguraci modulů
     * Imports module configuration
     *
     * @param array $configuration
     * @return array Výsledek importu / Import results
     */
    public static function importConfiguration(array $configuration): array
    {
        $moduleManager = app(static::getFacadeAccessor());
        $results = [
            'imported' => [],
            'failed' => [],
            'skipped' => []
        ];

        foreach ($configuration as $moduleName => $config) {
            try {
                if ($moduleManager->hasModule($moduleName)) {
                    $results['skipped'][] = $moduleName . ' (already exists)';
                    continue;
                }

                $module = $moduleManager->register($moduleName)
                    ->version($config['version'] ?? '1.0.0')
                    ->description($config['description'] ?? '');

                if (!empty($config['permissions'])) {
                    $module->permissions($config['permissions']);
                }

                if (!empty($config['config_paths'])) {
                    foreach ($config['config_paths'] as $path) {
                        $module->config($path);
                    }
                }

                if (!empty($config['migration_paths'])) {
                    foreach ($config['migration_paths'] as $path) {
                        $module->migrations($path);
                    }
                }

                if (isset($config['should_run_migrations'])) {
                    $module->runMigrations($config['should_run_migrations']);
                }

                if (isset($config['metadata'])) {
                    foreach ($config['metadata'] as $key => $value) {
                        $module->meta($key, $value);
                    }
                }

                $module->register();
                $results['imported'][] = $moduleName;

            } catch (Exception $e) {
                $results['failed'][] = $moduleName . ' (' . $e->getMessage() . ')';
            }
        }

        return $results;
    }
}
