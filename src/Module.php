<?php

namespace NyonCode\LaravelModulio;

use Closure;
use Illuminate\Support\Collection;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;
use NyonCode\LaravelModulio\Navigation\Navigation;

/**
 * Třída reprezentující jednotlivý modul
 *
 * Poskytuje fluent API pro konfiguraci modulu včetně verzí, migrací,
 * navigace, oprávnění a dalších nastavení.
 *
 * @package NyonCode\LaravelModulio
 *
 * ---
 *
 * Class representing individual module
 *
 * Provides fluent API for module configuration including versions, migrations,
 * navigation, permissions and other settings.
 */
class Module implements ModuleInterface
{
    /**
     * Název modulu
     * Module name
     *
     * @var string
     */
    protected string $name;

    /**
     * Verze modulu
     * Module version
     *
     * @var string|null
     */
    protected ?string $version = null;

    /**
     * Popis modulu
     * Module description
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * Cesty k konfiguračním souborům
     * Paths to configuration files
     *
     * @var array<string>
     */
    protected array $configPaths = [];

    /**
     * Cesty k migracím
     * Paths to migrations
     *
     * @var array<string>
     */
    protected array $migrationPaths = [];

    /**
     * Zda spustit migrace automaticky
     * Whether to run migrations automatically
     *
     * @var bool
     */
    protected bool $runMigrations = false;

    /**
     * Zda provést rollback migrací při odinstalaci
     * Whether to rollback migrations on uninstall
     *
     * @var bool
     */
    protected bool $rollbackMigrations = false;

    /**
     * Navigační položky
     * Navigation items
     *
     * @var array<Navigation>
     */
    protected array $navigations = [];

    /**
     * Oprávnění modulu
     * Module permissions
     *
     * @var array<string>
     */
    protected array $permissions = [];

    /**
     * Routy modulu
     * Module routes
     *
     * @var array<array>
     */
    protected array $routes = [];

    /**
     * Callback volaný při registraci
     * Callback called on registration
     *
     * @var Closure|null
     */
    protected ?Closure $onRegisteredCallback = null;

    /**
     * Metadata modulu
     * Module metadata
     *
     * @var array<string, mixed>
     */
    protected array $metadata = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Nastaví verzi modulu
     * Sets module version
     *
     * @param string $version
     * @return self
     */
    public function version(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Automaticky načte verzi z composer.json
     * Automatically loads version from composer.json
     *
     * @param string $composerPath Cesta k composer.json / Path to composer.json
     * @return self
     */
    public function versionFromComposer(string $composerPath): self
    {
        if (file_exists($composerPath)) {
            $composerData = json_decode(file_get_contents($composerPath), true);
            if (isset($composerData['version'])) {
                $this->version = $composerData['version'];
            }
        }
        return $this;
    }

    /**
     * Nastaví popis modulu
     * Sets module description
     *
     * @param string $description
     * @return self
     */
    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Přidá konfigurační soubor
     * Adds configuration file
     *
     * @param string $path
     * @return self
     */
    public function config(string $path): self
    {
        $this->configPaths[] = $path;
        return $this;
    }

    /**
     * Přidá cestu k migracím
     * Adds migration path
     *
     * @param string $path
     * @return self
     */
    public function migrations(string $path): self
    {
        $this->migrationPaths[] = $path;
        return $this;
    }

    /**
     * Nastaví zda spustit migrace automaticky
     * Sets whether to run migrations automatically
     *
     * @param bool $run
     * @return self
     */
    public function runMigrations(bool $run = true): self
    {
        $this->runMigrations = $run;
        return $this;
    }

    /**
     * Nastaví zda provést rollback migrací
     * Sets whether to rollback migrations
     *
     * @param bool $rollback
     * @return self
     */
    public function rollbackMigrations(bool $rollback = true): self
    {
        $this->rollbackMigrations = $rollback;
        return $this;
    }

    /**
     * Přidá navigaci
     * Adds navigation
     *
     * @param Navigation $navigation
     * @return self
     */
    public function nav(Navigation $navigation): self
    {
        $this->navigations[] = $navigation;
        return $this;
    }

    /**
     * Přidá více navigací
     * Adds multiple navigations
     *
     * @param Navigation ...$navigations
     * @return self
     */
    public function navs(Navigation ...$navigations): self
    {
        foreach ($navigations as $navigation) {
            $this->nav($navigation);
        }
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
     * Přidá routu
     * Adds route
     *
     * @param string $name
     * @param string $uri
     * @param string $action
     * @param array $middleware
     * @return self
     */
    public function route(string $name, string $uri, string $action, array $middleware = []): self
    {
        $this->routes[] = [
            'name' => $name,
            'uri' => $uri,
            'action' => $action,
            'middleware' => $middleware
        ];
        return $this;
    }

    /**
     * Nastaví metadata
     * Sets metadata
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function meta(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Registruje callback pro úspěšnou registraci
     * Registers callback for successful registration
     *
     * @param Closure $callback
     * @return self
     */
    public function onRegistered(Closure $callback): self
    {
        $this->onRegisteredCallback = $callback;
        return $this;
    }

    /**
     * Dokončí registraci modulu
     * Completes module registration
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->onRegisteredCallback) {
            call_user_func($this->onRegisteredCallback, $this);
        }
    }

    // Implementace ModuleInterface

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getConfigPaths(): array
    {
        return $this->configPaths;
    }

    public function getMigrationPaths(): array
    {
        return $this->migrationPaths;
    }

    public function shouldRunMigrations(): bool
    {
        return $this->runMigrations;
    }

    public function shouldRollbackMigrations(): bool
    {
        return $this->rollbackMigrations;
    }

    public function getNavigationItems(string $menuName = 'default'): Collection
    {
        return collect($this->navigations)
            ->filter(fn($nav) => $nav->getMenuName() === $menuName)
            ->flatMap(fn($nav) => $nav->getItems());
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Vrací všechny navigace
     * Returns all navigations
     *
     * @return array<Navigation>
     */
    public function getNavigations(): array
    {
        return $this->navigations;
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
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'config_paths' => $this->configPaths,
            'migration_paths' => $this->migrationPaths,
            'run_migrations' => $this->runMigrations,
            'rollback_migrations' => $this->rollbackMigrations,
            'navigations' => array_map(fn($nav) => $nav->toArray(), $this->navigations),
            'permissions' => $this->permissions,
            'routes' => $this->routes,
            'metadata' => $this->metadata,
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
        $module = new static($data['name']);
        $module->version = $data['version'] ?? null;
        $module->description = $data['description'] ?? null;
        $module->configPaths = $data['config_paths'] ?? [];
        $module->migrationPaths = $data['migration_paths'] ?? [];
        $module->runMigrations = $data['run_migrations'] ?? false;
        $module->rollbackMigrations = $data['rollback_migrations'] ?? false;
        $module->permissions = $data['permissions'] ?? [];
        $module->routes = $data['routes'] ?? [];
        $module->metadata = $data['metadata'] ?? [];

        // Rekonstrukce navigací
        // Reconstruct navigations
        if (isset($data['navigations'])) {
            $module->navigations = array_map(
                fn($navData) => Navigation::fromArray($navData),
                $data['navigations']
            );
        }

        return $module;
    }
}