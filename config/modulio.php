<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Základní nastavení modulu / Basic Module Settings
    |--------------------------------------------------------------------------
    |
    | Tyto nastavení řídí základní chování modulárního systému včetně
    | cache, auto-discovery a dalších funkcí.
    |
    | These settings control the basic behavior of the modular system including
    | cache, auto-discovery and other features.
    |
    */

    /**
     * Povolení cache systému
     * Enable cache system
     */
    'cache_enabled' => env('MODULIO_CACHE_ENABLED', true),

    /**
     * TTL cache v minutách
     * Cache TTL in minutes
     */
    'cache_ttl' => env('MODULIO_CACHE_TTL', 60),

    /**
     * Cache driver (null = použije výchozí Laravel cache)
     * Cache driver (null = uses default Laravel cache)
     */
    'cache_driver' => env('MODULIO_CACHE_DRIVER', null),

    /**
     * Prefix pro cache klíče
     * Prefix for cache keys
     */
    'cache_prefix' => env('MODULIO_CACHE_PREFIX', 'modulio'),

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery nastavení / Auto-Discovery Settings
    |--------------------------------------------------------------------------
    |
    | Automatické nalezení a registrace modulů z Composer balíčků.
    | Automatic discovery and registration of modules from Composer packages.
    |
    */

    /**
     * Povolení auto-discovery modulů
     * Enable module auto-discovery
     */
    'auto_discovery' => env('MODULIO_AUTO_DISCOVERY', true),

    /**
     * Cesty pro hledání modulů (relativně k base_path)
     * Paths for module discovery (relative to base_path)
     */
    'discovery_paths' => [
        'vendor/*/src',
        'packages/*/src',
        'modules/*/src',
    ],

    /**
     * Vzory pro ignorování při auto-discovery
     * Patterns to ignore during auto-discovery
     */
    'discovery_ignore_patterns' => [
        '*/tests/*',
        '*/test/*',
        '*/Tests/*',
        '*/Test/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Migrace nastavení / Migration Settings
    |--------------------------------------------------------------------------
    |
    | Nastavení pro automatické spouštění a správu migrací modulů.
    | Settings for automatic execution and management of module migrations.
    |
    */

    /**
     * Automatické spouštění migrací při registraci modulu
     * Automatic execution of migrations on module registration
     */
    'auto_migrate' => env('MODULIO_AUTO_MIGRATE', false),

    /**
     * Automatický rollback migrací při deregistraci modulu
     * Automatic rollback of migrations on module deregistration
     */
    'auto_rollback' => env('MODULIO_AUTO_ROLLBACK', false),

    /**
     * Timeout pro migrace v sekundách
     * Migration timeout in seconds
     */
    'migration_timeout' => env('MODULIO_MIGRATION_TIMEOUT', 300),

    /*
    |--------------------------------------------------------------------------
    | Oprávnění nastavení / Permission Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurace pro správu oprávnění modulů pomocí Spatie Permission.
    | Configuration for module permission management using Spatie Permission.
    |
    */

    /**
     * Automatické vytváření oprávnění při registraci modulu
     * Automatic permission creation on module registration
     */
    'auto_create_permissions' => env('MODULIO_AUTO_CREATE_PERMISSIONS', true),

    /**
     * Prefix pro oprávnění modulů
     * Prefix for module permissions
     */
    'permission_prefix' => env('MODULIO_PERMISSION_PREFIX', 'module.'),

    /**
     * Guard pro oprávnění
     * Permission guard
     */
    'permission_guard' => env('MODULIO_PERMISSION_GUARD', 'web'),

    /**
     * Automatické mazání oprávnění při deregistraci
     * Automatic permission deletion on deregistration
     */
    'auto_delete_permissions' => env('MODULIO_AUTO_DELETE_PERMISSIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Navigace nastavení / Navigation Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurace pro správu navigačních menu modulů.
    | Configuration for module navigation menu management.
    |
    */

    /**
     * Výchozí menu pro navigaci
     * Default navigation menu
     */
    'default_menu' => env('MODULIO_DEFAULT_MENU', 'default'),

    /**
     * Dostupné menu typy
     * Available menu types
     */
    'available_menus' => [
        'default' => 'Výchozí menu / Default menu',
        'admin' => 'Admin menu',
        'sidebar' => 'Postranní menu / Sidebar menu',
        'footer' => 'Zápatí menu / Footer menu',
        'mobile' => 'Mobilní menu / Mobile menu',
    ],

    /**
     * Výchozí template pro vykreslování navigace
     * Default template for navigation rendering
     */
    'navigation_template' => env('MODULIO_NAVIGATION_TEMPLATE', 'default'),

    /**
     * Dostupné templates pro navigaci
     * Available navigation templates
     */
    'navigation_templates' => [
        'default' => 'modulio::navigation.default',
        'bootstrap' => 'modulio::navigation.bootstrap',
        'tailwind' => 'modulio::navigation.tailwind',
        'sidebar' => 'modulio::navigation.sidebar',
        'breadcrumb' => 'modulio::navigation.breadcrumb',
    ],

    /**
     * Řešení konfliktů pořadí (alphabet, registration, random)
     * Order conflict resolution (alphabet, registration, random)
     */
    'order_conflict_resolution' => env('MODULIO_ORDER_CONFLICT_RESOLUTION', 'alphabet'),

    /*
    |--------------------------------------------------------------------------
    | Routing nastavení / Routing Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurace pro routy modulárního systému.
    | Configuration for modular system routes.
    |
    */

    /**
     * Načtení admin rout
     * Load admin routes
     */
    'load_routes' => env('MODULIO_LOAD_ROUTES', true),

    /**
     * Prefix pro admin routy
     * Prefix for admin routes
     */
    'route_prefix' => env('MODULIO_ROUTE_PREFIX', 'modulio'),

    /**
     * Middleware pro admin routy
     * Middleware for admin routes
     */
    'route_middleware' => [
        'web',
        'auth',
        'modulio.permission:modulio.access',
    ],

    /**
     * Načtení API rout
     * Load API routes
     */
    'load_api_routes' => env('MODULIO_LOAD_API_ROUTES', false),

    /**
     * Middleware pro API routy
     * Middleware for API routes
     */
    'api_middleware' => [
        'api',
        'auth:sanctum',
        'modulio.permission:modulio.api.access',
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire nastavení / Livewire Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurace pro Livewire komponenty modulárního systému.
    | Configuration for modular system Livewire components.
    |
    */

    /**
     * Povolení Livewire komponent
     * Enable Livewire components
     */
    'livewire_enabled' => env('MODULIO_LIVEWIRE_ENABLED', true),

    /**
     * Prefix pro Livewire komponenty
     * Prefix for Livewire components
     */
    'livewire_prefix' => env('MODULIO_LIVEWIRE_PREFIX', 'modulio'),

    /*
    |--------------------------------------------------------------------------
    | Logování a ladění / Logging and Debugging
    |--------------------------------------------------------------------------
    |
    | Nastavení pro logování událostí modulárního systému.
    | Settings for logging modular system events.
    |
    */

    /**
     * Povolení logování událostí
     * Enable event logging
     */
    'log_events' => env('MODULIO_LOG_EVENTS', true),

    /**
     * Channel pro logování
     * Logging channel
     */
    'log_channel' => env('MODULIO_LOG_CHANNEL', 'daily'),

    /**
     * Úroveň logování
     * Logging level
     */
    'log_level' => env('MODULIO_LOG_LEVEL', 'info'),

    /**
     * Debug mode pro vývojáře
     * Debug mode for developers
     */
    'debug_mode' => env('MODULIO_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Výkon a optimalizace / Performance and Optimization
    |--------------------------------------------------------------------------
    |
    | Nastavení pro optimalizaci výkonu modulárního systému.
    | Settings for modular system performance optimization.
    |
    */

    /**
     * Eager loading pro navigaci
     * Eager loading for navigation
     */
    'navigation_eager_loading' => env('MODULIO_NAVIGATION_EAGER_LOADING', true),

    /**
     * Maximální počet modulů
     * Maximum number of modules
     */
    'max_modules' => env('MODULIO_MAX_MODULES', 100),

    /**
     * Preload kritických cache klíčů
     * Preload critical cache keys
     */
    'preload_cache' => env('MODULIO_PRELOAD_CACHE', true),

    /**
     * Komprese cache dat
     * Cache data compression
     */
    'cache_compression' => env('MODULIO_CACHE_COMPRESSION', false),

    /*
    |--------------------------------------------------------------------------
    | Bezpečnost / Security
    |--------------------------------------------------------------------------
    |
    | Bezpečnostní nastavení pro modulární systém.
    | Security settings for modular system.
    |
    */

    /**
     * Whitelist povolených modulů (prázdné = všechny povolené)
     * Whitelist of allowed modules (empty = all allowed)
     */
    'allowed_modules' => env('MODULIO_ALLOWED_MODULES', ''),

    /**
     * Blacklist zakázaných modulů
     * Blacklist of forbidden modules
     */
    'forbidden_modules' => env('MODULIO_FORBIDDEN_MODULES', ''),

    /**
     * Povolení instalace modulů za běhu
     * Allow runtime module installation
     */
    'allow_runtime_installation' => env('MODULIO_ALLOW_RUNTIME_INSTALLATION', false),

    /**
     * Ověření podpisů modulů
     * Module signature verification
     */
    'verify_module_signatures' => env('MODULIO_VERIFY_SIGNATURES', false),

    /*
    |--------------------------------------------------------------------------
    | Notifikace nastavení / Notification Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurace pro odesílání notifikací o událostech modulárního systému.
    | Configuration for sending notifications about modular system events.
    |
    */

    /**
     * Povolení notifikací
     * Enable notifications
     */
    'notifications' => [
        'enabled' => env('MODULIO_NOTIFICATIONS_ENABLED', false),

        'channels' => [
            'mail',
            'database',
            // 'slack',
            // 'webhook',
            // 'discord',
        ],

        'email' => [
            'recipients' => [
                // 'admin@example.com',
            ],
        ],

        'slack' => [
            'webhook_url' => env('MODULIO_SLACK_WEBHOOK'),
        ],

        'webhook' => [
            'urls' => [
                // env('MODULIO_WEBHOOK_URL'),
            ],
        ],
    ],

    /**
     * Automatická synchronizace oprávnění s rolemi
     * Automatic permission sync with roles
     */
    'auto_sync_permissions_with_roles' => env('MODULIO_AUTO_SYNC_PERMISSIONS', false),

    /**
     * Mapování oprávnění modulů na role
     * Module permission to role mappings
     */
    'permission_role_mappings' => [
        // 'blog' => [
        //     'admin' => ['blog.*'],
        //     'editor' => ['blog.view', 'blog.create', 'blog.edit'],
        //     'author' => ['blog.view', 'blog.create'],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Listeners nastavení / Event Listeners Settings
    |--------------------------------------------------------------------------
    |
    | Mapování eventů na custom listenery pro rozšíření funkcionality.
    | Mapping events to custom listeners for extending functionality.
    |
    */

    /**
     * Vlastní event listenery
     * Custom event listeners
     */
    'event_listeners' => [
        \NyonCode\LaravelModulio\Events\ModuleRegistered::class => [
            \NyonCode\LaravelModulio\Listeners\LogModuleRegistration::class,
            \NyonCode\LaravelModulio\Listeners\ClearNavigationCache::class,
        ],
        \NyonCode\LaravelModulio\Events\ModuleDeregistered::class => [
            \NyonCode\LaravelModulio\Listeners\LogModuleDeregistration::class,
            \NyonCode\LaravelModulio\Listeners\ClearModuleCache::class,
        ],
    ],

    /**
     * Vlastní service providers pro rozšíření
     * Custom service providers for extensions
     */
    'extension_providers' => [
        // \App\Providers\ModulioExtensionProvider::class,
    ],

    /**
     * Hooks pro vlastní zpracování
     * Hooks for custom processing
     */
    'hooks' => [
        'before_module_registration' => [],
        'after_module_registration' => [],
        'before_module_deregistration' => [],
        'after_module_deregistration' => [],
        'before_navigation_render' => [],
        'after_navigation_render' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Experimentální funkce / Experimental Features
    |--------------------------------------------------------------------------
    |
    | Experimentální funkce které mohou být nestabilní.
    | Experimental features that may be unstable.
    |
    */

    /**
     * Povolení experimentálních funkcí
     * Enable experimental features
     */
    'experimental_features' => env('MODULIO_EXPERIMENTAL', false),

    /**
     * Hot reloading modulů při vývoji
     * Hot reloading of modules during development
     */
    'hot_reload' => env('MODULIO_HOT_RELOAD', false),

    /**
     * GraphQL API pro správu modulů
     * GraphQL API for module management
     */
    'graphql_api' => env('MODULIO_GRAPHQL_API', false),

    /**
     * WebSocket notifikace o změnách
     * WebSocket notifications for changes
     */
    'websocket_notifications' => env('MODULIO_WEBSOCKET_NOTIFICATIONS', false),
];