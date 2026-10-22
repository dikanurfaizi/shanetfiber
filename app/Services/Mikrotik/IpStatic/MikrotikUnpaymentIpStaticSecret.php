<?php

namespace App\Services\Mikrotik\IpStatic;

use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\IpStaticService;

class MikrotikUnpaymentIpStaticSecret
{
    public function __construct(
        private IpStaticService $ipStaticService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Mikrotik $router, Customer $customer, string $commentUnpayment, AutoIsolir $autoIsolir, string $traceId): array
    {
        $day = (int) now()->format('j');
        $dueDate = (int) ($autoIsolir->activation_date === 'false'
            ? $autoIsolir->due_date
            : now()->parse($customer->activation_date)->format('j'));

        $fullComment = $customer->username . '_' . $commentUnpayment;

        if ($day <= $dueDate) {
            // Sebelum jatuh tempo: Update komentar simple queue
            $this->ipStaticService->setCommentSimpleque($router, $customer->simpleque_id, $commentUnpayment, $traceId);
        } else {
            // Setelah jatuh tempo: Masukkan IP ke Address List Isolir & set komentar
            $this->ipStaticService->addIpToAddressList($router, $customer->ip_static, $autoIsolir->address_list_isolir, $fullComment, $traceId);
            $this->ipStaticService->setCommentSimpleque($router, $customer->simpleque_id, $fullComment, $traceId);
        }

        return $this->mikrotikHelper->responseSuccess("IP Static unpayment processed directly on Mikrotik");
    }
}
