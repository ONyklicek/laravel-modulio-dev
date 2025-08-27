<?php

namespace NyonCode\LaravelModulio\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Příkaz pro generování nového modulu
 *
 * Vytvoří strukturu nového modulu s všemi potřebnými soubory
 * podle šablony a best practices.
 *
 * @package NyonCode\LaravelModulio\Commands
 *
 * ---
 *
 * Command to generate new module
 *
 * Creates new module structure with all necessary files
 * based on template and best practices.
 */
class ModulioMakeCommand extends Command
{
    /**
     * Název a podpis příkazu
     * Command name and signature
     *
     * @var string
     */
    protected $signature = 'modulio:make 
                           {name : Module name}
                           {--vendor=App : Vendor namespace}
                           {--path=app/Modules : Base path for modules}
                           {--with-migration : Include migration template}
                           {--with-controller : Include controller template}
                           {--with-model : Include model template}
                           {--with-livewire : Include Livewire component template}
                           {--with-views : Include view templates}
                           {--force : Overwrite existing files}';

    /**
     * Popis příkazu
     * Command description
     *
     * @var string
     */
    protected $description = 'Generate a new module structure / Vygeneruje strukturu nového modulu';

    /**
     * Spuštění příkazu
     * Execute command
     *
     * @return int
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $vendor = $this->option('vendor');
        $basePath = $this->option('path');

        $modulePath = base_path("$basePath/" . Str::studly($name));

        if (is_dir($modulePath) && !$this->option('force')) {
            if (!$this->confirm("Module directory already exists. Overwrite?")) {
                $this->info('Module generation cancelled.');
                return 0;
            }
        }

        $this->info("🚀 Generating module: $name");

        // Vytvoření adresářové struktury
        // Create directory structure
        $this->createDirectoryStructure($modulePath);

        // Generování souborů
        // Generate files
        $this->generateServiceProvider($modulePath, $name, $vendor);
        $this->generateComposerJson($modulePath, $name, $vendor);
        $this->generateConfig($modulePath, $name);

        if ($this->option('with-migration')) {
            $this->generateMigration($modulePath, $name);
        }

        if ($this->option('with-controller')) {
            $this->generateController($modulePath, $name, $vendor);
        }

        if ($this->option('with-model')) {
            $this->generateModel($modulePath, $name, $vendor);
        }

        if ($this->option('with-livewire')) {
            $this->generateLivewireComponent($modulePath, $name, $vendor);
        }

        if ($this->option('with-views')) {
            $this->generateViews($modulePath, $name);
        }

        $this->generateRoutes($modulePath, $name);
        $this->generateReadme($modulePath, $name);

        $this->info("✅ Module {$name} generated successfully!");
        $this->info("📁 Location: {$modulePath}");
        $this->line("\nNext steps:");
        $this->line("1. Review generated files");
        $this->line("2. Customize configuration");
        $this->line("3. Register module in your application");

        return 0;
    }

    /**
     * Vytvoří adresářovou strukturu
     * Creates directory structure
     *
     * @param string $modulePath
     */
    protected function createDirectoryStructure(string $modulePath): void
    {
        $directories = [
            'src',
            'src/Controllers',
            'src/Models',
            'src/Livewire',
            'config',
            'database/migrations',
            'database/seeders',
            'resources/views',
            'resources/assets/css',
            'resources/assets/js',
            'routes',
            'tests/Unit',
            'tests/Feature',
        ];

        foreach ($directories as $dir) {
            $fullPath = "{$modulePath}/{$dir}";
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->line("📁 Created: {$dir}");
            }
        }
    }

    /**
     * Generuje service provider
     * Generates service provider
     *
     * @param string $modulePath
     * @param string $name
     * @param string $vendor
     */
    protected function generateServiceProvider(string $modulePath, string $name, string $vendor): void
    {
        $studlyName = Str::studly($name);
        $snakeName = Str::snake($name);

        $content = <<<PHP
<?php

namespace {$vendor}\\{$studlyName};

use Illuminate\Support\ServiceProvider;
use NyonCode\LaravelModulio\ModuleManager;
use NyonCode\LaravelModulio\Navigation\Navigation;
use NyonCode\LaravelModulio\Navigation\NavigationItem;
use NyonCode\LaravelModulio\Navigation\NavigationGroup;
use NyonCode\LaravelModulio\Contracts\ModuleRegistrarInterface;

class {$studlyName}ServiceProvider extends ServiceProvider implements ModuleRegistrarInterface
{
    public function register(): void
    {
        \$this->mergeConfigFrom(
            __DIR__ . '/../config/{$snakeName}.php',
            '{$snakeName}'
        );
    }

    public function boot(): void
    {
        \$this->publishes([
            __DIR__ . '/../config/{$snakeName}.php' => config_path('{$snakeName}.php'),
        ], '{$snakeName}-config');

        \$this->loadViewsFrom(__DIR__ . '/../resources/views', '{$snakeName}');
        \$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        \$this->registerModule(app(ModuleManager::class));
    }

    public function registerModule(ModuleManager \$moduleManager): void
    {
        \$moduleManager->register('{$snakeName}')
            ->version('1.0.0')
            ->description('{$studlyName} module')
            ->config(__DIR__ . '/../config/{$snakeName}.php')
            ->migrations(__DIR__ . '/../database/migrations')
            ->permissions([
                '{$snakeName}.view',
                '{$snakeName}.create',
                '{$snakeName}.edit',
                '{$snakeName}.delete',
            ])
            ->nav(
                Navigation::make('admin', fn() => [
                    NavigationItem::make('{$studlyName}')
                        ->icon('heroicon-s-puzzle-piece')
                        ->route('{$snakeName}.index')
                        ->permission('{$snakeName}.view')
                        ->order(50),
                ])
            )
            ->register();
    }
}
PHP;

        file_put_contents("{$modulePath}/src/{$studlyName}ServiceProvider.php", $content);
        $this->line("📝 Created: ServiceProvider");
    }

    /**
     * Generuje composer.json
     * Generates composer.json
     *
     * @param string $modulePath
     * @param string $name
     * @param string $vendor
     */
    protected function generateComposerJson(string $modulePath, string $name, string $vendor): void
    {
        $kebabName = str_replace('_', '-', Str::snake($name));
        $vendorLower = strtolower($vendor);
        $studlyName = Str::studly($name);

        $content = [
            'name' => "{$vendorLower}/{$kebabName}",
            'description' => "Laravel {$studlyName} Module",
            'type' => 'library',
            'keywords' => ['laravel', 'module', $kebabName, 'modulio'],
            'require' => [
                'php' => '^8.1',
                'illuminate/support' => '^10.0|^11.0|^12.0',
                'nyoncode/laravel-modulio' => '^1.0',
            ],
            'autoload' => [
                'psr-4' => [
                    "{$vendor}\\{$studlyName}\\" => 'src/',
                ],
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        "{$vendor}\\{$studlyName}\\{$studlyName}ServiceProvider",
                    ],
                    'modulio-providers' => [
                        "{$vendor}\\{$studlyName}\\{$studlyName}ServiceProvider",
                    ],
                ],
            ],
            'version' => '1.0.0',
        ];

        file_put_contents("{$modulePath}/composer.json", json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line("📝 Created: composer.json");
    }

    /**
     * Generuje konfiguraci
     * Generates configuration
     *
     * @param string $modulePath
     * @param string $name
     */
    protected function generateConfig(string $modulePath, string $name): void
    {
        $snakeName = Str::snake($name);
        $studlyName = Str::studly($name);

        $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | {$studlyName} Module Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for {$studlyName} module
    |
    */

    'enabled' => env('{$snakeName}_ENABLED', true),
    
    'auto_migrate' => env('{$snakeName}_AUTO_MIGRATE', false),
    
    'cache_ttl' => env('{$snakeName}_CACHE_TTL', 3600),
    
    'settings' => [
        'items_per_page' => 15,
        'show_in_menu' => true,
        'allow_public_access' => false,
    ],
];
PHP;

        file_put_contents("{$modulePath}/config/{$snakeName}.php", $content);
        $this->line("📝 Created: config/{$snakeName}.php");
    }

    // Další generátory metody by pokračovaly podobně...
    // Additional generator methods would continue similarly...
}