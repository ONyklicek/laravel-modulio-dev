<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro chyby cache
 *
 * Vyhazována při problémech s cache operacemi
 * v modulárním systému.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Cache Exception
 *
 * Thrown when cache operations fail
 * in the modular system.
 */
class CacheException extends ModulioException
{
    /**
     * Cache klíč u kterého nastala chyba
     * Cache key where error occurred
     *
     * @var string|null
     */
    protected ?string $cacheKey;

    /**
     * Typ cache operace
     * Cache operation type
     *
     * @var string
     */
    protected string $operation;

    /**
     * @param string $operation Typ operace (get, put, forget, flush)
     * @param string|null $cacheKey Cache klíč
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $operation,
        ?string    $cacheKey = null,
        ?string    $message = null,
        int        $code = 500,
        ?Exception $previous = null
    )
    {
        $this->operation = $operation;
        $this->cacheKey = $cacheKey;

        $keyInfo = $cacheKey ? " for key '{$cacheKey}'" : '';
        $message = $message ?: "Cache {$operation} operation failed{$keyInfo}.";

        parent::__construct($message, $code, $previous, [
            'operation' => $operation,
            'cache_key' => $cacheKey,
        ]);
    }

    /**
     * Vrací cache klíč
     * Returns cache key
     *
     * @return string|null
     */
    public function getCacheKey(): ?string
    {
        return $this->cacheKey;
    }

    /**
     * Vrací typ operace
     * Returns operation type
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }
}