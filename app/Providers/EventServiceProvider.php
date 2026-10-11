<?php

namespace App\Providers;

use App\Events\SingleNotification\CustomerActivated;
use App\Events\SingleNotification\DirectMessageRequested;
use App\Events\SingleNotification\OrderCreated;
use App\Events\SingleNotification\PackageChangesRequest;
use App\Events\SingleNotification\PayLaterRequest;
use App\Events\SingleNotification\PaymentCancelled;
use App\Events\SingleNotification\PaymentSucceeded;
use App\Events\SingleNotification\TelegramBotLinkRequest;
use App\Events\SingleNotification\WhatsAppTestRequest;
use App\Listeners\Telegram\SendTelegramCustomerActivationListener;
use App\Listeners\Telegram\SendTelegramPackageChangesListener;
use App\Listeners\Telegram\SendTelegramPayLaterListener;
use App\Listeners\WhatsApp\SendDirectNotificationListener;
use App\Listeners\WhatsApp\SendCustomerActivationListener;
use App\Listeners\WhatsApp\SendOrderCreatedListener;
use App\Listeners\WhatsApp\SendPackageChangesListener;
use App\Listeners\WhatsApp\SendPayLaterListener;
use App\Listeners\WhatsApp\SendPaymentCancellationListener;
use App\Listeners\WhatsApp\SendPaymentSucceededListener;
use App\Listeners\WhatsApp\SendTelegramBotLinkListener;
use App\Listeners\WhatsApp\SendWhatsAppTestListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PaymentSucceeded::class => [
            SendPaymentSucceededListener::class,
        ],
        PaymentCancelled::class => [
            SendPaymentCancellationListener::class,
        ],
        PayLaterRequest::class => [
            SendPayLaterListener::class,
            SendTelegramPayLaterListener::class,
        ],
        CustomerActivated::class => [
            SendCustomerActivationListener::class,
            SendTelegramCustomerActivationListener::class
        ],
        PackageChangesRequest::class => [
            SendPackageChangesListener::class,
            SendTelegramPackageChangesListener::class
        ],
        OrderCreated::class => [
            SendOrderCreatedListener::class,
        ],
        TelegramBotLinkRequest::class => [
            SendTelegramBotLinkListener::class
        ],
        WhatsAppTestRequest::class => [
            SendWhatsAppTestListener::class
        ],
        DirectMessageRequested::class => [
            SendDirectNotificationListener::class
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
