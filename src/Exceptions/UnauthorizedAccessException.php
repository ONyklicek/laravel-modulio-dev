<?php

namespace NyonCode\LaravelModulio\Exceptions;

use Exception;

/**
 * Výjimka pro neautorizovaný přístup
 *
 * Vyhazována při pokusu o přístup k funkcionalitě
 * bez potřebných oprávnění.
 *
 * @package NyonCode\LaravelModulio\Exceptions
 *
 * ---
 *
 * Unauthorized Access Exception
 *
 * Thrown when attempting to access functionality
 * without required permissions.
 */
class UnauthorizedAccessException extends ModulioException
{
    /**
     * Vyžadovaná oprávnění
     * Required permissions
     *
     * @var array<string>
     */
    protected array $requiredPermissions;

    /**
     * Uživatelská oprávnění
     * User permissions
     *
     * @var array<string>
     */
    protected array $userPermissions;

    /**
     * Akce kterou uživatel chtěl provést
     * Action user wanted to perform
     *
     * @var string
     */
    protected string $attemptedAction;

    /**
     * @param string $attemptedAction Akce
     * @param array $requiredPermissions Vyžadovaná oprávnění
     * @param array $userPermissions Uživatelská oprávnění
     * @param string|null $message Vlastní zpráva (volitelné)
     * @param int $code Kód výjimky
     * @param Exception|null $previous Předchozí výjimka
     */
    public function __construct(
        string     $attemptedAction,
        array      $requiredPermissions = [],
        array      $userPermissions = [],
        ?string    $message = null,
        int        $code = 403,
        ?Exception $previous = null
    )
    {
        $this->attemptedAction = $attemptedAction;
        $this->requiredPermissions = $requiredPermissions;
        $this->userPermissions = $userPermissions;

        $requiredList = implode(', ', $requiredPermissions);
        $message = $message ?: "Unauthorized access to '$attemptedAction'. Required permissions: $requiredList";

        parent::__construct($message, $code, $previous, [
            'attempted_action' => $attemptedAction,
            'required_permissions' => $requiredPermissions,
            'user_permissions' => $userPermissions,
        ]);
    }

    /**
     * Vrací vyžadovaná oprávnění
     * Returns required permissions
     *
     * @return array<string>
     */
    public function getRequiredPermissions(): array
    {
        return $this->requiredPermissions;
    }

    /**
     * Vrací uživatelská oprávnění
     * Returns user permissions
     *
     * @return array<string>
     */
    public function getUserPermissions(): array
    {
        return $this->userPermissions;
    }

    /**
     * Vrací akci
     * Returns attempted action
     *
     * @return string
     */
    public function getAttemptedAction(): string
    {
        return $this->attemptedAction;
    }
}