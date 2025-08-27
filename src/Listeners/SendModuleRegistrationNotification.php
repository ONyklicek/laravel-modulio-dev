<?php

namespace NyonCode\LaravelModulio\Listeners;

use Illuminate\Support\Facades\Log;
use Mail;
use NyonCode\LaravelModulio\Events\ModuleRegistered;
use NyonCode\LaravelModulio\Mail\ModuleRegisteredMail;

/**
 * Listener pro notifikace o registraci modulu
 *
 * Odesílá notifikace administrátorům nebo do systémů
 * o nově registrovaných modulech.
 *
 * @package NyonCode\LaravelModulio\Listeners
 *
 * ---
 *
 * Module Registration Notification Listener
 *
 * Sends notifications to administrators or systems
 * about newly registered modules.
 */
class SendModuleRegistrationNotification
{
    /**
     * Zpracuje event registrace modulu
     * Handle module registration event
     *
     * @param ModuleRegistered $event
     */
    public function handle(ModuleRegistered $event): void
    {
        if (!config('modulio.notifications.enabled', false)) {
            return;
        }

        $notificationChannels = config('modulio.notifications.channels', []);

        foreach ($notificationChannels as $channel) {
            match ($channel) {
                'email' => $this->sendEmailNotification($event),
                'slack' => $this->sendSlackNotification($event),
                'webhook' => $this->sendWebhookNotification($event),
                'database' => $this->storeDatabaseNotification($event),
                default => null,
            };
        }
    }

    /**
     * Odešle email notifikaci
     * Send email notification
     *
     * @param ModuleRegistered $event
     */
    protected function sendEmailNotification(ModuleRegistered $event): void
    {
        $recipients = config('modulio.notifications.email.recipients', []);

        if (empty($recipients)) {
            return;
        }

        // Zde by byla implementace odesílání emailu
        // Email sending implementation would be here
        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(
                    new ModuleRegisteredMail($event)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send module registration email', [
                    'recipient' => $recipient,
                    'module' => $event->getModuleName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Odešle Slack notifikaci
     * Send Slack notification
     *
     * @param ModuleRegistered $event
     */
    protected function sendSlackNotification(ModuleRegistered $event): void
    {
        $webhookUrl = config('modulio.notifications.slack.webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        $message = [
            'text' => "🚀 Nový modul byl registrován",
            'attachments' => [
                [
                    'color' => 'good',
                    'fields' => [
                        [
                            'title' => 'Název modulu',
                            'value' => $event->getModuleName(),
                            'short' => true,
                        ],
                        [
                            'title' => 'Verze',
                            'value' => $event->getModuleVersion() ?? 'N/A',
                            'short' => true,
                        ],
                        [
                            'title' => 'Oprávnění',
                            'value' => count($event->module->getPermissions()),
                            'short' => true,
                        ],
                        [
                            'title' => 'Routy',
                            'value' => count($event->module->getRoutes()),
                            'short' => true,
                        ],
                    ],
                ],
            ],
        ];

        try {
            \Http::post($webhookUrl, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send Slack notification', [
                'module' => $event->getModuleName(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Odešle webhook notifikaci
     * Send webhook notification
     *
     * @param ModuleRegistered $event
     */
    protected function sendWebhookNotification(ModuleRegistered $event): void
    {
        $webhookUrls = config('modulio.notifications.webhook.urls', []);

        if (empty($webhookUrls)) {
            return;
        }

        $payload = [
            'event' => 'module.registered',
            'module' => [
                'name' => $event->getModuleName(),
                'version' => $event->getModuleVersion(),
                'permissions_count' => count($event->module->getPermissions()),
                'routes_count' => count($event->module->getRoutes()),
                'registered_at' => $event->registeredAt->toISOString(),
            ],
        ];

        foreach ($webhookUrls as $url) {
            try {
                \Http::timeout(10)->post($url, $payload);
            } catch (\Exception $e) {
                Log::error('Failed to send webhook notification', [
                    'url' => $url,
                    'module' => $event->getModuleName(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Uloží notifikaci do databáze
     * Store notification in database
     *
     * @param ModuleRegistered $event
     */
    protected function storeDatabaseNotification(ModuleRegistered $event): void
    {
        try {
            \DB::table('modulio_notifications')->insert([
                'type' => 'module.registered',
                'module_name' => $event->getModuleName(),
                'module_version' => $event->getModuleVersion(),
                'data' => json_encode([
                    'permissions_count' => count($event->module->getPermissions()),
                    'routes_count' => count($event->module->getRoutes()),
                    'metadata' => $event->module->getMetadata(),
                ]),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store database notification', [
                'module' => $event->getModuleName(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}