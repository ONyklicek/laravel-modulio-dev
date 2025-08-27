<?php

namespace NyonCode\LaravelModulio\Concerns;

use NyonCode\LaravelModulio\ModuleManager;

/**
 * Trait pro validaci modulů
 *
 * Poskytuje metody pro validaci konfigurace
 * a integrity modulů.
 *
 * @package NyonCode\LaravelModulio\Traits
 *
 * ---
 *
 * Module Validation Trait
 *
 * Provides methods for validating configuration
 * and module integrity.
 */
trait ValidatesModules
{
    /**
     * Validuje konfiguraci modulu
     * Validates module configuration
     *
     * @param array $config
     * @return array Pole chyb / Error array
     */
    protected function validateModuleConfiguration(array $config): array
    {
        $errors = [];

        // Povinné pole
        // Required fields
        $requiredFields = ['name', 'version'];
        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Validace názvu modulu
        // Module name validation
        if (!empty($config['name']) && !preg_match('/^[a-z0-9\-_]+$/', $config['name'])) {
            $errors[] = "Invalid module name format. Use only lowercase letters, numbers, hyphens and underscores.";
        }

        // Validace verze
        // Version validation
        if (!empty($config['version']) && !preg_match('/^\d+\.\d+\.\d+/', $config['version'])) {
            $errors[] = "Invalid version format. Use semantic versioning (e.g., 1.0.0).";
        }

        // Validace cest
        // Path validation
        if (isset($config['migration_paths'])) {
            foreach ($config['migration_paths'] as $path) {
                if (!is_dir($path)) {
                    $errors[] = "Migration path does not exist: {$path}";
                }
            }
        }

        if (isset($config['config_paths'])) {
            foreach ($config['config_paths'] as $path) {
                if (!file_exists($path)) {
                    $errors[] = "Configuration file does not exist: {$path}";
                }
            }
        }

        return $errors;
    }

    /**
     * Validuje oprávnění modulu
     * Validates module permissions
     *
     * @param array $permissions
     * @return array
     */
    protected function validateModulePermissions(array $permissions): array
    {
        $errors = [];

        foreach ($permissions as $permission) {
            if (!is_string($permission) || empty(trim($permission))) {
                $errors[] = "Invalid permission format. Permissions must be non-empty strings.";
                continue;
            }

            if (!preg_match('/^[a-z0-9.\-_]+$/', $permission)) {
                $errors[] = "Invalid permission name: {$permission}. Use only lowercase letters, numbers, dots, hyphens and underscores.";
            }
        }

        return $errors;
    }

    /**
     * Kontroluje kompatibilitu verze PHP
     * Checks PHP version compatibility
     *
     * @param string $requiredVersion
     * @return bool
     */
    protected function checkPhpCompatibility(string $requiredVersion): bool
    {
        return version_compare(PHP_VERSION, $requiredVersion, '>=');
    }

    /**
     * Kontroluje kompatibilitu Laravel verze
     * Checks Laravel version compatibility
     *
     * @param array $supportedVersions
     * @return bool
     */
    protected function checkLaravelCompatibility(array $supportedVersions): bool
    {
        $currentVersion = app()->version();

        foreach ($supportedVersions as $version) {
            if (version_compare($currentVersion, $version, '>=')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kontroluje závislosti modulu
     * Checks module dependencies
     *
     * @param array $dependencies
     * @return array Missing dependencies
     */
    protected function checkModuleDependencies(array $dependencies): array
    {
        $missing = [];
        $moduleManager = app(ModuleManager::class);

        foreach ($dependencies as $dependency => $version) {
            if (!$moduleManager->hasModule($dependency)) {
                $missing[] = "{$dependency} (required version: {$version})";
                continue;
            }

            $module = $moduleManager->getModule($dependency);
            if ($module && version_compare($module->getVersion(), $version, '<')) {
                $missing[] = "{$dependency} (current: {$module->getVersion()}, required: {$version})";
            }
        }

        return $missing;
    }
}