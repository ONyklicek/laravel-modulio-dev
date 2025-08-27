<?php

namespace NyonCode\LaravelModulio\Helpers;

use Exception;
use Log;

/**
 * Pomocná třída pro správu eventů
 *
 * Poskytuje utility metody pro práci s eventy
 * v kontextu modulárního systému.
 *
 * @package NyonCode\LaravelModulio\Helpers
 *
 * ---
 *
 * Event Management Helper Class
 *
 * Provides utility methods for working with events
 * in modular system context.
 */
class ModulioEventManager
{
    /**
     * Registruje listener pro modul
     * Register listener for module
     *
     * @param string $eventClass
     * @param string $listenerClass
     * @param string $moduleName
     */
    public static function registerModuleListener(string $eventClass, string $listenerClass, string $moduleName): void
    {
        if (!class_exists($listenerClass)) {
            Log::warning("Listener class not found: $listenerClass for module $moduleName");
            return;
        }

        app('events')->listen($eventClass, function (...$args) use ($listenerClass, $moduleName) {
            // Přidáme context o modulu
            // Add module context
            if (method_exists($listenerClass, 'setModuleContext')) {
                $listener = app($listenerClass);
                $listener->setModuleContext($moduleName);
                return $listener->handle(...$args);
            }

            return app($listenerClass)->handle(...$args);
        });

        if (config('modulio.debug_mode', false)) {
            Log::debug("Registered module listener", [
                'event' => $eventClass,
                'listener' => $listenerClass,
                'module' => $moduleName,
            ]);
        }
    }

    /**
     * Odregistruje všechny listenery modulu
     * Deregister all module listeners
     *
     * @param string $moduleName
     */
    public static function deregisterModuleListeners(string $moduleName): void
    {
        $registeredListeners = config("modulio.modules.$moduleName.listeners", []);

        foreach ($registeredListeners as $eventClass => $listeners) {
            foreach ($listeners as $listenerClass) {
                app('events')->forget($eventClass, $listenerClass);
            }
        }

        if (config('modulio.debug_mode', false)) {
            Log::debug("Deregistered module listeners", [
                'module' => $moduleName,
                'listeners_count' => array_sum(array_map('count', $registeredListeners)),
            ]);
        }
    }

    /**
     * Získá všechny listenery pro daný event
     * Get all listeners for given event
     *
     * @param string $eventClass
     * @return array
     */
    public static function getEventListeners(string $eventClass): array
    {
        return app('events')->getListeners($eventClass);
    }

    /**
     * Zkontroluje zda má event nějaké listenery
     * Check if event has any listeners
     *
     * @param string $eventClass
     * @return bool
     */
    public static function hasEventListeners(string $eventClass): bool
    {
        return app('events')->hasListeners($eventClass);
    }

    /**
     * Spustí event podmíněně
     * Fire event conditionally
     *
     * @param string $eventClass
     * @param array $payload
     * @param callable|null $condition
     * @return array|null
     */
    public static function fireIf(string $eventClass, array $payload = [], ?callable $condition = null): ?array
    {
        if ($condition && !$condition()) {
            return null;
        }

        return event(new $eventClass(...$payload));
    }

    /**
     * Spustí event asynchronně (pokud je možné)
     * Fire event asynchronously (if possible)
     *
     * @param string $eventClass
     * @param array $payload
     */
    public static function fireAsync(string $eventClass, array $payload = []): void
    {
        if (config('queue.default') !== 'sync') {
            // Použij queue pro asynchronní zpracování
            // Use queue for async processing
            dispatch(function () use ($eventClass, $payload) {
                event(new $eventClass(...$payload));
            });
        } else {
            // Fallback na synchronní zpracování
            // Fallback to sync processing
            event(new $eventClass(...$payload));
        }
    }

    /**
     * Registruje dočasný listener
     * Register temporary listener
     *
     * @param string $eventClass
     * @param callable $callback
     * @param int $times Kolikrát má být listener aktivní
     */
    public static function listenOnce(string $eventClass, callable $callback, int $times = 1): void
    {
        $counter = 0;

        app('events')->listen($eventClass, function (...$args) use ($callback, &$counter, $times, $eventClass) {
            if ($counter < $times) {
                $counter++;
                $result = $callback(...$args);

                // Odregistruje listener po dosažení limitu
                // Deregister listener after reaching limit
                if ($counter >= $times) {
                    app('events')->forget($eventClass, $callback);
                }

                return $result;
            }
        });

    }

    /**
     * Vytvoří event hook pro moduly
     * Create event hook for modules
     *
     * @param string $hookName
     * @param array $payload
     * @return mixed
     * @return mixed
     * @return mixed
     *
     * @throws Exception
     *
     */
    public static function triggerHook(string $hookName, array $payload = []): mixed
    {
        $hooks = config('modulio.hooks', []);

        if (!isset($hooks[$hookName])) {
            return null;
        }

        $results = [];

        foreach ($hooks[$hookName] as $callback) {
            if (is_callable($callback)) {
                $results[] = call_user_func_array($callback, $payload);
            } elseif (is_string($callback) && class_exists($callback)) {
                $instance = app($callback);
                if (method_exists($instance, 'handle')) {
                    $results[] = $instance->handle(...$payload);
                }
            }
        }

        return count($results) === 1 ? $results[0] : $results;
    }
}