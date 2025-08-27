# Laravel Modulio

[![Latest Version](https://img.shields.io/packagist/v/nyoncode/laravel-modulio.svg?style=flat-square)](https://packagist.org/packages/nyoncode/laravel-modulio)
[![Total Downloads](https://img.shields.io/packagist/dt/nyoncode/laravel-modulio.svg?style=flat-square)](https://packagist.org/packages/nyoncode/laravel-modulio)
[![License](https://img.shields.io/packagist/l/nyoncode/laravel-modulio.svg?style=flat-square)](https://packagist.org/packages/nyoncode/laravel-modulio)

Profesionální modulární systém pro Laravel aplikace s automatickou registrací, navigací a správou migrací. Postavený na*
*nyoncode/laravel-package-toolkit**.

Professional modular system for Laravel applications with automatic registration, navigation and migration management.
Built on **nyoncode/laravel-package-toolkit**.

## ✨ Hlavní funkce / Key Features

- 🚀 **Automatická registrace modulů** - Instalací Composer balíčku se modul automaticky zaregistruje
- 📊 **Hierarchická navigace** - Podpora vnořených menu s ikonami, oprávněními a řazením
- 🔄 **Automatické migrace** - Volitelné automatické spouštění migrací při instalaci
- 🔐 **Integrace se Spatie Permission** - Automatické vytváření a správa oprávnění
- ⚡ **Vysoký výkon** - Pokročilé cachování s podporou všech Laravel cache driverů
- 🎨 **Livewire 3 podpora** - Předpřipravené komponenty pro správu modulů
- 🔧 **Fluent API** - Elegantní a intuitivní konfigurace modulů
- 📦 **Production ready** - Optimalizováno pro produkční nasazení

## 📋 Požadavky / Requirements

- PHP 8.1+
- Laravel 10.0+ | 11.0+ | 12.0+
- Livewire 3.0+
- Spatie Permission 5.0+ | 6.0+

## 🚀 Instalace / Installation

```bash
composer require nyoncode/laravel-modulio
```

Publikujte konfiguraci:

```bash
php artisan vendor:publish --tag=modulio-config
```

Volitelně publikujte views a migrace:

```bash
php artisan vendor:publish --tag=modulio-views
php artisan vendor:publish --tag=modulio-migrations
```

## ⚙️ Základní použití / Basic Usage

### Registrace modulu v Service Provideru

```php
<?php

namespace VendorName\YourModule;

use Illuminate\Support\ServiceProvider;
use NyonCode\LaravelModulio\ModuleManager;
use NyonCode\LaravelModulio\Navigation\Navigation;
use NyonCode\LaravelModulio\Navigation\NavigationItem;
use NyonCode\LaravelModulio\Navigation\NavigationGroup;

class YourModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Registrace modulu
        $this->registerModule(app(ModuleManager::class));
    }

    public function registerModule(ModuleManager $moduleManager): void
    {
        $moduleManager->register('your-module')
            ->version('1.0.0')
            ->description('Popis vašeho modulu')
            
            // Konfigurace
            ->config(__DIR__ . '/../config/your-module.php')
            
            // Migrace
            ->migrations(__DIR__ . '/../database/migrations')
            ->runMigrations(true) // Automatické spuštění
            
            // Oprávnění
            ->permissions([
                'your-module.view',
                'your-module.create',
                'your-module.edit',
                'your-module.delete',
            ])
            
            // Navigace
            ->nav(
                Navigation::make('admin', fn() => [
                    NavigationGroup::make('Váš Modul', fn() => [
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-s-home')
                            ->route('your-module.dashboard')
                            ->permission('your-module.view')
                            ->order(1),
                        NavigationItem::make('Nastavení')
                            ->icon('heroicon-s-cog')
                            ->route('your-module.settings')
                            ->permission('your-module.edit')
                            ->order(2),
                    ])
                    ->icon('heroicon-s-puzzle-piece')
                    ->order(50),
                ])
            )
            ->register();
    }
}
```

### Auto-discovery v composer.json

**🎯 DOPORUČENO - Pouze modulio-providers:**

```json
{
    "extra": {
        "laravel": {
            "modulio-providers": [
                "VendorName\\YourModule\\YourModuleServiceProvider"
            ]
        }
    }
}
```

**📋 Alternativně - Standardní providers:**

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "VendorName\\YourModule\\YourModuleServiceProvider"
            ]
        }
    }
}
```

*Pozn: Provider musí implementovat `ModuleRegistrarInterface`*

**❌ NEDOPORUČUJE SE - Oboje současně:**

```json
{
    "extra": {
        "laravel": {
            "providers": [
                "..."
            ],
            "modulio-providers": [
                "..."
            ]
        }
    }
}
```

*Může způsobit dvojí registraci modulu*

## 🎯 Pokročilé funkce / Advanced Features

### Hierarchické menu s vnořenými položkami

```php
->nav(
    Navigation::make('admin', fn() => [
        NavigationGroup::make('E-commerce', fn() => [
            NavigationItem::make('Produkty')
                ->icon('heroicon-s-cube')
                ->route('products.index')
                ->permission('products.view')
                ->badge('150')
                ->order(1),
            NavigationItem::make('Kategorie')
                ->icon('heroicon-s-folder')
                ->route('categories.index')
                ->permission('categories.view')
                ->order(2),
            NavigationItem::make('Objednávky')
                ->icon('heroicon-s-shopping-cart')
                ->route('orders.index')
                ->permission('orders.view')
                ->badge('5')
                ->classes(['highlight'])
                ->order(3),
        ])
        ->icon('heroicon-s-shopping-bag')
        ->collapsed(false)
        ->order(30),
    ])
)
```

### Více typů menu

```php
// Admin navigace
->nav(Navigation::make('admin', fn() => [...]))

// Front-end navigace  
->nav(Navigation::make('default', fn() => [...]))

// Mobilní menu
->nav(Navigation::make('mobile', fn() => [...]))

// Footer menu
->nav(Navigation::make('footer', fn() => [...]))
```

### Metadata a verze z Composeru

```php
$moduleManager->register('blog')
    ->versionFromComposer(__DIR__ . '/../composer.json')
    ->meta('author', 'NyonCode')
    ->meta('homepage', 'https://nyoncode.com')
    ->meta('support_email', 'support@nyoncode.com')
    ->meta('min_php_version', '8.1')
    ->register();
```

## 🎨 Blade direktivy / Blade Directives

```blade
{{-- Kontrola existence modulu --}}
@modulioModule('blog')
    <p>Blog modul je aktivní</p>
@endModulioModule

{{-- Kontrola oprávnění --}}
@modulioPermission('blog.create')
    <a href="{{ route('blog.create') }}">Nový příspěvek</a>
@endModulioPermission

{{-- Vykreslení navigace --}}
@modulioNavigation('admin')
```

## 🔧 Facade použití / Facade Usage

```php
use NyonCode\LaravelModulio\Facades\Modulio;

// Získání všech modulů
$modules = Modulio::getModules();

// Kontrola existence modulu
if (Modulio::hasModule('blog')) {
    // Modul existuje
}

// Získání navigace
$navigation = Modulio::getNavigationItems('admin');

// Smazání cache
Modulio::clearCache();

// Získání konkrétního modulu
$blogModule = Modulio::getModule('blog');
```

## ⚡ Livewire komponenty / Livewire Components

### Navigační komponenta

```blade
<livewire:modulio.navigation menu="admin" template="sidebar" />
```

### Seznam modulů

```blade
<livewire:modulio.module-list show-actions="true" />
```

## 🗂️ Struktura modulu / Module Structure

```
your-module/
├── src/
│   ├── YourModuleServiceProvider.php
│   ├── Controllers/
│   ├── Models/
│   ├── Livewire/
│   └── ...
├── config/
│   └── your-module.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   └── assets/
├── routes/
│   ├── web.php
│   └── api.php
└── composer.json
```

## 🔒 Oprávnění / Permissions

Systém automaticky vytváří oprávnění při registraci modulu:

```php
->permissions([
    'module.view',      // Zobrazení modulu
    'module.create',    // Vytváření
    'module.edit',      // Úpravy  
    'module.delete',    // Mazání
    'module.manage',    // Správa
])
```

## 🚀 Migrace / Migrations

### Automatické spuštění

```php
->migrations(__DIR__ . '/../database/migrations')
->runMigrations(true)  // Spustí migrace při registraci
->rollbackMigrations(false) // Neprovedete rollback při deregistraci
```

### Ruční správa

```bash
# Spuštění migrací konkrétního modulu
php artisan migrate --path=vendor/your-vendor/your-module/database/migrations

# Rollback migrací modulu
php artisan migrate:rollback --path=vendor/your-vendor/your-module/database/migrations
```

## 📊 Cache systém / Cache System

Laravel Modulio používá pokročilé cachování pro maximální výkon:

```php
// Konfigurace cache
'cache_enabled' => true,
'cache_ttl' => 60, // minuty
'cache_driver' => 'redis', // null = výchozí Laravel cache
'cache_prefix' => 'modulio',

// Preload kritických dat
'preload_cache' => true,
'navigation_eager_loading' => true,
```

## 🎛️ Konfigurace / Configuration

Hlavní konfigurační soubor `config/modulio.php` obsahuje:

### Základní nastavení

```php
// Cache systém
'cache_enabled' => true,
'cache_ttl' => 60,

// Auto-discovery
'auto_discovery' => true,

// Migrace
'auto_migrate' => false,
'auto_rollback' => false,

// Oprávnění
'auto_create_permissions' => true,
'permission_prefix' => 'module.',
```

### Navigace

```php
// Dostupné menu typy
'available_menus' => [
    'default' => 'Výchozí menu',
    'admin' => 'Admin menu',
    'sidebar' => 'Postranní menu',
    'mobile' => 'Mobilní menu',
],

// Templates
'navigation_templates' => [
    'default' => 'modulio::navigation.default',
    'bootstrap' => 'modulio::navigation.bootstrap',
    'tailwind' => 'modulio::navigation.tailwind',
],
```

## 🔐 Bezpečnost / Security

```php
// Whitelist povolených modulů
'allowed_modules' => '',

// Blacklist zakázaných modulů  
'forbidden_modules' => '',

// Runtime instalace
'allow_runtime_installation' => false,

// Ověření podpisů
'verify_module_signatures' => false,
```

## 📝 Eventy / Events

Laravel Modulio vyvolává následující eventy:

```php
// Registrace modulu
\NyonCode\LaravelModulio\Events\ModuleRegistered::class

// Deregistrace modulu
\NyonCode\LaravelModulio\Events\ModuleDeregistered::class

// Před spuštěním migrací
\NyonCode\LaravelModulio\Events\BeforeModuleMigrations::class

// Po spuštění migrací
\NyonCode\LaravelModulio\Events\AfterModuleMigrations::class

// Vytvoření oprávnění
\NyonCode\LaravelModulio\Events\ModulePermissionsCreated::class

// Aktualizace cache
\NyonCode\LaravelModulio\Events\ModuleCacheUpdated::class
```

### Posluchače eventů

```php
// V EventServiceProvider
protected $listen = [
    \NyonCode\LaravelModulio\Events\ModuleRegistered::class => [
        \App\Listeners\LogModuleRegistration::class,
        \App\Listeners\SendModuleNotification::class,
    ],
];
```

## 🛠️ Artisan příkazy / Artisan Commands

```bash
# Seznam všech registrovaných modulů
php artisan modulio:list

# Smazání cache modulů
php artisan modulio:clear-cache

# Instalace modulu (pokud povoleno)
php artisan modulio:install vendor/module-name
```

## 🔧 Troubleshooting

### Modul se neregistruje automaticky

1. Zkontrolujte `composer.json` - sekce `extra.laravel.modulio-providers`
2. Spusťte `composer dump-autoload`
3. Zkontrolujte konfiguraci `auto_discovery`

### Navigace se nezobrazuje

1. Zkontrolujte oprávnění uživatele
2. Ověřte cache - `php artisan modulio:clear-cache`
3. Zkontrolujte konfiguraci menu typu

### Migrace se nespouští

1. Nastavte `runMigrations(true)` v registraci modulu
2. Nebo povolit `auto_migrate` globálně v konfiguraci
3. Zkontrolujte cesty k migračním souborům

## 📚 Příklady modulů / Module Examples

### Blog systém

Kompletní příklad najdete v souboru `examples/BlogServiceProvider.php`

### E-shop modul

```php
$moduleManager->register('eshop')
    ->version('2.1.0')
    ->permissions(['eshop.view', 'eshop.manage', 'eshop.orders'])
    ->nav(
        Navigation::make('admin', fn() => [
            NavigationGroup::make('E-shop', fn() => [
                NavigationItem::make('Produkty')->route('eshop.products')->order(1),
                NavigationItem::make('Objednávky')->route('eshop.orders')->badge('12')->order(2),
                NavigationItem::make('Zákazníci')->route('eshop.customers')->order(3),
            ])->order(20),
        ])
    )
    ->register();
```

## 🤝 Přispívání / Contributing

1. Fork projekt
2. Vytvořte feature branch (`git checkout -b feature/amazing-feature`)
3. Commit změny (`git commit -m 'Add amazing feature'`)
4. Push do branch (`git push origin feature/amazing-feature`)
5. Otevřete Pull Request

## 📄 Licence

Tento projekt je licencován pod MIT licencí - viz [LICENSE](LICENSE) soubor.

## 👨‍💻 Autor

**NyonCode**

- Website: [https://nyoncode.com](https://nyoncode.com)
- Email: info@nyoncode.com
- GitHub: [@nyoncode](https://github.com/nyoncode)

## 🙏 Poděkování

- [Laravel](https://laravel.com) - Za úžasný framework
- [Spatie](https://spatie.be) - Za Permission balíček
- [Livewire](https://laravel-livewire.com) - Za reaktivní komponenty
- [Heroicons](https://heroicons.com) - Za krásné ikony

---

**Laravel Modulio** - Profesionální modulární systém pro Laravel ⚡