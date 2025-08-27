<?php

namespace NyonCode\LaravelModulio\Concerns;

use Illuminate\Support\Collection;
use NyonCode\LaravelModulio\ModuleManager;

/**
 * Trait pro práci s navigation v komponentách
 *
 * Pomocné metody pro vykreslování a filtrování
 * navigačních položek.
 *
 * @package NyonCode\LaravelModulio\Traits
 *
 * ---
 *
 * Navigation Helper Trait
 *
 * Helper methods for rendering and filtering
 * navigation items.
 */
trait HasNavigation
{
    /**
     * Získá autorizované navigační položky
     * Gets authorized navigation items
     *
     * @param string $menuName
     * @param array|null $userPermissions
     * @return Collection
     */
    protected function getAuthorizedNavigation(string $menuName = 'default', ?array $userPermissions = null): Collection
    {
        if ($userPermissions === null && auth()->check()) {
            $userPermissions = auth()->user()->getAllPermissions()->pluck('name')->toArray();
        }

        $navigation = app(ModuleManager::class)->getNavigationItems($menuName);

        return $navigation->filter(function ($item) use ($userPermissions) {
            if (method_exists($item, 'isAuthorized')) {
                return $item->isAuthorized($userPermissions ?? []);
            }
            return true;
        });
    }

    /**
     * Vykreslí navigaci pomocí view
     * Renders navigation using view
     *
     * @param string $menuName
     * @param string|null $template
     * @param array $additionalData
     * @return string
     */
    protected function renderNavigation(string $menuName = 'default', ?string $template = null, array $additionalData = []): string
    {
        $template = $template ?? config('modulio.navigation_template', 'default');
        $templatePath = config("modulio.navigation_templates.{$template}", 'modulio::navigation.default');

        $navigation = $this->getAuthorizedNavigation($menuName);

        return view($templatePath, array_merge([
            'navigation' => $navigation,
            'menuName' => $menuName,
        ], $additionalData))->render();
    }

    /**
     * Získá breadcrumb navigaci
     * Gets breadcrumb navigation
     *
     * @param string $currentRoute
     * @param string $menuName
     * @return Collection
     */
    protected function getBreadcrumbNavigation(string $currentRoute, string $menuName = 'default'): Collection
    {
        $navigation = $this->getAuthorizedNavigation($menuName);
        $breadcrumbs = collect();

        // Najde aktuální položku a sestaví breadcrumb cestu
        // Finds current item and builds breadcrumb path
        $this->buildBreadcrumbPath($navigation, $currentRoute, $breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * Rekurzivně sestaví breadcrumb cestu
     * Recursively builds breadcrumb path
     *
     * @param Collection $items
     * @param string $currentRoute
     * @param Collection $breadcrumbs
     * @param array $path
     * @return bool
     */
    protected function buildBreadcrumbPath(Collection $items, string $currentRoute, Collection &$breadcrumbs, array $path = []): bool
    {
        foreach ($items as $item) {
            $currentPath = array_merge($path, [$item]);

            if ($item->getRoute() === $currentRoute) {
                $breadcrumbs->push(...$currentPath);
                return true;
            }

            // Pokud má položka děti, prohledej je
            // If item has children, search them
            if (method_exists($item, 'getItems') && $item->getItems()->isNotEmpty()) {
                if ($this->buildBreadcrumbPath($item->getItems(), $currentRoute, $breadcrumbs, $currentPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Kontroluje zda je navigační položka aktivní
     * Checks if navigation item is active
     *
     * @param mixed $item
     * @param string|null $currentRoute
     * @return bool
     */
    protected function isNavigationItemActive(mixed $item, ?string $currentRoute = null): bool
    {
        $currentRoute = $currentRoute ?? request()->route()->getName();

        if (method_exists($item, 'getRoute') && $item->getRoute() === $currentRoute) {
            return true;
        }

        // Kontrola aktivních dětí pro skupiny
        // Check active children for groups
        if (method_exists($item, 'getItems')) {
            foreach ($item->getItems() as $childItem) {
                if ($this->isNavigationItemActive($childItem, $currentRoute)) {
                    return true;
                }
            }
        }

        return false;
    }
}