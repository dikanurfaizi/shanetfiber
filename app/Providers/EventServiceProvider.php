<?php

namespace App\Providers;

use App\Events\Radius\RadiusProfileUpdateRequested;
use App\Events\Radius\RadiusUserProvisionRequested;
use App\Events\SingleNotification\ActivateCustomerEvent;
use App\Events\SingleNotification\DirectMessageRequested;
use App\Events\SingleNotification\OrderCreated;
use App\Events\SingleNotification\PackageChangesRequest;
use App\Events\SingleNotification\PayLaterRequest;
use App\Events\SingleNotification\TelegramBotLinkRequest;
use App\Events\SingleNotification\WhatsAppTestRequest;
use App\Events\UserRegisteredEvent;
use App\Events\UserVoidEvent;
use App\Events\BillingPaymentEvent;
use App\Events\UnpaymentBillingEvent;
use App\Listeners\Routerboard\HandleMikrotikUnpaymentRequested;
use App\Listeners\Radius\HandleRadiusGroupUpdateRequested;
use App\Listeners\Radius\HandleRadiusPaymentUpdateRequested;
use App\Listeners\Radius\HandleRadiusUnpaymentUpdateRequested;
use App\Listeners\Radius\HandleRadiusUserProvisionRequested;
use App\Listeners\Routerboard\SyncCustomerExpiredToMikrotik;
use App\Listeners\Routerboard\SyncCustomerToMikrotik;
use App\Listeners\Routerboard\HandleMiikrotikPaymentRequested;
use App\Listeners\Telegram\SendTelegramCustomerActivationListener;
// use App\Listeners\Telegram\SendTelegramPackageChangesListener;
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
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Observers\CustomerObserver;
use App\Observers\MikrotikObserver;
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
        ActivateCustomerEvent::class => [
            SendCustomerActivationListener::class
        ],
        PayLaterRequest::class => [
            SendPayLaterListener::class,
            SendTelegramPayLaterListener::class,
        ],
        UserRegisteredEvent::class => [
            SendCustomerActivationListener::class,
            SendTelegramCustomerActivationListener::class,
            HandleRadiusUserProvisionRequested::class,
            SyncCustomerToMikrotik::class
        ],
        PackageChangesRequest::class => [
            SendPackageChangesListener::class,
            // SendTelegramPackageChangesListener::class
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
        ],
        RadiusProfileUpdateRequested::class => [
            HandleRadiusGroupUpdateRequested::class
        ],
        RadiusUserProvisionRequested::class => [
            HandleRadiusUserProvisionRequested::class
        ],
        UserVoidEvent::class => [
            SyncCustomerExpiredToMikrotik::class,
            HandleRadiusUserProvisionRequested::class
        ],
        BillingPaymentEvent::class => [
            HandleMiikrotikPaymentRequested::class,
            HandleRadiusPaymentUpdateRequested::class,
            SendPaymentSucceededListener::class,
        ],
        UnpaymentBillingEvent::class => [
            HandleMikrotikUnpaymentRequested::class,
            HandleRadiusUnpaymentUpdateRequested::class,
            SendPaymentCancellationListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Mikrotik::observe(MikrotikObserver::class);
        Customer::observe(CustomerObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
