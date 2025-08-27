<?php

namespace NyonCode\LaravelModulio\Navigation;

use Closure;
use Illuminate\Support\Collection;

/**
 * Navigace pro modul
 *
 * Třída reprezentující navigační menu pro konkrétní oblast (admin, front-end, atd.)
 * Podporuje hierarchické menu s vnořenými položkami a skupinami.
 *
 * @package NyonCode\LaravelModulio\Navigation
 *
 * ---
 *
 * Navigation for module
 *
 * Class representing navigation menu for specific area (admin, front-end, etc.)
 * Supports hierarchical menu with nested items and groups.
 */
class Navigation
{
    /**
     * Název menu
     * Menu name
     *
     * @var string
     */
    protected string $menuName;

    /**
     * Kolekce navigačních položek
     * Collection of navigation items
     *
     * @var Collection<NavigationItem|NavigationGroup>
     */
    protected Collection $items;

    /**
     * @param string $menuName Název menu (admin, default, atd.)
     * @param Closure|array|null $items Closure vracející položky nebo přímo pole položek
     */
    public function __construct(string $menuName, Closure|array|null $items = null)
    {
        $this->menuName = $menuName;
        $this->items = new Collection();

        if ($items instanceof Closure) {
            $this->items = collect($items());
        } elseif (is_array($items)) {
            $this->items = collect($items);
        }
    }

    /**
     * Vytvoří novou instanci navigace
     * Creates new navigation instance
     *
     * @param string $menuName
     * @param Closure|array|null $items
     * @return static
     */
    public static function make(string $menuName, Closure|array|null $items = null): static
    {
        return new static($menuName, $items);
    }

    /**
     * Přidá navigační položku
     * Adds navigation item
     *
     * @param NavigationItem|NavigationGroup $item
     * @return self
     */
    public function addItem(NavigationItem|NavigationGroup $item): self
    {
        $this->items->push($item);
        return $this;
    }

    /**
     * Vrací název menu
     * Returns menu name
     *
     * @return string
     */
    public function getMenuName(): string
    {
        return $this->menuName;
    }

    /**
     * Vrací všechny položky
     * Returns all items
     *
     * @return Collection<NavigationItem|NavigationGroup>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Vrací pouze navigační položky (ne skupiny)
     * Returns only navigation items (not groups)
     *
     * @return Collection<NavigationItem>
     */
    public function getNavigationItems(): Collection
    {
        return $this->items->filter(fn($item) => $item instanceof NavigationItem);
    }

    /**
     * Vrací pouze navigační skupiny
     * Returns only navigation groups
     *
     * @return Collection<NavigationGroup>
     */
    public function getNavigationGroups(): Collection
    {
        return $this->items->filter(fn($item) => $item instanceof NavigationGroup);
    }

    /**
     * Vrací všechny položky včetně vnořených
     * Returns all items including nested ones
     *
     * @return Collection<NavigationItem>
     */
    public function getFlattenedItems(): Collection
    {
        return $this->items->flatMap(function ($item) {
            if ($item instanceof NavigationGroup) {
                return $item->getItems();
            }
            return [$item];
        });
    }

    /**
     * Filtruje položky podle oprávnění uživatele
     * Filters items by user permissions
     *
     * @param array $userPermissions
     * @return Collection
     */
    public function getAuthorizedItems(array $userPermissions = []): Collection
    {
        return $this->items->filter(function ($item) use ($userPermissions) {
            if ($item instanceof NavigationItem) {
                return $item->isAuthorized($userPermissions);
            }
            if ($item instanceof NavigationGroup) {
                return $item->hasAuthorizedItems($userPermissions);
            }
            return true;
        });
    }

    /**
     * Serializace pro cache
     * Serialization for cache
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'menu_name' => $this->menuName,
            'items' => $this->items->map(fn($item) => $item->toArray())->toArray(),
        ];
    }

    /**
     * Deserializace z cache
     * Deserialization from cache
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $navigation = new static($data['menu_name']);

        foreach ($data['items'] as $itemData) {
            if (isset($itemData['type']) && $itemData['type'] === 'group') {
                $navigation->addItem(NavigationGroup::fromArray($itemData));
            } else {
                $navigation->addItem(NavigationItem::fromArray($itemData));
            }
        }

        return $navigation;
    }
}