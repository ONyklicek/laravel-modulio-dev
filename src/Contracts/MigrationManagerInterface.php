<?php

namespace NyonCode\LaravelModulio\Contracts;

/**
 * Kontrakt pro migration managera
 *
 * Definuje rozhraní pro správu migrací modulů včetně
 * automatického spouštění a rollback operací.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Migration Manager Contract
 *
 * Defines interface for module migrations management including
 * automatic execution and rollback operations.
 */
interface MigrationManagerInterface
{
    /**
     * Spustí migrace pro modul
     * Runs migrations for module
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function runMigrations(ModuleInterface $module): bool;

    /**
     * Provede rollback migrací pro modul
     * Rollbacks migrations for module
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function rollbackMigrations(ModuleInterface $module): bool;

    /**
     * Vrací stav migrací pro modul
     * Returns migration status for module
     *
     * @param ModuleInterface $module
     * @return array<string, mixed>
     */
    public function getMigrationStatus(ModuleInterface $module): array;

    /**
     * Kontroluje zda jsou migrace spuštěny
     * Checks if migrations are executed
     *
     * @param ModuleInterface $module
     * @return bool
     */
    public function areMigrationsExecuted(ModuleInterface $module): bool;
}