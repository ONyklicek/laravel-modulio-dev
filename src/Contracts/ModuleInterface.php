<?php

namespace NyonCode\LaravelModulio\Contracts;

use Illuminate\Support\Collection;

/**
 * Kontrakt pro jednotlivý modul
 *
 * Definuje rozhraní pro modul včetně jeho metadat, navigace,
 * oprávnění a dalších vlastností.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Module Interface
 *
 * Defines interface for module including its metadata, navigation,
 * permissions and other properties.
 */
interface ModuleInterface
{
    /**
     * Vrací název modulu
     * Returns module name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Vrací verzi modulu
     * Returns module version
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Vrací popis modulu
     * Returns module description
     *
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * Vrací cesty ke konfiguračním souborům
     * Returns paths to configuration files
     *
     * @return array<string>
     */
    public function getConfigPaths(): array;

    /**
     * Vrací cesty k migracím
     * Returns paths to migrations
     *
     * @return array<string>
     */
    public function getMigrationPaths(): array;

    /**
     * Vrací zda spustit migrace automaticky
     * Returns whether to run migrations automatically
     *
     * @return bool
     */
    public function shouldRunMigrations(): bool;

    /**
     * Vrací zda provést rollback migrací při odinstalaci
     * Returns whether to rollback migrations on uninstall
     *
     * @return bool
     */
    public function shouldRollbackMigrations(): bool;

    /**
     * Vrací navigační položky pro dané menu
     * Returns navigation items for given menu
     *
     * @param string $menuName Název menu / Menu name
     * @return Collection
     */
    public function getNavigationItems(string $menuName = 'default'): Collection;

    /**
     * Vrací oprávnění modulu
     * Returns module permissions
     *
     * @return array<string>
     */
    public function getPermissions(): array;

    /**
     * Vrací routy modulu
     * Returns module routes
     *
     * @return array<array>
     */
    public function getRoutes(): array;

    /**
     * Vrací metadata modulu
     * Returns module metadata
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    /**
     * Vrací konkrétní metadata
     * Returns specific metadata
     *
     * @param string $key Klíč metadata / Metadata key
     * @param mixed $default Výchozí hodnota / Default value
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed;

    /**
     * Serializace pro cache
     * Serialization for cache
     *
     * @return array
     */
    public function toArray(): array;
}