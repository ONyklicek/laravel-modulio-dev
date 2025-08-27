<?php

namespace NyonCode\LaravelModulio\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;

/**
 * Event vyvolaný při deregistraci modulu
 *
 * Tento event je vyvolán při odstraňování modulu ze systému.
 * Umožňuje provedení cleanup operací nebo logování.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * Module Deregistered Event
 *
 * This event is fired when removing a module from the system.
 * Allows performing cleanup operations or logging.
 */
class ModuleDeregistered
{
    use Dispatchable, SerializesModels;

    /**
     * Deregistrovaný modul
     * Deregistered module
     *
     * @var ModuleInterface
     */
    public ModuleInterface $module;

    /**
     * Čas deregistrace
     * Deregistration time
     *
     * @var Carbon
     */
    public Carbon $deregisteredAt;

    /**
     * Důvod deregistrace
     * Deregistration reason
     *
     * @var string|null
     */
    public ?string $reason;

    /**
     * Dodatečné informace
     * Additional information
     *
     * @var array<string, mixed>
     */
    public array $context;

    /**
     * @param ModuleInterface $module Deregistrovaný modul
     * @param string|null $reason Důvod deregistrace
     * @param array $context Dodatečné informace
     */
    public function __construct(ModuleInterface $module, ?string $reason = null, array $context = [])
    {
        $this->module = $module;
        $this->deregisteredAt = now();
        $this->reason = $reason;
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
     * Vrací informace o eventu pro logování
     * Returns event information for logging
     *
     * @return array<string, mixed>
     */
    public function getLoggingContext(): array
    {
        return [
            'event' => 'module.deregistered',
            'module_name' => $this->getModuleName(),
            'module_version' => $this->module->getVersion(),
            'deregistered_at' => $this->deregisteredAt->toISOString(),
            'reason' => $this->reason,
            'context' => $this->context,
        ];
    }
}