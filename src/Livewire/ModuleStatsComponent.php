<?php

namespace NyonCode\LaravelModulio\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Livewire komponenta pro statistiky modulů
 *
 * Zobrazuje přehledové statistiky a grafy
 * o registrovaných modulech.
 *
 * @package NyonCode\LaravelModulio\Livewire
 *
 * ---
 *
 * Livewire Module Statistics Component
 *
 * Displays overview statistics and charts
 * about registered modules.
 */
class ModuleStatsComponent extends Component
{
    /**
     * Časové období pro statistiky
     * Time period for statistics
     *
     * @var string
     */
    public string $period = '30d';

    /**
     * Typ grafu
     * Chart type
     *
     * @var string
     */
    public string $chartType = 'bar';

    /**
     * Zda zobrazit detailní stats
     * Whether to show detailed stats
     *
     * @var bool
     */
    public bool $showDetails = false;

    /**
     * Cache pro statistiky
     * Statistics cache
     *
     * @var array|null
     */
    protected ?array $statsCache = null;

    /**
     * Získá základní statistiky
     * Gets basic statistics
     *
     * @return array
     */
    public function getBasicStats(): array
    {
        if ($this->statsCache === null) {
            $moduleManager = app(ModuleManager::class);
            $modules = $moduleManager->getModules();

            $this->statsCache = [
                'total_modules' => $modules->count(),
                'total_permissions' => $modules->sum(fn($module) => count($module->getPermissions())),
                'total_routes' => $modules->sum(fn($module) => count($module->getRoutes())),
                'modules_with_navigation' => $modules->filter(fn($module) => !empty($module->getNavigations()))->count(),
                'modules_with_migrations' => $modules->filter(fn($module) => !empty($module->getMigrationPaths()))->count(),
                'average_permissions_per_module' => $modules->count() > 0
                    ? round($modules->sum(fn($module) => count($module->getPermissions())) / $modules->count(), 1)
                    : 0,
            ];
        }

        return $this->statsCache;
    }

    /**
     * Získá data pro graf
     * Gets chart data
     *
     * @return array
     */
    public function getChartData(): array
    {
        $moduleManager = app(ModuleManager::class);
        $modules = $moduleManager->getModules();

        return match ($this->chartType) {
            'permissions' => $this->getPermissionsChartData($modules),
            'routes' => $this->getRoutesChartData($modules),
            'versions' => $this->getVersionsChartData($modules),
            default => $this->getBasicChartData($modules),
        };
    }

    /**
     * Základní data pro graf
     * Basic chart data
     *
     * @param Collection $modules
     * @return array
     */
    protected function getBasicChartData(Collection $modules): array
    {
        return [
            'labels' => $modules->pluck('name')->take(10)->toArray(),
            'datasets' => [
                [
                    'label' => 'Oprávnění / Permissions',
                    'data' => $modules->take(10)->map(fn($module) => count($module->getPermissions()))->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                ],
                [
                    'label' => 'Routy / Routes',
                    'data' => $modules->take(10)->map(fn($module) => count($module->getRoutes()))->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                ],
            ],
        ];
    }

    /**
     * Data grafu oprávnění
     * Permissions chart data
     *
     * @param Collection $modules
     * @return array
     */
    protected function getPermissionsChartData(Collection $modules): array
    {
        $permissionCounts = $modules->groupBy(function ($module) {
            $count = count($module->getPermissions());
            return match (true) {
                $count === 0 => 'Bez oprávnění',
                $count <= 3 => '1-3 oprávnění',
                $count <= 6 => '4-6 oprávnění',
                default => '7+ oprávnění',
            };
        })->map->count();

        return [
            'labels' => $permissionCounts->keys()->toArray(),
            'datasets' => [
                [
                    'data' => $permissionCounts->values()->toArray(),
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                    ],
                ],
            ],
        ];
    }

    /**
     * Toggle zobrazení detailů
     * Toggle details display
     */
    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    /**
     * Změna typu grafu
     * Change chart type
     *
     * @param string $type
     */
    public function changeChartType(string $type): void
    {
        $this->chartType = $type;
        $this->dispatch('chart-type-changed', $type);
    }

    /**
     * Refresh statistik
     * Refresh statistics
     */
    public function refreshStats(): void
    {
        $this->statsCache = null;
        $this->dispatch('stats-refreshed');
    }

    /**
     * Vykreslí komponentu
     * Renders component
     *
     * @return View
     */
    public function render(): View
    {
        return view('modulio::livewire.module-stats', [
            'basicStats' => $this->getBasicStats(),
            'chartData' => $this->getChartData(),
            'showDetails' => $this->showDetails,
        ]);
    }
}