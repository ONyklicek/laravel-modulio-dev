<?php

namespace NyonCode\LaravelModulio\Contracts;

use NyonCode\LaravelModulio\ModuleManager;

/**
 * Kontrakt pro registraci modulů ve service providerech
 *
 * Poskytuje standardizované rozhraní pro registraci modulů
 * přímo ve service providerech třetích stran.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Module Registration Contract
 *
 * Provides standardized interface for module registration
 * directly in third-party service providers.
 */
interface ModuleRegistrarInterface
{
    /**
     * Registruje modul
     * Registers module
     *
     * @param ModuleManager $moduleManager
     * @return void
     */
    public function registerModule(ModuleManager $moduleManager): void;
}