<?php

namespace NyonCode\LaravelModulio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;

/**
 * Event vyvolaný po spuštění migrací modulu
 *
 * Informuje o úspěšném nebo neúspěšném spuštění migrací
 * a umožňuje dodatečné zpracování.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * After Module Migrations Event
 *
 * Informs about successful or failed migration execution
 * and allows additional processing.
 */
class AfterModuleMigrations
{
    use Dispatchable, SerializesModels;

    /**
     * Modul pro který se spustily migrace
     * Module for which migrations were executed
     *
     * @var ModuleInterface
     */
    public ModuleInterface $module;

    /**
     * Zda byly migrace úspěšné
     * Whether migrations were successful
     *
     * @var bool
     */
    public bool $successful;

    /**
     * Chybové zprávy (pokud nějaké jsou)
     * Error messages (if any)
     *
     * @var array<string>
     */
    public array $errors;

    /**
     * Spuštěné migrace
     * Executed migrations
     *
     * @var array<string>
     */
    public array $executedMigrations;

    /**
     * @param ModuleInterface $module
     * @param bool $successful
     * @param array $errors
     * @param array $executedMigrations
     */
    public function __construct(
        ModuleInterface $module,
        bool            $successful,
        array           $errors = [],
        array           $executedMigrations = []
    )
    {
        $this->module = $module;
        $this->successful = $successful;
        $this->errors = $errors;
        $this->executedMigrations = $executedMigrations;
    }
}