<?php

namespace NyonCode\LaravelModulio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;

/**
 * Event vyvolaný při registraci modulu
 *
 * Tento event je vyvolán pokaždé, když je modul úspěšně registrován
 * do modulárního systému. Umožňuje další zpracování nebo logování.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * Module Registered Event
 *
 * This event is fired whenever a module is successfully registered
 * into the modular system. Allows additional processing or logging.
 */
class ModuleRegistered
{
    use Dispatchable, SerializesModels;

    /**
     * Registrovaný modul
     * Registered module
     *
     * @var ModuleInterface
     */
    public ModuleInterface $module;

    /**
     * Čas registrace
     * Registration time
     *
     * @var \Carbon\Carbon
     */
    public \Carbon\Carbon $registeredAt;

    /**
     * Dodatečné informace
     * Additional information
     *
     * @var array<string, mixed>
     */
    public array $context;

    /**
     * @param ModuleInterface $module Registrovaný modul
     * @param array $context Dodatečné informace o registraci
     */
    public function __construct(ModuleInterface $module, array $context = [])
    {
        $this->module = $module;
        $this->registeredAt = now();
        $this->context = $context;
    }

    /**
     * Vrací název modulu
     * Returns module name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module->getName();
    }

    /**
     * Vrací verzi modulu
     * Returns module version
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string
    {
        return $this->module->getVersion();
    }

    /**
     * Vrací informace o eventu pro logování
     * Returns event information for logging
     *
     * @return array<string, mixed>
     */
    public function getLoggingContext(): array
    {
        return [
            'event' => 'module.registered',
            'module_name' => $this->getModuleName(),
            'module_version' => $this->getModuleVersion(),
            'registered_at' => $this->registeredAt->toISOString(),
            'permissions_count' => count($this->module->getPermissions()),
            'routes_count' => count($this->module->getRoutes()),
            'context' => $this->context,
        ];
    }
}