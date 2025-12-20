<?php
// app/Services/Messaging/Handlers/BillingStatusHandler.php
namespace App\Services\Messaging\Handlers;

use App\Models\Customers\Customer;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;
use App\Helpers\LogHelper;

class CallAdminHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private Customer $customer,
        private MessageBuilder $builder,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($customer?->contact ?? null);
    }

    public function action(): string
    {
        return 'hubungi_admin';
    }

    public function validate(string $traceId): void
    {
        $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        $cust    = $this->customer;
        $cs      = $cust?->user?->contact ?? '-';

        return [
            'template_slug' => $this->action(),
            'to'            => $cust->contact,
            'customer_id'   => $cust->id,
            'replacements'  => [],
        ];
    }
}
