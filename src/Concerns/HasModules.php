<?php

namespace NyonCode\LaravelModulio\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Trait pro práci s moduly v controllerech
 *
 * Poskytuje pomocné metody pro práci s modulárním systémem
 * přímo v controllerech a dalších třídách.
 *
 * @package NyonCode\LaravelModulio\Traits
 *
 * ---
 *
 * Module Helper Trait
 *
 * Provides helper methods for working with modular system
 * directly in controllers and other classes.
 */
trait HasModules
{
    /**
     * Získá instanci module managera
     * Gets module manager instance
     *
     * @return ModuleManager
     */
    protected function getModuleManager(): ModuleManager
    {
        return app(ModuleManager::class);
    }

    /**
     * Kontroluje existenci modulu
     * Checks module existence
     *
     * @param string $moduleName
     * @return bool
     */
    protected function hasModule(string $moduleName): bool
    {
        return $this->getModuleManager()->hasModule($moduleName);
    }

    /**
     * Získá konkrétní modul
     * Gets specific module
     *
     * @param string $moduleName
     * @return ModuleInterface|null
     */
    protected function getModule(string $moduleName): ?ModuleInterface
    {
        return $this->getModuleManager()->getModule($moduleName);
    }

    /**
     * Získá všechny registrované moduly
     * Gets all registered modules
     *
     * @return Collection
     */
    protected function getModules(): Collection
    {
        return $this->getModuleManager()->getModules();
    }

    /**
     * Získá navigační položky pro menu
     * Gets navigation items for menu
     *
     * @param string $menuName
     * @return Collection
     */
    protected function getNavigationItems(string $menuName = 'default'): Collection
    {
        return $this->getModuleManager()->getNavigationItems($menuName);
    }

    /**
     * Kontroluje oprávnění pro modul
     * Checks permission for module
     *
     * @param string $permission
     * @return bool
     */
    protected function hasModulePermission(string $permission): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->can($permission);
    }

    /**
     * Ověří oprávnění nebo vyhodí výjimku
     * Authorize permission or throw exception
     *
     * @param string $permission
     * @param string|null $message
     * @throws AuthorizationException
     */
    protected function authorizeModulePermission(string $permission, ?string $message = null): void
    {
        if (!$this->hasModulePermission($permission)) {
            abort(403, $message ?: "Insufficient permissions for: $permission");
        }
    }

    /**
     * Získá metadata modulu
     * Gets module metadata
     *
     * @param string $moduleName
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    protected function getModuleMetadata(string $moduleName, ?string $key = null, mixed $default = null): mixed
    {
        $module = $this->getModule($moduleName);

        if (!$module) {
            return $default;
        }

        if ($key === null) {
            return $module->getMetadata();
        }

        return $module->getMeta($key, $default);
    }
}