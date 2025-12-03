<?php

namespace App\Services\Messaging\Handlers;

use App\Helpers\LogHelper;
use App\Services\Messaging\Contracts\MessageActionHandler;
use App\Services\Messaging\MessageBuilder;

class MenuHandler extends BaseBroadcastAwareHandler implements MessageActionHandler
{
    public function __construct(
        private string $type,
        private MessageBuilder $builder,
        private string $custContact,
        private string $custName,
        string $category
    ) {
        $this->setCategory($category);
        $this->setRawContact($custContact);
    }

    public function action(): string
    {
        return $this->type;
    }

    public function validate(string $traceId): void
    {
        $customer = $this->validateBase($traceId);
    }

    public function build(string $traceId): array
    {
        return [
            'template_slug' => $this->type,
            'to'            => $this->custContact,
            'customer_id'   => null, // menu bisa tanpa customer
            'replacements'  => [
                '%name%' => $this->custName,
            ],
        ];
    }
}
