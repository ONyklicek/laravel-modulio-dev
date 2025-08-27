<?php

namespace NyonCode\LaravelModulio\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Příkaz pro výpis všech registrovaných modulů
 *
 * Zobrazí tabulku s informacemi o všech registrovaných modulech
 * včetně verzí, oprávnění a dalších metadat.
 *
 * @package NyonCode\LaravelModulio\Commands
 *
 * ---
 *
 * Command to list all registered modules
 *
 * Displays table with information about all registered modules
 * including versions, permissions and other metadata.
 */
class ModulioListCommand extends Command
{
    /**
     * Název a podpis příkazu
     * Command name and signature
     *
     * @var string
     */
    protected $signature = 'modulio:list 
                           {--format=table : Output format (table, json, csv)}
                           {--filter=* : Filter modules by name pattern}
                           {--show-permissions : Show module permissions}
                           {--show-routes : Show module routes}
                           {--show-metadata : Show module metadata}
                           {--sort=name : Sort by (name, version, order)}';

    /**
     * Popis příkazu
     * Command description
     *
     * @var string
     */
    protected $description = 'List all registered modules with their information / Vypíše všechny registrované moduly s jejich informacemi';

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
        $modules = $this->getFilteredModules();

        if ($modules->isEmpty()) {
            $this->warn('No modules found matching the criteria / Nebyly nalezeny žádné moduly odpovídající kritériím.');
            return 0;
        }

        $format = $this->option('format');

        match ($format) {
            'json' => $this->outputJson($modules),
            'csv' => $this->outputCsv($modules),
            default => $this->outputTable($modules),
        };

        return 0;
    }

    /**
     * Získá filtrované moduly
     * Gets filtered modules
     *
     * @return Collection
     */
    protected function getFilteredModules(): Collection
    {
        $modules = $this->moduleManager->getModules();
        $filters = $this->option('filter');
        $sortBy = $this->option('sort');

        // Filtrování
        // Filtering
        if (!empty($filters)) {
            $modules = $modules->filter(function ($module) use ($filters) {
                foreach ($filters as $filter) {
                    if (str_contains(strtolower($module->getName()), strtolower($filter))) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Řazení
        // Sorting
        $modules = match ($sortBy) {
            'version' => $modules->sortBy(fn($module) => $module->getVersion() ?? '0.0.0'),
            'order' => $modules->sortBy(fn($module) => $module->getMeta('order', 100)),
            default => $modules->sortBy('name'),
        };

        return $modules;
    }

    /**
     * Výstup jako tabulka
     * Table output
     *
     * @param Collection $modules
     */
    protected function outputTable(Collection $modules): void
    {
        $headers = ['Name', 'Version', 'Description', 'Permissions', 'Routes'];
        $rows = [];

        foreach ($modules as $module) {
            $row = [
                $module->getName(),
                $module->getVersion() ?? 'N/A',
                Str::limit($module->getDescription() ?? 'No description', 50),
                count($module->getPermissions()),
                count($module->getRoutes()),
            ];

            // Rozšířené informace
            // Extended information
            if ($this->option('show-permissions') && !empty($module->getPermissions())) {
                $row[] = implode(', ', array_slice($module->getPermissions(), 0, 3)) .
                    (count($module->getPermissions()) > 3 ? '...' : '');
            }

            if ($this->option('show-metadata')) {
                $metadata = $module->getMetadata();
                $metaString = '';
                if (!empty($metadata)) {
                    $metaString = collect($metadata)
                        ->take(2)
                        ->map(fn($value, $key) => "{$key}: {$value}")
                        ->implode(', ');
                }
                $row[] = $metaString;
            }

            $rows[] = $row;
        }

        // Přidání dodatečných hlaviček
        // Adding additional headers
        if ($this->option('show-permissions')) {
            $headers[] = 'Top Permissions';
        }
        if ($this->option('show-metadata')) {
            $headers[] = 'Metadata';
        }

        $this->table($headers, $rows);

        $this->info("\nTotal modules: " . $modules->count());
        $this->info("Total permissions: " . $modules->sum(fn($module) => count($module->getPermissions())));
        $this->info("Total routes: " . $modules->sum(fn($module) => count($module->getRoutes())));
    }

    /**
     * JSON výstup
     * JSON output
     *
     * @param Collection $modules
     */
    protected function outputJson(Collection $modules): void
    {
        $data = $modules->map(function ($module) {
            $moduleData = [
                'name' => $module->getName(),
                'version' => $module->getVersion(),
                'description' => $module->getDescription(),
                'permissions_count' => count($module->getPermissions()),
                'routes_count' => count($module->getRoutes()),
            ];

            if ($this->option('show-permissions')) {
                $moduleData['permissions'] = $module->getPermissions();
            }

            if ($this->option('show-routes')) {
                $moduleData['routes'] = $module->getRoutes();
            }

            if ($this->option('show-metadata')) {
                $moduleData['metadata'] = $module->getMetadata();
            }

            return $moduleData;
        })->values();

        $this->line(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * CSV výstup
     * CSV output
     *
     * @param Collection $modules
     */
    protected function outputCsv(Collection $modules): void
    {
        $headers = ['Name', 'Version', 'Description', 'Permissions Count', 'Routes Count'];

        if ($this->option('show-metadata')) {
            $headers[] = 'Author';
            $headers[] = 'Homepage';
        }

        $this->line(implode(',', $headers));

        foreach ($modules as $module) {
            $row = [
                $this->escapeCsv($module->getName()),
                $this->escapeCsv($module->getVersion() ?? 'N/A'),
                $this->escapeCsv($module->getDescription() ?? ''),
                count($module->getPermissions()),
                count($module->getRoutes()),
            ];

            if ($this->option('show-metadata')) {
                $row[] = $this->escapeCsv($module->getMeta('author', ''));
                $row[] = $this->escapeCsv($module->getMeta('homepage', ''));
            }

            $this->line(implode(',', $row));
        }
    }

    /**
     * Escapuje hodnotu pro CSV
     * Escapes value for CSV
     *
     * @param string $value
     * @return string
     */
    protected function escapeCsv(string $value): string
    {
        return '"' . str_replace('"', '""', $value) . '"';
    }
}