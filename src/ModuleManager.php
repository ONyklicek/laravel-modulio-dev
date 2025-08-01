<?php

namespace NyonCode\LaravelModulio\Core;

use Illuminate\Support\Collection;

class ModuleManager
{
    protected Collection $modules;
    protected $menuManager;
    protected array $config;
    protected bool $autoMigrations;

    public function __construct()
    {
        $this->modules = new Collection();

        $this->config = config('modules', []);
    }
}