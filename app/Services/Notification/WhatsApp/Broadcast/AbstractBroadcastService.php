<?php

namespace App\Services\Notification\WhatsApp\Broadcast;

use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Notification\WhatsApp\AbstractBaseNotificationService;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use App\Services\WhatsApp\WhatsAppGatewayResolver;

abstract class AbstractBroadcastService extends AbstractBaseNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        protected MessageDispatcher $dispatcher,
        protected AdminBankCollector $bankCollector
    ) {
        parent::__construct($gatewayResolver);
    }
}
