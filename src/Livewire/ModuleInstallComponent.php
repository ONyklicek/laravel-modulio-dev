<?php

namespace NyonCode\LaravelModulio\Livewire;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire komponenta pro instalaci modulů
 *
 * Umožňuje instalaci nových modulů přes webové rozhraní
 * s progress barem a real-time feedback.
 *
 * @package NyonCode\LaravelModulio\Livewire
 *
 * ---
 *
 * Livewire Module Installation Component
 *
 * Allows installation of new modules via web interface
 * with progress bar and real-time feedback.
 */
class ModuleInstallComponent extends Component
{
    /**
     * Název balíčku k instalaci
     * Package name to install
     *
     * @var string
     */
    public string $packageName = '';

    /**
     * Stav instalace
     * Installation status
     *
     * @var string
     */
    public string $installationStatus = 'idle'; // idle, installing, completed, error

    /**
     * Progress instalace (0-100)
     * Installation progress (0-100)
     *
     * @var int
     */
    public int $progress = 0;

    /**
     * Zprávy o průběhu
     * Progress messages
     *
     * @var array
     */
    public array $progressMessages = [];

    /**
     * Chybové zprávy
     * Error messages
     *
     * @var array
     */
    public array $errors = [];

    /**
     * Zda instalovat jako dev
     * Whether to install as dev
     *
     * @var bool
     */
    public bool $installAsDev = false;

    /**
     * Zda spustit migrace
     * Whether to run migrations
     *
     * @var bool
     */
    public bool $runMigrations = true;

    /**
     * Spustí instalaci modulu
     * Start module installation
     */
    public function installModule(): void
    {
        $this->validate([
            'packageName' => 'required|string|min:3',
        ], [
            'packageName.required' => 'Zadejte název balíčku',
            'packageName.min' => 'Název balíčku musí mít alespoň 3 znaky',
        ]);

        if (!config('modulio.allow_runtime_installation', false)) {
            $this->addError('packageName', 'Runtime instalace modulů není povolena.');
            return;
        }

        $this->installationStatus = 'installing';
        $this->progress = 0;
        $this->progressMessages = [];
        $this->errors = [];

        $this->addProgressMessage('Zahajování instalace...');

        // Simulace kroků instalace pro demo účely
        // Installation steps simulation for demo purposes
        $this->simulateInstallation();
    }

    /**
     * Simuluje instalační proces
     * Simulates installation process
     */
    protected function simulateInstallation(): void
    {
        // Krok 1: Validace balíčku
        // Step 1: Package validation
        $this->updateProgress(10, 'Validace balíčku...');

        // Krok 2: Stahování přes Composer
        // Step 2: Downloading via Composer
        $this->updateProgress(30, 'Stahování balíčku...');

        // Krok 3: Registrace modulu
        // Step 3: Module registration
        $this->updateProgress(60, 'Registrace modulu...');

        // Krok 4: Spuštění migrací
        // Step 4: Running migrations
        if ($this->runMigrations) {
            $this->updateProgress(80, 'Spouštění migrací...');
        }

        // Krok 5: Vyčištění cache
        // Step 5: Clearing cache
        $this->updateProgress(95, 'Vyčištění cache...');

        // Dokončení
        // Completion
        $this->updateProgress(100, 'Instalace dokončena!');
        $this->installationStatus = 'completed';

        $this->dispatch('module-installed', $this->packageName);
    }

    /**
     * Aktualizuje progress
     * Updates progress
     *
     * @param  int  $progress
     * @param  string  $message
     */
    protected function updateProgress(int $progress, string $message): void
    {
        $this->progress = $progress;
        $this->addProgressMessage($message);
    }

    /**
     * Přidá zprávu o průběhu
     * Adds progress message
     *
     * @param  string  $message
     */
    protected function addProgressMessage(string $message): void
    {
        $this->progressMessages[] = [
            'message' => $message,
            'time' => now()->format('H:i:s'),
        ];
    }

    /**
     * Resetuje formulář
     * Resets form
     */
    public function resetForm(): void
    {
        $this->packageName = '';
        $this->installationStatus = 'idle';
        $this->progress = 0;
        $this->progressMessages = [];
        $this->errors = [];
        $this->installAsDev = false;
        $this->runMigrations = true;
    }

    /**
     * Vykreslí komponentu
     * Renders component
     *
     * @return View
     */
    public function render(): View
    {
        return view('modulio::livewire.module-install', [
            'isInstalling' => $this->installationStatus === 'installing',
            'isCompleted' => $this->installationStatus === 'completed',
            'hasErrors' => $this->installationStatus === 'error',
        ]);
    }
}