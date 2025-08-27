<?php

namespace NyonCode\LaravelModulio\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event vyvolaný při aktualizaci cache modulů
 *
 * Informuje o změnách v cache a umožňuje
 * invalidaci souvisejících cache klíčů.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * Module Cache Updated Event
 *
 * Informs about cache changes and allows
 * invalidation of related cache keys.
 */
class ModuleCacheUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Typ cache operace
     * Cache operation type
     *
     * @var string
     */
    public string $operation;

    /**
     * Dotčené cache klíče
     * Affected cache keys
     *
     * @var array<string>
     */
    public array $affectedKeys;

    /**
     * Čas aktualizace
     * Update time
     *
     * @var Carbon
     */
    public Carbon $updatedAt;

    /**
     * @param string $operation Typ operace (update, clear, flush)
     * @param array $affectedKeys Dotčené klíče
     */
    public function __construct(string $operation, array $affectedKeys = [])
    {
        $this->operation = $operation;
        $this->affectedKeys = $affectedKeys;
        $this->updatedAt = now();
    }
}