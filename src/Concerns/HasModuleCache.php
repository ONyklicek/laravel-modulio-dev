<?php

namespace NyonCode\LaravelModulio\Concerns;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

/**
 * Trait pro cache operace s moduly
 *
 * Poskytuje helpers pro efektivní cache operace
 * v modulárním systému.
 *
 * @package NyonCode\LaravelModulio\Traits
 *
 * ---
 *
 * Module Cache Trait
 *
 * Provides helpers for efficient cache operations
 * in modular system.
 */
trait HasModuleCache
{
    /**
     * Načte hodnotu z cache s modulovým prefixem
     * Loads value from cache with module prefix
     *
     * @param string $key
     * @param mixed $default
     * @param string|null $moduleName
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getModuleCache(string $key, mixed $default = null, ?string $moduleName = null): mixed
    {
        $cacheKey = $this->buildModuleCacheKey($key, $moduleName);
        return cache()->get($cacheKey, $default);
    }

    /**
     * Uloží hodnotu do cache s modulovým prefixem
     * Stores value in cache with module prefix
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param string|null $moduleName
     * @return bool
     */
    protected function putModuleCache(string $key, mixed $value, ?int $ttl = null, ?string $moduleName = null): bool
    {
        $cacheKey = $this->buildModuleCacheKey($key, $moduleName);
        $ttl = $ttl ?? config('modulio.cache_ttl', 60) * 60;

        return cache()->put($cacheKey, $value, $ttl);
    }

    /**
     * Načte hodnotu s callback funkcí
     * Loads value with callback function
     *
     * @param string $key
     * @param int|null $ttl
     * @param callable $callback
     * @param string|null $moduleName
     * @return mixed
     */
    protected function rememberModuleCache(string $key, ?int $ttl, callable $callback, ?string $moduleName = null): mixed
    {
        $cacheKey = $this->buildModuleCacheKey($key, $moduleName);
        $ttl = $ttl ?? config('modulio.cache_ttl', 60) * 60;

        return cache()->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Smaže hodnotu z cache
     * Removes value from cache
     *
     * @param string $key
     * @param string|null $moduleName
     * @return bool
     */
    protected function forgetModuleCache(string $key, ?string $moduleName = null): bool
    {
        $cacheKey = $this->buildModuleCacheKey($key, $moduleName);
        return cache()->forget($cacheKey);
    }

    /**
     * Sestaví cache klíč pro modul
     * Builds cache key for module
     *
     * @param string $key
     * @param string|null $moduleName
     * @return string
     */
    protected function buildModuleCacheKey(string $key, ?string $moduleName = null): string
    {
        $prefix = config('modulio.cache_prefix', 'modulio');
        $module = $moduleName ?? $this->getModuleName();

        return "$prefix.$module.$key";
    }

    /**
     * Získá název aktuálního modulu
     * Gets current module name
     *
     * @return string
     */
    protected function getModuleName(): string
    {
        // Pokus o automatické určení názvu modulu z namespace
        // Attempt to automatically determine module name from namespace
        $reflection = new ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();

        // Extrakce názvu modulu z namespace (např. App\Modules\Blog\Controllers -> blog)
        // Extract module name from namespace (e.g., App\Modules\Blog\Controllers -> blog)
        $parts = explode('\\', $namespace);
        $moduleIndex = array_search('Modules', $parts);

        if ($moduleIndex !== false && isset($parts[$moduleIndex + 1])) {
            return strtolower($parts[$moduleIndex + 1]);
        }

        return 'default';
    }
}