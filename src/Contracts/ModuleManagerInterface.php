<?php

namespace NyonCode\LaravelModulio\Contracts;

use Illuminate\Support\Collection;
use NyonCode\LaravelModulio\Module;

/**
 * Kontrakt pro správce modulů
 *
 * Definuje rozhraní pro registraci, deregistraci a správu modulů
 * v modulárním systému Laravel aplikace.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Module Manager Contract
 *
 * Defines interface for registering, deregistering and managing modules
 * in Laravel application modular system.
 */
interface ModuleManagerInterface
{
    /**
     * Registruje nový modul
     * Registers a new module
     *
     * @param string $name Název modulu / Module name
     * @return Module
     */
    public function register(string $name): Module;

    /**
     * Odregistruje modul
     * Deregisters a module
     *
     * @param string $name Název modulu / Module name
     * @throws \NyonCode\LaravelModulio\Exceptions\ModuleNotFoundException
     */
    public function deregister(string $name): void;

    /**
     * Vrací všechny registrované moduly
     * Returns all registered modules
     *
     * @return Collection<string, ModuleInterface>
     */
    public function getModules(): Collection;

    /**
     * Vrací modul podle názvu
     * Returns module by name
     *
     * @param string $name Název modulu / Module name
     * @return ModuleInterface|null
     */
    public function getModule(string $name): ?ModuleInterface;

    /**
     * Kontroluje zda je modul registrován
     * Checks if module is registered
     *
     * @param string $name Název modulu / Module name
     * @return bool
     */
    public function hasModule(string $name): bool;

    /**
     * Vrací navigační položky pro dané menu
     * Returns navigation items for given menu
     *
     * @param string $menuName Název menu / Menu name
     * @return Collection
     */
    public function getNavigationItems(string $menuName = 'default'): Collection;

    /**
     * Vrací všechna oprávnění modulů
     * Returns all module permissions
     *
     * @return Collection<string>
     */
    public function getAllPermissions(): Collection;

    /**
     * Smaže cache
     * Clears cache
     */
    public function clearCache(): void;
}
