<?php

namespace NyonCode\LaravelModulio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;

/**
 * Event vyvolaný před spuštěním migrací modulu
 *
 * Umožňuje provést akce před spuštěním migrací
 * nebo potenciálně zrušit spuštění migrací.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * Before Module Migrations Event
 *
 * Allows performing actions before running migrations
 * or potentially canceling migration execution.
 */
class BeforeModuleMigrations
{
    use Dispatchable, SerializesModels;

    /**
     * Modul pro který se spustí migrace
     * Module for which migrations will run
     *
     * @var ModuleInterface
     */
    public ModuleInterface $module;

    /**
     * Cesty k migračním souborům
     * Paths to migration files
     *
     * @var array<string>
     */
    public array $migrationPaths;

    /**
     * Zda pokračovat ve spuštění migrací
     * Whether to continue with migration execution
     *
     * @var bool
     */
    public bool $shouldContinue = true;

    /**
     * @param ModuleInterface $module
     * @param array $migrationPaths
     */
    public function __construct(ModuleInterface $module, array $migrationPaths)
    {
        $this->module = $module;
        $this->migrationPaths = $migrationPaths;
    }

    /**
     * Zastaví spuštění migrací
     * Stops migration execution
     */
    public function stopExecution(): void
    {
        $this->shouldContinue = false;
    }
}
