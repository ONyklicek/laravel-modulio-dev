<?php

namespace NyonCode\LaravelModulio\Navigation;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NavigationItem
{
    protected string $name;
    protected string $label;
    protected string|null $route = null;
    protected string|null $url = null;
    protected string|null $icon = null;
    protected array $permissions = [];
    protected int $order = 100;
    protected Collection $children;
    protected array $attributes = [];
    protected bool $active = false;
    protected NavigationItem|null $parent = null;


    public function __construct(string $name, string|null $label = null)
    {
        $this->name = $name;
        $this->label = $label ?? Str::ucfirst($name);
        $this->children = new Collection();
    }

    /**
     * Set label
     *
     * @param string $label
     * @return $this
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Set route
     *
     * @param string $route
     * @param array $parameters
     * @return $this
     */
    public function route(string $route, array $parameters = []): self
    {
        $this->route = $route;
        $this->attributes['route_parameters'] = $parameters;
        return $this;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return $this
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set permission
     *
     * @param string|array $permissions
     * @return $this
     */
    public function permissions(string|array $permissions): self
    {
        $this->permissions = is_array($permissions) ? $permissions : [$permissions];
        return $this;
    }

    /**
     * Set order
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
     * Set active
     *
     * @param bool $active
     * @return $this
     */
    public function active(bool $active = true): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Check if user can access this menu item
     *
     * @return bool
     */
    public function canAccess(): bool
    {
        if (empty($this->permissions)) {
            return true;
        }

        if (!auth()->check()) {
            return false;
        }

        foreach ($this->permissions as $permission) {
            if (!auth()->user()->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if menu item is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        if ($this->active) {
            return true;
        }

        if ($this->route && request()->routeIs($this->route)) {
            return true;
        }

        if ($this->url && request()->is(trim($this->url, '/'))) {
            return true;
        }

        // Check children
        return $this->children->contains(function (MenuItem $child) {
            return $child->isActive();
        });
    }

    /**
     * |Getters
     * |---
     */
    public function getName(): string { return $this->name; }
    public function getLabel(): string { return $this->label; }
    public function getRoute(): ?string { return $this->route; }
    public function getIcon(): ?string { return $this->icon; }
    public function getPermissions(): array { return $this->permissions; }
    public function getOrder(): int { return $this->order; }
    public function getChildren(): Collection { return $this->children; }
    public function getAttributes(): array { return $this->attributes; }

    /**
     * | Setters for conflict resolution
     * |---
     */
    public function setOrder(int $order): void { $this->order = $order; }

}