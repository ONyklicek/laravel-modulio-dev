<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro nenalezený modul
 *
 * Vyhazována při pokusu o přístup k modulu,
 * který není registrován v systému.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Module Not Found Exception
 *
 * Thrown when attempting to access a module
 * that is not registered in the system.
 */
class ModuleNotFoundException extends ModulioException
{
    /**
     * Název hledaného modulu
     * Name of searched module
     *
     * @var string
     */
    protected string $moduleName;

    /**
     * @param string $moduleName Název hledaného modulu
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $moduleName,
        ?string    $message = null,
        int        $code = 404,
        ?Exception $previous = null
    )
    {
        $this->moduleName = $moduleName;

        $message = $message ?: "Module '$moduleName' not found.";

        parent::__construct($message, $code, $previous, [
            'module_name' => $moduleName,
        ]);
    }

    /**
     * Vrací název hledaného modulu
     * Returns searched module name
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }
}
