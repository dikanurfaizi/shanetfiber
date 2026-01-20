<?php

namespace App\Providers;

use App\Events\PayLaterApplied;
use App\Events\PaymentCancelled;
use App\Events\PaymentSucceeded;
use App\Events\CustomerActivated;
use App\Events\OrderCreated;
use App\Events\PackageChangesApplied;
use App\Events\BroadcastMidMonthTriggered;
use App\Events\MonthlyKickoffBroadcastTriggered;
use App\Events\OverdueBroadcastTriggered;
use App\Listeners\QueueCustomerActivationNotification;
use App\Listeners\QueueOrderCreatedNotification;
use App\Listeners\QueuePayLaterNotification;
use App\Listeners\QueuePaymentCancellationNotification;
use App\Listeners\QueuePaymentNotification;
use App\Listeners\QueuePackageChangesNotification;
use App\Listeners\QueueBroadcastMidMonthProcess;
use App\Listeners\QueueMonthlyKickoffBroadcast;
use App\Listeners\QueueOverdueBroadcastNotification;
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
            QueuePaymentNotification::class,
        ],
        PaymentCancelled::class => [
            QueuePaymentCancellationNotification::class,
        ],
        PayLaterApplied::class => [
            QueuePayLaterNotification::class,
        ],
        CustomerActivated::class => [
            QueueCustomerActivationNotification::class,
        ],
        PackageChangesApplied::class => [
            QueuePackageChangesNotification::class,
        ],
        OrderCreated::class => [
            QueueOrderCreatedNotification::class,
        ],
        BroadcastMidMonthTriggered::class => [
            QueueBroadcastMidMonthProcess::class,
        ],
        MonthlyKickoffBroadcastTriggered::class => [
            QueueMonthlyKickoffBroadcast::class,
        ],
        OverdueBroadcastTriggered::class => [
            QueueOverdueBroadcastNotification::class,
        ],
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
