<?php

namespace NyonCode\LaravelModulio\Helpers;

use Illuminate\Support\Str;

/**
 * Helper třída pro práci s moduly
 *
 * Poskytuje užitečné helper funkce pro práci
 * s modulárním systémem.
 *
 * @package NyonCode\LaravelModulio\Helpers
 *
 * ---
 *
 * Module Helper Class
 *
 * Provides useful helper functions for working
 * with modular system.
 */
class ModuleHelper
{
    /**
     * Parsuje verzi modulu
     * Parses module version
     *
     * @param string $version
     * @return array
     */
    public static function parseVersion(string $version): array
    {
        if (!preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-([a-zA-Z0-9\-.]+))?(?:\+([a-zA-Z0-9\-.]+))?$/', $version, $matches)) {
            return [
                'major' => 0,
                'minor' => 0,
                'patch' => 0,
                'prerelease' => null,
                'build' => null,
                'valid' => false
            ];
        }

        return [
            'major' => (int)$matches[1],
            'minor' => (int)$matches[2],
            'patch' => (int)$matches[3],
            'prerelease' => $matches[4] ?? null,
            'build' => $matches[5] ?? null,
            'valid' => true
        ];
    }

    /**
     * Porovnává verze modulů
     * Compares module versions
     *
     * @param string $version1
     * @param string $version2
     * @return int -1, 0, 1
     */
    public static function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }

    /**
     * Validuje název modulu
     * Validates module name
     *
     * @param string $name
     * @return bool
     */
    public static function validateModuleName(string $name): bool
    {
        return preg_match('/^[a-z0-9\-_]+$/', $name) === 1;
    }

    /**
     * Normalizuje název modulu
     * Normalizes module name
     *
     * @param string $name
     * @return string
     */
    public static function normalizeModuleName(string $name): string
    {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9\-_]/', '-', $name), '-'));
    }

    /**
     * Generuje cache klíč pro modul
     * Generates cache key for module
     *
     * @param string $moduleName
     * @param string $suffix
     * @return string
     */
    public static function generateCacheKey(string $moduleName, string $suffix = ''): string
    {
        $prefix = config('modulio.cache_prefix', 'modulio');
        $key = "{$prefix}.{$moduleName}";

        if (!empty($suffix)) {
            $key .= ".{$suffix}";
        }

        return $key;
    }

    /**
     * Získá namespace pro modul
     * Gets namespace for module
     *
     * @param string $moduleName
     * @param string $vendor
     * @return string
     */
    public static function getModuleNamespace(string $moduleName, string $vendor = 'App\\Modules'): string
    {
        return $vendor.'\\'.Str::studly($moduleName);
    }

    /**
     * Konvertuje snake_case na StudlyCase
     * Converts snake_case to StudlyCase
     *
     * @param string $value
     * @return string
     */
    public static function studlyCase(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    /**
     * Konvertuje StudlyCase na snake_case
     * Converts StudlyCase to snake_case
     *
     * @param string $value
     * @return string
     */
    public static function snakeCase(string $value): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }

    /**
     * Parsuje Composer package jméno
     * Parses Composer package name
     *
     * @param string $packageName
     * @return array
     */
    public static function parsePackageName(string $packageName): array
    {
        if (!str_contains($packageName, '/')) {
            return [
                'vendor' => null,
                'name' => $packageName,
                'valid' => false
            ];
        }

        [$vendor, $name] = explode('/', $packageName, 2);

        return [
            'vendor' => $vendor,
            'name' => $name,
            'valid' => !empty($vendor) && !empty($name)
        ];
    }

    /**
     * Formátuje velikost souboru
     * Formats file size
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatFileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Sanitizuje řetězec pro použití v názvu souboru
     * Sanitizes string for filename usage
     *
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '_', $filename);
    }
}