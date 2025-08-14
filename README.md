# INSTALLATION GUIDE / PRŮVODCE INSTALACÍ

## English

### 1. Install the package

```bash
composer require nyoncode/laravel-modulio
```

### 2. Publish configuration

```bash
php artisan vendor:publish --tag=modulio-config
```

### 3. Run migrations (optional)

```bash
php artisan migrate
```

### 4. Clear cache

```bash
php artisan modulio:clear-cache
```

### 5. Auto-discover modules

```bash
php artisan modulio:discover
```

## Czech / Česky

### 1. Nainstalujte balíček

```bash
composer require nyoncode/laravel-modulio
```

### 2. Publikujte konfiguraci

```bash
php artisan vendor:publish --tag=modulio-config
```

### 3. Spusťte migrace (volitelné)

```bash
php artisan migrate
```

### 4. Vymažte cache

```bash
php artisan modulio:clear-cache
```

### 5. Automaticky objevte moduly

```bash
php artisan modulio:discover
```

## USAGE EXAMPLES / PŘÍKLADY POUŽITÍ

### Basic Module Registration / Základní registrace modulu

```php
use Nyoncode\LaravelModulio\ModuleManager;
use Nyoncode\LaravelModulio\Navigation\Navigation;
use Nyoncode\LaravelModulio\Navigation\NavigationItem;

public function registerModule(ModuleManager $module): ModuleManager
{
    return $module->name('My Module')
        ->version('1.0.0')
        ->description('Description of my module')
        ->author('Your Name')
        ->migrations([__DIR__ . '/../database/migrations'])
        ->runMigrations(true)
        ->routes([
            'web' => __DIR__ . '/../routes/web.php',
            'api' => __DIR__ . '/../routes/api.php',
        ])
        ->nav(
            Navigation::make('admin', fn() => [
                NavigationItem::make('Dashboard')
                    ->icon('heroicon-s-home')
                    ->route('admin.dashboard')
                    ->order(1),
            ])
        );
}
```

### Getting Navigation / Získání navigace

```php
// In your controller / Ve vašem controlleru
$navigation = modulio()->getNavigation('admin');

// In Blade template / V Blade šabloně
@foreach(module_navigation('admin') as $item)
    <a href="{{ $item->getUrl() }}">{{ $item->getName() }}</a>
@endforeach
```

### Using with Livewire / Použití s Livewire

```php
// In your Livewire component / Ve vaší Livewire komponentě
<livewire:modulio::navigation menu="admin" :show-icons="true" />
<livewire:modulio::module-manager />
```

### Checking Module Status / Kontrola stavu modulu

```php
if (modulio()->hasModule('Blog System')) {
    $module = modulio()->getModule('Blog System');
    if ($module->isEnabled()) {
        // Module is active / Modul je aktivní
    }
}
```

## ADVANCED FEATURES / POKROČILÉ FUNKCE

### Custom Cache Driver / Vlastní cache driver

```php
// In config/modulio.php
'cache' => [
    'enabled' => true,
    'store' => 'redis', // Use Redis for caching / Použít Redis pro cache
    'prefix' => 'my_app_modulio',
    'ttl' => 7200, // 2 hours / 2 hodiny
],
```

### Event Listeners / Posluchače událostí

```php
// In EventServiceProvider
protected $listen = [
    'modulio.module.registered' => [
        ModuleRegisteredListener::class,
    ],
    'modulio.module.enabled' => [
        ModuleEnabledListener::class,
    ],
];
```

### Custom Permissions / Vlastní oprávnění

```php
NavigationItem::make('Users')
    ->icon('heroicon-s-users')
    ->route('admin.users.index')
    ->permissions(['users.view', 'admin.access'])
    ->order(5);
```

### Hierarchical Navigation / Hierarchická navigace

```php
NavigationGroup::make('User Management', fn() => [
    NavigationItem::make('Users')
        ->route('admin.users.index')
        ->order(1),
    NavigationItem::make('Roles')
        ->route('admin.roles.index')
        ->order(2),
    NavigationGroup::make('Advanced', fn() => [
        NavigationItem::make('Permissions')
            ->route('admin.permissions.index')
            ->order(1),
        NavigationItem::make('Audit Log')
            ->route('admin.audit.index')
            ->order(2),
    ])->order(10),
])->order(20);
```

## BEST PRACTICES / NEJLEPŠÍ PRAKTIKY

### 1. Module Structure / Struktura modulu

```
your-module/
├── src/
│   ├── Providers/
│   │   └── ModuleServiceProvider.php
│   ├── Controllers/
│   ├── Models/
│   ├── Livewire/
│   └── Services/
├── database/
│   └── migrations/
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   ├── views/
│   └── lang/
├── config/
│   └── module.php
└── composer.json
```

### 2. Naming Conventions / Konvence pojmenování

- Use PascalCase for module names / Použijte PascalCase pro názvy modulů
- Use semantic versioning / Použijte sémantické verzování
- Prefix routes with module name / Předpony routes s názvem modulu
- Use consistent naming for permissions / Použijte konzistentní pojmenování pro oprávnění

### 3. Performance Optimization / Optimalizace výkonu

