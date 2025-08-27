<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro chyby oprávnění
 *
 * Vyhazována při neúspěšném vytváření
 * nebo správě oprávnění modulu.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Permission Exception
 *
 * Thrown when permission creation or
 * management fails for a module.
 */
class PermissionException extends ModulioException
{
    /**
     * Název modulu u kterého selhala správa oprávnění
     * Name of module where permission management failed
     *
     * @var string
     */
    protected string $moduleName;

    /**
     * Problematická oprávnění
     * Problematic permissions
     *
     * @var array<string>
     */
    protected array $permissions;

    /**
     * Typ operace
     * Operation type
     *
     * @var string
     */
    protected string $operation;

    /**
     * @param string $moduleName Název modulu
     * @param array $permissions Problematická oprávnění
     * @param string $operation Typ operace (create, delete, sync)
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $moduleName,
        array      $permissions = [],
        string     $operation = 'manage',
        ?string    $message = null,
        int        $code = 500,
        ?Exception $previous = null
    )
    {
        $this->moduleName = $moduleName;
        $this->permissions = $permissions;
        $this->operation = $operation;

        $permissionList = implode(', ', $permissions);
        $message = $message ?: "Permission $operation failed for module '$moduleName'. Permissions: $permissionList";

        parent::__construct($message, $code, $previous, [
            'module_name' => $moduleName,
            'permissions' => $permissions,
            'operation' => $operation,
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
     * Vrací problematická oprávnění
     * Returns problematic permissions
     *
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
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
