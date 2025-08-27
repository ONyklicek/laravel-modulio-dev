<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Základní výjimka pro modulární systém
 *
 * Rodičovská třída pro všechny výjimky v Laravel Modulio.
 * Poskytuje společné rozhraní a funkcionality.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Base Modulio Exception
 *
 * Parent class for all Laravel Modulio exceptions.
 * Provides common interface and functionality.
 */
class ModulioException extends Exception
{
    /**
     * Dodatečné informace o výjimce
     * Additional exception information
     *
     * @var array<string, mixed>
     */
    protected array $context = [];

    /**
     * @param string $message Zpráva výjimky / Exception message
     * @param int $code Kód výjimky / Exception code
     * @param Exception|null $previous Předchozí výjimka / Previous exception
     * @param array $context Dodatečné informace / Additional context
     */
    public function __construct(
        string     $message = "",
        int        $code = 0,
        ?Exception $previous = null,
        array      $context = []
    )
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Vrací dodatečné informace
     * Returns additional context
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Nastaví dodatečné informace
     * Sets additional context
     *
     * @param array<string, mixed> $context
     * @return self
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Přidá dodatečnou informaci
     * Adds additional context item
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addContext(string $key, mixed $value): self
    {
        $this->context[$key] = $value;
        return $this;
    }

    /**
     * Vrací pole pro logování
     * Returns array for logging
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'exception' => static::class,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
            'trace' => $this->getTraceAsString(),
        ];
    }
}