<?php

namespace NyonCode\LaravelModulio\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Livewire komponenta pro správu modulů
 *
 * Komponenta pro zobrazení, aktivaci/deaktivaci a správu
 * registrovaných modulů v aplikaci.
 *
 * @package NyonCode\LaravelModulio\Livewire
 *
 * ---
 *
 * Livewire Module Management Component
 *
 * Component for displaying, activating/deactivating and managing
 * registered modules in application.
 */
class ModuleListComponent extends Component
{
    use WithPagination;

    /**
     * Vyhledávací dotaz
     * Search query
     *
     * @var string
     */
    public string $search = '';

    /**
     * Filtr podle stavu
     * Filter by status
     *
     * @var string
     */
    public string $statusFilter = 'all';

    /**
     * Řazení
     * Sorting
     *
     * @var string
     */
    public string $sortBy = 'name';

    /**
     * Směr řazení
     * Sort direction
     *
     * @var string
     */
    public string $sortDirection = 'asc';

    /**
     * Počet položek na stránku
     * Items per page
     *
     * @var int
     */
    public int $perPage = 10;

    /**
     * Zda zobrazit akce
     * Whether to show actions
     *
     * @var bool
     */
    public bool $showActions = true;

    /**
     * Zda zobrazit detaily
     * Whether to show details
     *
     * @var bool
     */
    public bool $showDetails = false;

    /**
     * Vybrané moduly pro hromadné akce
     * Selected modules for bulk actions
     *
     * @var array
     */
    public array $selectedModules = [];

    /**
     * Všechny moduly vybrány
     * All modules selected
     *
     * @var bool
     */
    public bool $selectAll = false;

    /**
     * Posluchače událostí
     * Event listeners
     *
     * @var array
     */
    protected $listeners = [
        'module-updated' => 'refreshModules',
        'refresh-modules' => 'refreshModules',
    ];

    /**
     * Mount komponenty
     * Component mount
     *
     * @param bool $showActions
     * @param bool $showDetails
     * @param int $perPage
     */
    public function mount(
        bool $showActions = true,
        bool $showDetails = false,
        int  $perPage = 10
    ): void
    {
        $this->showActions = $showActions;
        $this->showDetails = $showDetails;
        $this->perPage = $perPage;
    }

    /**
     * Aktualizované vlastnosti
     * Updated properties
     *
     * @var array
     */
    public function getUpdatedPropertyNames(): array
    {
        return ['search', 'statusFilter', 'sortBy', 'sortDirection'];
    }

    /**
     * Aktualizace vyhledávání
     * Search update
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Aktualizace filtru stavu
     * Status filter update
     */
    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Aktualizace výběru všech
     * Select all update
     */
    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedModules = $this->getFilteredModules()->pluck('name')->toArray();
        } else {
            $this->selectedModules = [];
        }
    }

    /**
     * Získá filtrované a seřazené moduly
     * Gets filtered and sorted modules
     *
     * @return Collection
     */
    public function getFilteredModules(): Collection
    {
        $moduleManager = app(ModuleManager::class);
        $modules = $moduleManager->getModules();

        // Vyhledávání
        // Search
        if (!empty($this->search)) {
            $modules = $modules->filter(function ($module) {
                return str_contains(strtolower($module->getName()), strtolower($this->search)) ||
                    str_contains(strtolower($module->getDescription() ?? ''), strtolower($this->search));
            });
        }

        // Filtrování podle stavu
        // Filter by status
        if ($this->statusFilter !== 'all') {
            $modules = $modules->filter(function ($module) {
                return match ($this->statusFilter) {
                    'active' => true, // Všechny registrované moduly jsou aktivní
                    'with_permissions' => !empty($module->getPermissions()),
                    'with_routes' => !empty($module->getRoutes()),
                    'with_navigation' => !empty($module->getNavigations()),
                    default => true,
                };
            });
        }

        // Řazení
        // Sorting
        $modules = $modules->sortBy(function ($module) {
            return match ($this->sortBy) {
                'version' => $module->getVersion() ?? '0.0.0',
                'permissions' => count($module->getPermissions()),
                'routes' => count($module->getRoutes()),
                default => $module->getName(),
            };
        }, SORT_REGULAR, $this->sortDirection === 'desc');

        return $modules;
    }

    /**
     * Řazení podle sloupce
     * Sort by column
     *
     * @param string $column
     */
    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Vyčistí cache modulu
     * Clear module cache
     *
     * @param string $moduleName
     */
    public function clearModuleCache(string $moduleName): void
    {
        app(ModuleManager::class)->clearCache();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => "Cache pro modul {$moduleName} byla vyčištěna."
        ]);
    }

    /**
     * Zobrazí detail modulu
     * Show module details
     *
     * @param string $moduleName
     */
    public function showModuleDetails(string $moduleName): void
    {
        $this->dispatch('show-module-details', $moduleName);
    }

    /**
     * Refresh modulů
     * Refresh modules
     */
    public function refreshModules(): void
    {
        app(ModuleManager::class)->clearCache();
        $this->dispatch('modules-refreshed');
    }

    /**
     * Hromadné vyčištění cache
     * Bulk clear cache
     */
    public function bulkClearCache(): void
    {
        if (empty($this->selectedModules)) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Vyberte alespoň jeden modul.'
            ]);
            return;
        }

        app(ModuleManager::class)->clearCache();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Cache pro vybrané moduly byla vyčištěna.'
        ]);

        $this->selectedModules = [];
        $this->selectAll = false;
    }

    /**
     * Export modulů do CSV
     * Export modules to CSV
     */
    public function exportToCsv(): void
    {
        $modules = $this->getFilteredModules();
        $filename = 'modules_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $csv = "Name,Version,Description,Permissions,Routes\n";

        foreach ($modules as $module) {
            $csv .= sprintf(
                '"%s","%s","%s",%d,%d' . "\n",
                $module->getName(),
                $module->getVersion() ?? 'N/A',
                str_replace('"', '""', $module->getDescription() ?? ''),
                count($module->getPermissions()),
                count($module->getRoutes())
            );
        }

        $this->dispatch('download-file', [
            'filename' => $filename,
            'content' => $csv,
            'type' => 'text/csv'
        ]);
    }

    /**
     * Vykreslí komponentu
     * Renders component
     *
     * @return View
     */
    public function render(): View
    {
        $modules = $this->getFilteredModules();

        // Paginace
        // Pagination
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        $paginatedModules = $modules->slice($offset, $this->perPage);
        $totalModules = $modules->count();

        return view('modulio::livewire.module-list', [
            'modules' => $paginatedModules,
            'totalModules' => $totalModules,
            'showActions' => $this->showActions,
            'showDetails' => $this->showDetails,
        ]);
    }
}