<?php

namespace NyonCode\LaravelModulio\Commands;

use Illuminate\Console\Command;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Příkaz pro vyčištění cache modulů
 *
 * Vyčistí všechny cache klíče související s modulárním systémem
 * a poskytne možnosti pro selektivní čištění.
 *
 * @package NyonCode\LaravelModulio\Commands
 *
 * ---
 *
 * Command to clear module cache
 *
 * Clears all cache keys related to modular system
 * and provides options for selective clearing.
 */
class ModulioClearCacheCommand extends Command
{
    /**
     * Název a podpis příkazu
     * Command name and signature
     *
     * @var string
     */
    protected $signature = 'modulio:clear-cache
                           {--modules : Clear only module cache}
                           {--navigation : Clear only navigation cache}
                           {--permissions : Clear only permissions cache}
                           {--all : Clear all Modulio cache (default)}
                           {--force : Force clear without confirmation}';

    /**
     * Popis příkazu
     * Command description
     *
     * @var string
     */
    protected $description = 'Clear Modulio cache / Vyčistí cache modulárního systému';

    /**
     * Instance module managera
     * Module manager instance
     *
     * @var ModuleManager
     */
    protected ModuleManager $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
    }

    /**
     * Spuštění příkazu
     * Execute command
     *
     * @return int
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Are you sure you want to clear the cache?')) {
            $this->info('Cache clearing cancelled.');
            return 0;
        }

        $clearedItems = [];

        if ($this->option('modules') || (!$this->hasSpecificOption() && $this->option('all'))) {
            $this->clearModulesCache();
            $clearedItems[] = 'modules';
        }

        if ($this->option('navigation') || (!$this->hasSpecificOption() && $this->option('all'))) {
            $this->clearNavigationCache();
            $clearedItems[] = 'navigation';
        }

        if ($this->option('permissions') || (!$this->hasSpecificOption() && $this->option('all'))) {
            $this->clearPermissionsCache();
            $clearedItems[] = 'permissions';
        }

        if (empty($clearedItems)) {
            $this->clearAllCache();
            $clearedItems[] = 'all modulio cache';
        }

        $this->info('✅ Cache cleared: ' . implode(', ', $clearedItems));

        return 0;
    }

    /**
     * Kontroluje zda byla zadána specifická možnost
     * Checks if specific option was provided
     *
     * @return bool
     */
    protected function hasSpecificOption(): bool
    {
        return $this->option('modules') ||
            $this->option('navigation') ||
            $this->option('permissions');
    }

    /**
     * Vyčistí cache modulů
     * Clears modules cache
     */
    protected function clearModulesCache(): void
    {
        cache()->forget(config('modulio.cache_prefix', 'modulio') . '.modules');
        $this->line('🗑️  Modules cache cleared');
    }

    /**
     * Vyčistí cache navigace
     * Clears navigation cache
     */
    protected function clearNavigationCache(): void
    {
        $prefix = config('modulio.cache_prefix', 'modulio');
        $menus = config('modulio.available_menus', ['default', 'admin']);

        foreach ($menus as $menu => $label) {
            cache()->forget("$prefix.navigation.$menu");
        }

        $this->line('🗑️  Navigation cache cleared');
    }

    /**
     * Vyčistí cache oprávnění
     * Clears permissions cache
     */
    protected function clearPermissionsCache(): void
    {
        $prefix = config('modulio.cache_prefix', 'modulio');
        cache()->forget("$prefix.permissions");
        $this->line('🗑️  Permissions cache cleared');
    }

    /**
     * Vyčistí všechnu cache
     * Clears all cache
     */
    protected function clearAllCache(): void
    {
        $this->moduleManager->clearCache();
        $this->line('🗑️  All Modulio cache cleared');
    }
}