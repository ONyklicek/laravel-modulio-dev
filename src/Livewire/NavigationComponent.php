<?php

namespace NyonCode\LaravelModulio\Livewire;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Livewire komponenta pro zobrazení navigace
 *
 * Reaktivní komponenta pro vykreslování navigačních menu
 * s podporou oprávnění a hierarchie.
 *
 * @package NyonCode\LaravelModulio\Livewire
 *
 * ---
 *
 * Livewire Navigation Component
 *
 * Reactive component for rendering navigation menus
 * with permission and hierarchy support.
 */
class NavigationComponent extends Component
{
    /**
     * Název menu k zobrazení
     * Menu name to display
     *
     * @var string
     */
    public string $menuName = 'default';

    /**
     * Template pro vykreslení
     * Template for rendering
     *
     * @var string
     */
    public string $template = 'default';

    /**
     * Zda zobrazit pouze aktivní položky
     * Whether to show only active items
     *
     * @var bool
     */
    public bool $activeOnly = false;

    /**
     * CSS třídy pro kontejner
     * CSS classes for container
     *
     * @var string
     */
    public string $containerClass = '';

    /**
     * Dodatečné parametry
     * Additional parameters
     *
     * @var array
     */
    public array $options = [];

    /**
     * Cache navigačních položek
     * Navigation items cache
     *
     * @var Collection|null
     */
    protected $navigationItems = null;

    /**
     * Mount komponenty
     * Component mount
     *
     * @param string $menu
     * @param string $template
     * @param bool $activeOnly
     * @param string $containerClass
     * @param array $options
     */
    public function mount(
        string $menu = 'default',
        string $template = 'default',
        bool   $activeOnly = false,
        string $containerClass = '',
        array  $options = []
    ): void
    {
        $this->menuName = $menu;
        $this->template = $template;
        $this->activeOnly = $activeOnly;
        $this->containerClass = $containerClass;
        $this->options = $options;
    }

    /**
     * Získá navigační položky
     * Gets navigation items
     *
     * @return Collection
     */
    public function getNavigationItems(): ?Collection
    {
        if ($this->navigationItems === null) {
            $moduleManager = app(ModuleManager::class);
            $items = $moduleManager->getNavigationItems($this->menuName);

            // Filtrování podle oprávnění
            // Filter by permissions
            if (auth()->check()) {
                $userPermissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();

                $items = $items->filter(function ($item) use ($userPermissions) {
                    if (method_exists($item, 'isAuthorized')) {
                        return $item->isAuthorized($userPermissions);
                    }
                    return true;
                });
            }

            // Filtrování pouze aktivních
            // Filter only active items
            if ($this->activeOnly) {
                $currentRoute = request()->route()->getName();
                $items = $items->filter(function ($item) use ($currentRoute) {
                    return $this->isItemActive($item, $currentRoute);
                });
            }

            $this->navigationItems = $items;
        }

        return $this->navigationItems;
    }

    /**
     * Kontroluje zda je položka aktivní
     * Checks if item is active
     *
     * @param mixed $item
     * @param string $currentRoute
     * @return bool
     */
    protected function isItemActive($item, string $currentRoute): bool
    {
        if (method_exists($item, 'getRoute') && $item->getRoute() === $currentRoute) {
            return true;
        }

        // Kontrola aktivních dětí pro skupiny
        // Check active children for groups
        if (method_exists($item, 'getItems')) {
            foreach ($item->getItems() as $childItem) {
                if ($this->isItemActive($childItem, $currentRoute)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Refresh navigace
     * Refresh navigation
     */
    public function refreshNavigation(): void
    {
        $this->navigationItems = null;
        $this->dispatch('navigation-refreshed');
    }

    /**
     * Toggle kolapse skupiny
     * Toggle group collapse
     *
     * @param string $groupTitle
     */
    public function toggleGroup(string $groupTitle): void
    {
        $this->dispatch('group-toggled', $groupTitle);
    }

    /**
     * Vykreslí komponentu
     * Renders component
     *
     * @return View
     */
    public function render()
    {
        $templatePath = config("modulio.navigation_templates.{$this->template}", 'modulio::livewire.navigation');

        return view($templatePath, [
            'items' => $this->getNavigationItems(),
            'menuName' => $this->menuName,
            'containerClass' => $this->containerClass,
            'options' => $this->options,
        ]);
    }
}