<?php

namespace App\Providers;

use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\BroadcastMidMonthCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\CustomerActivationCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPayLaterCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\InfoPembayaranCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\OverdueBroadcastCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\ReplyMessageCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Commands\TestMessageCommand;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\BroadcastMidMonthHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\CustomerActivationHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\InfoPayLaterHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\InfoPembayaranHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\OverdueBroadcastHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\ReplyMessageHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\Handlers\TestMessageHandler;
use App\Services\Whatsapp\CloudAPI\Templates\Runtime\TemplateHandlerRegistry;
use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(TemplateHandlerRegistry::class, function () {
            $registry = new TemplateHandlerRegistry();

            foreach ($this->handlers() as $command => $handler) {
                $registry->register($command, $handler);
            }

            return $registry;
        });
    }

    private function handlers(): array
    {
        return [
            InfoPembayaranCommand::class => InfoPembayaranHandler::class,
            InfoPayLaterCommand::class => InfoPayLaterHandler::class,
            CustomerActivationCommand::class => CustomerActivationHandler::class,
            BroadcastMidMonthCommand::class => BroadcastMidMonthHandler::class,
            TestMessageCommand::class => TestMessageHandler::class,
            OverdueBroadcastCommand::class => OverdueBroadcastHandler::class,
            ReplyMessageCommand::class => ReplyMessageHandler::class,
        ];
    }
}
