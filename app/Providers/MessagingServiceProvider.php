<?php
// app/Providers/MessagingServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Messaging\MessageActionRegistry;
use App\Services\Messaging\TemplateEngine;
use App\Services\Messaging\MessageBuilder;

class MessagingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessageActionRegistry::class, fn() => new MessageActionRegistry());
        $this->app->singleton(TemplateEngine::class, fn() => new TemplateEngine());
        $this->app->singleton(MessageBuilder::class, fn() => new MessageBuilder());
    }
}
