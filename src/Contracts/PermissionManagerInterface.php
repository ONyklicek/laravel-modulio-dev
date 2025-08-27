<?php

namespace NyonCode\LaravelModulio\Contracts;

use Illuminate\Support\Collection;

/**
 * Kontrakt pro permission managera
 *
 * Definuje rozhraní pro správu oprávnění modulů včetně
 * automatického vytváření a mazání oprávnění.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Permission Manager Contract
 *
 * Defines interface for module permissions management including
 * automatic creation and deletion of permissions.
 */
interface PermissionManagerInterface
{
    /**
     * Vytvoří oprávnění pro modul
     * Creates permissions for module
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function createPermissions(ModuleInterface $module): bool;

    /**
     * Smaže oprávnění pro modul
     * Deletes permissions for module
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function deletePermissions(ModuleInterface $module): bool;

    /**
     * Kontroluje existenci oprávnění
     * Checks permission existence
     *
     * @param string $permission Název oprávnění / Permission name
     * @return bool
     */
    public function permissionExists(string $permission): bool;

    /**
     * Vrací všechna oprávnění pro modul
     * Returns all permissions for module
     *
     * @param ModuleInterface $module
     * @return Collection<string>
     */
    public function getModulePermissions(ModuleInterface $module): Collection;

    /**
     * Synchronizuje oprávnění modulu
     * Synchronizes module permissions
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function syncPermissions(ModuleInterface $module): bool;
}