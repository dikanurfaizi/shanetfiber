<?php

namespace App\Services\Mikrotik\Ppp;

use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\Ppp\SecretService;

class MikrotikUnpaymentPppSecret
{
    public function __construct(
        private SecretService $secretService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Mikrotik $router, Customer $customer, string $comment, AutoIsolir $autoIsolir, string $traceId): array
    {
        $day = (int) now()->format('j');
        $dueDate = (int) ($autoIsolir->activation_date === 'false'
            ? $autoIsolir->due_date
            : now()->parse($customer->activation_date)->format('j'));

        if ($day <= $dueDate) {
            // Sebelum jatuh tempo: Hanya perbarui komentar
            $this->secretService->addCommentSecret($router, $comment, $customer->secret_id, $traceId);
        } else {
            // Setelah jatuh tempo: Putus koneksi, kembalikan ke profil Isolir, update komentar
            $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
            $this->secretService->updateProfileSecret($router, $autoIsolir->profile_id, $customer->secret_id, $traceId);
            $this->secretService->addCommentSecret($router, $comment, $customer->secret_id, $traceId);
        }

        return $this->mikrotikHelper->responseSuccess("PPP unpayment processed directly on Mikrotik");
    }
}
