<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro duplicitní routy
 *
 * Vyhazována při registraci modulu s routou,
 * která již existuje v jiném modulu.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Duplicate Route Exception
 *
 * Thrown when registering a module with a route
 * that already exists in another module.
 */
class DuplicateRouteException extends ModulioException
{
    /**
     * Duplicitní routy
     * Duplicate routes
     *
     * @var array<string>
     */
    protected array $duplicateRoutes;

    /**
     * @param array|string $duplicateRoutes Duplicitní routy
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        array|string $duplicateRoutes,
        ?string      $message = null,
        int          $code = 409,
        ?Exception   $previous = null
    )
    {
        $this->duplicateRoutes = is_array($duplicateRoutes)
            ? $duplicateRoutes
            : [$duplicateRoutes];

        $routeList = implode(', ', $this->duplicateRoutes);
        $message = $message ?: "Duplicate routes detected: {$routeList}";

        parent::__construct($message, $code, $previous, [
            'duplicate_routes' => $this->duplicateRoutes,
        ]);
    }

    /**
     * Vrací duplicitní routy
     * Returns duplicate routes
     *
     * @return array<string>
     */
    public function getDuplicateRoutes(): array
    {
        return $this->duplicateRoutes;
    }
}