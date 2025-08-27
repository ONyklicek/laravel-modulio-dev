<?php

namespace NyonCode\LaravelModulio\Listeners;

use DB;
use Illuminate\Support\Facades\Log;
use NyonCode\LaravelModulio\Events\AfterModuleMigrations;
use Schema;

/**
 * Listener pro indexování migrací
 *
 * Udržuje index spuštěných migrací pro jednotlivé moduly
 * pro účely rollback a správy.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Migration Index Listener
 *
 * Maintains index of executed migrations for individual modules
 * for rollback and management purposes.
 */
class IndexModuleMigrations
{
    /**
     * Zpracuje event dokončení migrací
     * Handle migrations completed event
     *
     * @param AfterModuleMigrations $event
     */
    public function handle(AfterModuleMigrations $event): void
    {
        if (!$event->successful) {
            return;
        }

        try {
            $this->storeMigrationIndex($event);
        } catch (\Exception $e) {
            Log::error('Failed to index module migrations', [
                'module' => $event->module->getName(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Uloží index migrací
     * Store migration index
     *
     * @param AfterModuleMigrations $event
     */
    protected function storeMigrationIndex(AfterModuleMigrations $event): void
    {
        if (!$this->migrationTableExists()) {
            $this->createMigrationTable();
        }

        foreach ($event->executedMigrations as $migration) {
            DB::table('modulio_migrations')->updateOrInsert([
                'module_name' => $event->module->getName(),
                'migration' => $migration,
            ], [
                'executed_at' => now(),
                'batch' => $this->getNextBatchNumber(),
            ]);
        }

        Log::info('Module migrations indexed', [
            'module' => $event->module->getName(),
            'migrations_count' => count($event->executedMigrations),
        ]);
    }

    /**
     * Kontroluje existenci tabulky migrací
     * Check migration table exists
     *
     * @return bool
     */
    protected function migrationTableExists(): bool
    {
        return Schema::hasTable('modulio_migrations');
    }

    /**
     * Vytvoří tabulku pro indexování migrací
     * Create migration index table
     */
    protected function createMigrationTable(): void
    {
        Schema::create('modulio_migrations', function ($table) {
            $table->id();
            $table->string('module_name');
            $table->string('migration');
            $table->integer('batch');
            $table->timestamp('executed_at');

            $table->unique(['module_name', 'migration']);
            $table->index(['module_name', 'batch']);
        });
    }

    /**
     * Získá další batch číslo
     * Get next batch number
     *
     * @return int
     */
    protected function getNextBatchNumber(): int
    {
        return DB::table('modulio_migrations')->max('batch') + 1;
    }
}