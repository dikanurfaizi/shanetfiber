<?php

namespace App\Providers;

use App\Services\WhatsAppWebhook\Handlers\GroupHandler;
use App\Services\WhatsAppWebhook\Handlers\MediaMessageHandler;
use App\Services\WhatsAppWebhook\Handlers\MessageAckHandler;
use App\Services\WhatsAppWebhook\Handlers\TextMessageHandler;
use App\Services\WhatsAppWebhook\WebhookEventRouter;
use App\Services\WhatsAppWebhook\WebhookValidator;
use Illuminate\Support\ServiceProvider;

class WhatsAppWebhookServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(WebhookValidator::class, function ($app) {
            return new WebhookValidator(
                secret: env('WHATSAPP_WEBHOOK_SECRET', 'secret')
            );
        });

        $this->app->singleton(WebhookEventRouter::class, function ($app) {
            return new WebhookEventRouter(
                $app->make(MessageAckHandler::class),
                $app->make(GroupHandler::class),
                $app->make(TextMessageHandler::class),
                $app->make(MediaMessageHandler::class),
            );
        });
    }

    public function boot()
    {
        //
    }
}