- Always enable caching in production / Vždy povolte cachování v produkci
- Use Redis or Memcached for better performance / Použijte Redis nebo Memcached pro lepší výkon
- Minimize database queries in navigation / Minimalizujte databázové dotazy v navigaci
- Use lazy loading for module resources / Použijte lazy loading pro zdroje modulů

### 4. Security / Bezpečnost

- Always check permissions in navigation items / Vždy kontrolujte oprávnění v navigačních položkách
- Validate module dependencies / Validujte závislosti modulů
- Use middleware for route protection / Použijte middleware pro ochranu routes
- Sanitize user input in module configurations / Sanitizujte uživatelské vstupy v konfiguracích modulů

## TROUBLESHOOTING / ŘEŠENÍ PROBLÉMŮ

### Common Issues / Běžné problémy

1. **Module not discovered / Modul není objevován**
   ```bash
   php artisan modulio:discover
   php artisan modulio:clear-cache
   ```

2. **Navigation not showing / Navigace se nezobrazuje**
   - Check permissions / Zkontrolujte oprávnění
   - Verify module is enabled / Ověřte, že je modul povolen
   - Clear cache / Vymažte cache

3. **Migrations not running / Migrace se nespouštějí**
   ```php
   ->runMigrations(true) // Enable auto migrations / Povolit automatické migrace
   ```

4. **Route conflicts / Konflikty routes**
   - Use unique route names / Použijte unikátní názvy routes
   - Check for duplicate registrations / Zkontrolujte duplicitní registrace

### Debug Mode / Debug režim

```php
// In config/modulio.php
'debug' => true, // Enable detailed logging / Povolit podrobné logování
```

## EXTENDING MODULIO / ROZŠÍŘENÍ MODULIO

### Custom Navigation Item Types / Vlastní typy navigačních položek

```php
class CustomNavigationItem extends NavigationItem
{
    protected string $customProperty;
    
    public function customMethod(string $value): self
    {
        $this->customProperty = $value;
        return $this;
    }
}
```

### Custom Module Types / Vlastní typy modulů

```php
class CustomModule extends Module
{
    protected array $customMetadata = [];
    
    public function setCustomData(array $data): self
    {
        $this->customMetadata = $data;
        return $this;
    }
}
```

### Event Subscribers / Odběratelé událostí

```php
class ModulioEventSubscriber
{
    public function handleModuleRegistered($event): void
    {
        // Custom logic when module is registered
        // Vlastní logika při registraci modulu
    }
    
    public function handleNavigationBuilt($event): void
    {
        // Custom logic when navigation is built
        // Vlastní logika při sestavení navigace
    }
    
    public function subscribe($events): void
    {
        $events->listen(
            'modulio.module.registered',
            [ModulioEventSubscriber::class, 'handleModuleRegistered']
        );
        
        $events->listen(
            'modulio.navigation.built',
            [ModulioEventSubscriber::class, 'handleNavigationBuilt']
        );
    }
}
```

## API REFERENCE / REFERENCE API

### ModuleManager Methods / Metody ModuleManager

- `name(string $name): self` - Set module name / Nastavit název modulu
- `version(string $version): self` - Set version / Nastavit verzi
- `description(string $description): self` - Set description / Nastavit popis
- `author(string $author): self` - Set author / Nastavit autora
- `dependencies(array $dependencies): self` - Set dependencies / Nastavit závislosti
- `versionFromComposer(?string $path): self` - Load version from composer.json / Načíst verzi z composer.json
- `config(array $configs): self` - Set configurations / Nastavit konfigurace
- `migrations(array $migrations): self` - Set migrations / Nastavit migrace
- `runMigrations(bool $run): self` - Enable/disable auto migrations / Povolit/zakázat automatické migrace
- `routes(array $routes): self` - Set routes / Nastavit routes
- `nav(...$navigations): self` - Set navigation / Nastavit navigaci
- `views(string $namespace, string $path): self` - Set views / Nastavit views
- `translations(string $namespace, string $path): self` - Set translations / Nastavit překlady

### Modulio Facade Methods / Metody Modulio Facade

- `register(ModuleInterface $module): void` - Register module / Registrovat modul
- `deregister(string $name): void` - Deregister module / Deregistrovat modul
- `getModules(): Collection` - Get all modules / Získat všechny moduly
- `getModule(string $name): ?ModuleInterface` - Get specific module / Získat konkrétní modul
- `hasModule(string $name): bool` - Check if module exists / Zkontrolovat existenci modulu
- `enableModule(string $name): void` - Enable module / Povolit modul
- `disableModule(string $name): void` - Disable module / Zakázat modul
- `getNavigation(string $menu): array` - Get navigation / Získat navigaci
- `getMenuNames(): array` - Get available menu names / Získat dostupné názvy menu
- `autoDiscover(): void` - Auto discover modules / Automaticky objevit moduly
- `clearCache(): void` - Clear cache / Vymazat cache

## LICENSE / LICENCE

This package is open-sourced software licensed under the MIT license.
Tento balíček je open-source software licencovaný pod MIT licencí.

## SUPPORT / PODPORA

For support and questions, please visit our GitHub repository or contact us at support@example.com.
Pro podporu a dotazy navštivte naši GitHub repository nebo nás kontaktujte na support@example.com.
*/
                
