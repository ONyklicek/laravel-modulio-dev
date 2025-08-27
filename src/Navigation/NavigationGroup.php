<?php

namespace NyonCode\LaravelModulio\Navigation;

use Closure;
use Illuminate\Support\Collection;

/**
 * Navigační skupina
 *
 * Reprezentuje skupinu navigačních položek s možností vnořování.
 * Podporuje hierarchické menu strukture.
 *
 * @package NyonCode\LaravelModulio\Navigation
 *
 * ---
 *
 * Navigation Group
 *
 * Represents group of navigation items with nesting capability.
 * Supports hierarchical menu structures.
 */
class NavigationGroup
{
    /**
     * Název skupiny
     * Group title
     *
     * @var string
     */
    protected string $title;

    /**
     * Ikona skupiny
     * Group icon
     *
     * @var string|null
     */
    protected ?string $icon = null;

    /**
     * Pořadí skupiny
     * Group order
     *
     * @var int
     */
    protected int $order = 100;

    /**
     * Kolapse/rozbalení skupiny
     * Group collapse/expand state
     *
     * @var bool
     */
    protected bool $collapsed = false;

    /**
     * Položky ve skupině
     * Items in group
     *
     * @var Collection<NavigationItem>
     */
    protected Collection $items;

    /**
     * @param string $title
     * @param Closure|array|null $items
     */
    public function __construct(string $title, Closure|array|null $items = null)
    {
        $this->title = $title;
        $this->items = new Collection();

        if ($items instanceof Closure) {
            $this->items = collect($items());
        } elseif (is_array($items)) {
            $this->items = collect($items);
        }
    }

    /**
     * Vytvoří novou instanci skupiny
     * Creates new group instance
     *
     * @param string $title
     * @param Closure|array|null $items
     * @return static
     */
    public static function make(string $title, Closure|array|null $items = null): static
    {
        return new static($title, $items);
    }

    /**
     * Nastaví ikonu
     * Sets icon
     *
     * @param string $icon
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Nastaví pořadí
     * Sets order
     *
     * @param int $order
     * @return self
     */
    public function order(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Nastaví stav kolapsu
     * Sets collapse state
     *
     * @param bool $collapsed
     * @return self
     */
    public function collapsed(bool $collapsed = true): self
    {
        $this->collapsed = $collapsed;
        return $this;
    }

    /**
     * Přidá položku do skupiny
     * Adds item to group
     *
     * @param NavigationItem $item
     * @return self
     */
    public function addItem(NavigationItem $item): self
    {
        $this->items->push($item);
        return $this;
    }

    /**
     * Kontroluje zda má skupina autorizované položky
     * Checks if group has authorized items
     *
     * @param array $userPermissions
     * @return bool
     */
    public function hasAuthorizedItems(array $userPermissions = []): bool
    {
        return $this->items->some(fn($item) => $item->isAuthorized($userPermissions));
    }

    /**
     * Vrací pouze autorizované položky
     * Returns only authorized items
     *
     * @param array $userPermissions
     * @return Collection<NavigationItem>
     */
    public function getAuthorizedItems(array $userPermissions = []): Collection
    {
        return $this->items->filter(fn($item) => $item->isAuthorized($userPermissions));
    }

    // Gettery

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function isCollapsed(): bool
    {
        return $this->collapsed;
    }

    public function getItems(): Collection
    {
        return $this->items;
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
            'type' => 'group',
            'title' => $this->title,
            'icon' => $this->icon,
            'order' => $this->order,
            'collapsed' => $this->collapsed,
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
        $group = new static($data['title']);
        $group->icon = $data['icon'] ?? null;
        $group->order = $data['order'] ?? 100;
        $group->collapsed = $data['collapsed'] ?? false;

        foreach ($data['items'] as $itemData) {
            $group->addItem(NavigationItem::fromArray($itemData));
        }

        return $group;
    }
}
