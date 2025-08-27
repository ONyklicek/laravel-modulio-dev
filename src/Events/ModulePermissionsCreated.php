<?php

namespace NyonCode\LaravelModulio\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NyonCode\LaravelModulio\Contracts\ModuleInterface;

/**
 * Event vyvolaný při vytváření oprávnění modulu
 *
 * Umožňuje modifikaci nebo dodatečné zpracování
 * při vytváření oprávnění pro modul.
 *
 * @package NyonCode\LaravelModulio\Events
 *
 * ---
 *
 * Module Permissions Created Event
 *
 * Allows modification or additional processing
 * when creating permissions for module.
 */
class ModulePermissionsCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Modul pro který se vytvářejí oprávnění
     * Module for which permissions are created
     *
     * @var ModuleInterface
     */
    public ModuleInterface $module;

    /**
     * Vytvořená oprávnění
     * Created permissions
     *
     * @var array<string>
     */
    public array $createdPermissions;

    /**
     * Oprávnění která již existovala
     * Permissions that already existed
     *
     * @var array<string>
     */
    public array $existingPermissions;

    /**
     * @param ModuleInterface $module
     * @param array $createdPermissions
     * @param array $existingPermissions
     */
    public function __construct(
        ModuleInterface $module,
        array           $createdPermissions,
        array           $existingPermissions = []
    )
    {
        $this->module = $module;
        $this->createdPermissions = $createdPermissions;
        $this->existingPermissions = $existingPermissions;
    }
}