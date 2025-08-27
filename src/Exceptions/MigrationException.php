<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro chyby migrace
 *
 * Vyhazována při neúspěšném spuštění nebo
 * rollback migrací modulu.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Migration Exception
 *
 * Thrown when migration execution or
 * rollback fails for a module.
 */
class MigrationException extends ModulioException
{
    /**
     * Název modulu u kterého selhala migrace
     * Name of module where migration failed
     *
     * @var string
     */
    protected string $moduleName;

    /**
     * Typ migrace operace
     * Migration operation type
     *
     * @var string
     */
    protected string $operation;

    /**
     * Chybové výstupy
     * Error outputs
     *
     * @var array<string>
     */
    protected array $errors;

    /**
     * @param string $moduleName Název modulu
     * @param string $operation Typ operace (run, rollback)
     * @param array $errors Chybové výstupy
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $moduleName,
        string     $operation,
        array      $errors = [],
        ?string    $message = null,
        int        $code = 500,
        ?Exception $previous = null
    )
    {
        $this->moduleName = $moduleName;
        $this->operation = $operation;
        $this->errors = $errors;

        $message = $message ?: "Migration $operation failed for module '$moduleName'.";

        parent::__construct($message, $code, $previous, [
            'module_name' => $moduleName,
            'operation' => $operation,
            'errors' => $errors,
        ]);
    }

    /**
     * Vrací název modulu
     * Returns module name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
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

    /**
     * Vrací chybové výstupy
     * Returns error outputs
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
