<?php

namespace NyonCode\LaravelModulio\Commands;

use Illuminate\Console\Command;
use NyonCode\LaravelModulio\ModuleManager;
use Spatie\Permission\Models\Permission;

/**
 * Příkaz pro instalaci modulu
 *
 * Umožňuje instalaci modulu za běhu aplikace
 * s automatickou registrací a konfigurací.
 *
 * @package NyonCode\LaravelModulio\Commands
 *
 * ---
 *
 * Command to install module
 *
 * Allows runtime module installation
 * with automatic registration and configuration.
 */
class ModulioInstallCommand extends Command
{
    /**
     * Název a podpis příkazu
     * Command name and signature
     *
     * @var string
     */
    protected $signature = 'modulio:install 
                           {package : Composer package name}
                           {--dev : Install as dev dependency}
                           {--no-migrate : Skip automatic migrations}
                           {--no-permissions : Skip automatic permission creation}
                           {--force : Force installation without confirmation}';

    /**
     * Popis příkazu
     * Command description
     *
     * @var string
     */
    protected $description = 'Install a new module package / Nainstaluje nový modulární balíček';

    /**
     * Instance module managera
     * Module manager instance
     *
     * @var ModuleManager
     */
    protected ModuleManager $moduleManager;

    /**
     * @param ModuleManager $moduleManager
     */
    public function __construct(ModuleManager $moduleManager)
    {
        parent::__construct();
        $this->moduleManager = $moduleManager;
    }

    /**
     * Spuštění příkazu
     * Execute command
     *
     * @return int
     */
    public function handle(): int
    {
        if (!config('modulio.allow_runtime_installation', false)) {
            $this->error('Runtime installation is disabled. Enable it in modulio config.');
            return 1;
        }

        $package = $this->argument('package');

        if (!$this->option('force')) {
            if (!$this->confirm("Install module package '{$package}'?")) {
                $this->info('Installation cancelled.');
                return 0;
            }
        }

        $this->info("🚀 Installing module package: {$package}");

        // Krok 1: Instalace přes Composer
        // Step 1: Install via Composer
        if (!$this->installViaComposer($package)) {
            return 1;
        }

        // Krok 2: Auto-discovery a registrace
        // Step 2: Auto-discovery and registration
        $this->discoverAndRegisterModule($package);

        // Krok 3: Spuštění migrací
        // Step 3: Run migrations
        if (!$this->option('no-migrate')) {
            $this->runModuleMigrations($package);
        }

        // Krok 4: Vytvoření oprávnění
        // Step 4: Create permissions
        if (!$this->option('no-permissions')) {
            $this->createModulePermissions($package);
        }

        // Krok 5: Vyčištění cache
        // Step 5: Clear cache
        $this->call('modulio:clear-cache', ['--force' => true]);

        $this->info("✅ Module {$package} installed successfully!");

        return 0;
    }

    /**
     * Instaluje balíček přes Composer
     * Installs package via Composer
     *
     * @param string $package
     * @return bool
     */
    protected function installViaComposer(string $package): bool
    {
        $this->line('📦 Running Composer install...');

        $devFlag = $this->option('dev') ? '--dev' : '';
        $command = "composer require {$package} {$devFlag}";

        $process = proc_open(
            $command,
            [
                1 => ['pipe', 'w'], // stdout
                2 => ['pipe', 'w'], // stderr
            ],
            $pipes,
            base_path()
        );

        if (!is_resource($process)) {
            $this->error('Failed to start Composer process.');
            return false;
        }

        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            $this->error('Composer installation failed:');
            $this->line($errors);
            return false;
        }

        $this->info('📦 Composer installation completed.');
        return true;
    }

    /**
     * Objeví a zaregistruje modul
     * Discovers and registers module
     *
     * @param string $package
     */
    protected function discoverAndRegisterModule(string $package): void
    {
        $this->line('🔍 Discovering module...');

        // Pokus o nalezení service provideru v composer.json
        // Attempt to find service provider in composer.json
        $composerFile = base_path("vendor/{$package}/composer.json");

        if (!file_exists($composerFile)) {
            $this->warn("⚠️  Composer file not found for {$package}");
            return;
        }

        $composerData = json_decode(file_get_contents($composerFile), true);

        if (isset($composerData['extra']['laravel']['modulio-providers'])) {
            $providers = $composerData['extra']['laravel']['modulio-providers'];

            if (!is_array($providers)) {
                $providers = [$providers];
            }

            foreach ($providers as $providerClass) {
                if (class_exists($providerClass)) {
                    $this->line("📝 Registering module via {$providerClass}");

                    $provider = new $providerClass(app());

                    if (method_exists($provider, 'registerModule')) {
                        $provider->registerModule($this->moduleManager);
                        $this->info("✅ Module registered successfully!");
                    }
                }
            }
        }
    }

    /**
     * Spustí migrace modulu
     * Runs module migrations
     *
     * @param string $package
     */
    protected function runModuleMigrations(string $package): void
    {
        $this->line('🗄️  Running module migrations...');

        $migrationPath = base_path("vendor/{$package}/database/migrations");

        if (is_dir($migrationPath)) {
            $this->call('migrate', [
                '--path' => "vendor/{$package}/database/migrations",
                '--force' => true
            ]);
            $this->info('✅ Migrations completed.');
        } else {
            $this->line('ℹ️  No migrations found.');
        }
    }

    /**
     * Vytvoří oprávnění modulu
     * Creates module permissions
     *
     * @param string $package
     */
    protected function createModulePermissions(string $package): void
    {
        $this->line('🔐 Creating module permissions...');

        // Získá název modulu z package name
        // Get module name from package name
        $moduleName = basename($package);
        if (str_contains($moduleName, '-')) {
            $moduleName = str_replace('-', '_', $moduleName);
        }

        // Pokus o získání modulu a vytvoření jeho oprávnění
        // Attempt to get module and create its permissions
        $module = $this->moduleManager->getModule($moduleName);

        if ($module && !empty($module->getPermissions())) {
            foreach ($module->getPermissions() as $permission) {
                if (!Permission::where('name', $permission)->exists()) {
                    Permission::create(['name' => $permission]);
                    $this->line("🔐 Created permission: {$permission}");
                }
            }
            $this->info('✅ Permissions created.');
        } else {
            $this->line('ℹ️  No permissions to create.');
        }
    }
}