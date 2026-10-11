<?php

namespace App\Services\Notification\WhatsApp\Single;

use App\Helpers\DummyReceiptHelper;
use App\Helpers\FormatReceiver;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Messaging\MessageBuilder;
use App\Services\Messaging\MessageDispatcher;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use App\Services\Notification\WhatsApp\AbstractBaseNotificationService;
use App\Services\WhatsApp\CloudAPI\Messages\TemplateMessageService;
use App\Services\WhatsApp\WhatsAppGatewayResolver;

abstract class AbstractSingleNotificationService extends AbstractBaseNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        WhatsAppGatewayResolver $gatewayResolver,
        protected readonly TemplateMessageService $templateMessageService,
        protected readonly MessageDispatcher $dispatcher,
        protected readonly AdminBankCollector $bankCollector,
        protected readonly FormatReceiver $formatNumber,
        protected readonly MessageBuilder $builder,
        protected readonly DummyReceiptHelper $dummyReceipt
    ) {
        parent::__construct($gatewayResolver);
    }
}
