<?php

namespace NyonCode\LaravelModulio\Navigation;

/**
 * Navigační položka
 *
 * Reprezentuje jednu položku v navigačním menu.
 * Podporuje ikony, routy, oprávnění a řazení.
 *
 * @package NyonCode\LaravelModulio\Navigation
 *
 * ---
 *
 * Navigation Item
 *
 * Represents one item in navigation menu.
 * Supports icons, routes, permissions and ordering.
 */
class NavigationItem
{
    /**
     * Název položky
     * Item title
     *
     * @var string
     */
    protected string $title;

    /**
     * Ikona položky
     * Item icon
     *
     * @var string|null
     */
    protected ?string $icon = null;

    /**
     * Název routy
     * Route name
     *
     * @var string|null
     */
    protected ?string $route = null;

    /**
     * URL
     *
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * Pořadí položky
     * Item order
     *
     * @var int
     */
    protected int $order = 100;

    /**
     * Vyžadovaná oprávnění
     * Required permissions
     *
     * @var array<string>
     */
    protected array $permissions = [];

    /**
     * CSS třídy
     * CSS classes
     *
     * @var array<string>
     */
    protected array $classes = [];

    /**
     * Další atributy
     * Additional attributes
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Badge/štítek
     * Badge/label
     *
     * @var string|null
     */
    protected ?string $badge = null;

    /**
     * Skupina do které položka patří
     * Group the item belongs to
     *
     * @var string|null
     */
    protected ?string $group = null;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * Vytvoří novou instanci
     * Creates new instance
     *
     * @param string $title
     * @return static
     */
    public static function make(string $title): static
    {
        return new static($title);
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
     * Nastaví routu
     * Sets route
     *
     * @param string $route
     * @return self
     */
    public function route(string $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Nastaví URL
     * Sets URL
     *
     * @param string $url
     * @return self
     */
    public function url(string $url): self
    {
        $this->url = $url;
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
     * Přidá oprávnění
     * Adds permission
     *
     * @param string $permission
     * @return self
     */
    public function permission(string $permission): self
    {
        $this->permissions[] = $permission;
        return $this;
    }

    /**
     * Přidá více oprávnění
     * Adds multiple permissions
     *
     * @param array<string> $permissions
     * @return self
     */
    public function permissions(array $permissions): self
    {
        $this->permissions = array_merge($this->permissions, $permissions);
        return $this;
    }

    /**
     * Přidá CSS třídu
     * Adds CSS class
     *
     * @param string $class
     * @return self
     */
    public function class(string $class): self
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     * Přidá více CSS tříd
     * Adds multiple CSS classes
     *
     * @param array<string> $classes
     * @return self
     */
    public function classes(array $classes): self
    {
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    /**
     * Nastaví atribut
     * Sets attribute
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function attribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Nastaví více atributů
     * Sets multiple attributes
     *
     * @param array<string, mixed> $attributes
     * @return self
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Nastaví badge/štítek
     * Sets badge/label
     *
     * @param string $badge
     * @return self
     */
    public function badge(string $badge): self
    {
        $this->badge = $badge;
        return $this;
    }

    /**
     * Nastaví skupinu
     * Sets group
     *
     * @param string $group
     * @return self
     */
    public function group(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Kontroluje oprávnění uživatele
     * Checks user permissions
     *
     * @param array $userPermissions
     * @return bool
     */
    public function isAuthorized(array $userPermissions = []): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        return !empty(array_intersect($this->permissions, $userPermissions));
    }

    /**
     * Vrací URL položky
     * Returns item URL
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route) {
            return route($this->route);
        }

        return null;
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

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function getGroup(): ?string
    {
        return $this->group;
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
            'type' => 'item',
            'title' => $this->title,
            'icon' => $this->icon,
            'route' => $this->route,
            'url' => $this->url,
            'order' => $this->order,
            'permissions' => $this->permissions,
            'classes' => $this->classes,
            'attributes' => $this->attributes,
            'badge' => $this->badge,
            'group' => $this->group,
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
        $item = new static($data['title']);
        $item->icon = $data['icon'] ?? null;
        $item->route = $data['route'] ?? null;
        $item->url = $data['url'] ?? null;
        $item->order = $data['order'] ?? 100;
        $item->permissions = $data['permissions'] ?? [];
        $item->classes = $data['classes'] ?? [];
        $item->attributes = $data['attributes'] ?? [];
        $item->badge = $data['badge'] ?? null;
        $item->group = $data['group'] ?? null;

        return $item;
    }
}