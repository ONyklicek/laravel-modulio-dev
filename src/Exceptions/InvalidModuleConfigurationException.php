<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro neplatnou konfiguraci modulu
 *
 * Vyhazována při registraci modulu s neplatnou
 * nebo chybějící konfigurací.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Invalid Module Configuration Exception
 *
 * Thrown when registering a module with invalid
 * or missing configuration.
 */
class InvalidModuleConfigurationException extends ModulioException
{
    /**
     * Název modulu s neplatnou konfigurací
     * Name of module with invalid configuration
     *
     * @var string
     */
    protected string $moduleName;

    /**
     * Chybějící nebo neplatné pole konfigurace
     * Missing or invalid configuration fields
     *
     * @var array<string>
     */
    protected array $invalidFields;

    /**
     * @param string $moduleName Název modulu
     * @param array $invalidFields Neplatná pole
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $moduleName,
        array      $invalidFields = [],
        ?string    $message = null,
        int        $code = 422,
        ?Exception $previous = null
    )
    {
        $this->moduleName = $moduleName;
        $this->invalidFields = $invalidFields;

        $fieldList = implode(', ', $invalidFields);
        $message = $message ?: "Invalid configuration for module '$moduleName'. Invalid fields: $fieldList";

        parent::__construct($message, $code, $previous, [
            'module_name' => $moduleName,
            'invalid_fields' => $invalidFields,
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
     * Vrací neplatná pole
     * Returns invalid fields
     *
     * @return array<string>
     */
    public function getInvalidFields(): array
    {
        return $this->invalidFields;
    }
}