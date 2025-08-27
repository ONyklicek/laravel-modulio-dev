<?php

namespace NyonCode\LaravelModulio\Contracts;

/**
 * Kontrakt pro cache adaptér
 *
 * Definuje rozhraní pro různé typy cache úložišť
 * používané modulárním systémem.
 *
 * @package NyonCode\LaravelModulio\Contracts
 *
 * ---
 *
 * Cache Adapter Contract
 *
 * Defines interface for different types of cache storages
 * used by modular system.
 */
interface CacheAdapterInterface
{
    /**
     * Uloží hodnotu do cache
     * Stores value in cache
     *
     * @param string $key Klíč / Key
     * @param mixed $value Hodnota / Value
     * @param int|null $ttl TTL v sekundách / TTL in seconds
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Načte hodnotu z cache
     * Retrieves value from cache
     *
     * @param string $key Klíč / Key
     * @param mixed $default Výchozí hodnota / Default value
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Smaže hodnotu z cache
     * Removes value from cache
     *
     * @param string $key Klíč / Key
     * @return bool
     */
    public function forget(string $key): bool;

    /**
     * Smaže celou cache
     * Clears entire cache
     *
     * @return bool
     */
    public function flush(): bool;

    /**
     * Kontroluje existenci klíče
     * Checks if key exists
     *
     * @param string $key Klíč / Key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Načte hodnotu s callback funkcí
     * Retrieves value with callback function
     *
     * @param string $key Klíč / Key
     * @param int|null $ttl TTL v sekundách / TTL in seconds
     * @param callable $callback Callback funkce / Callback function
     * @return mixed
     */
    public function remember(string $key, ?int $ttl, callable $callback): mixed;
}