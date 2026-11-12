<?php

namespace App\Services\Mikrotik\Ppp;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Mikrotik\MikrotikHelper;
use App\Services\Routerboard\Ppp\SecretService;
use Exception;

class MikrotikUnpaymentPppSecret
{
    public function __construct(
        private SecretService $secretService,
        private MikrotikHelper $mikrotikHelper
    ) {}

    public function handle(Mikrotik $router, Customer $customer, string $comment, AutoIsolir $autoIsolir, string $traceId): array
    {
        $timer = new ExecutionTimer();

        LogHelper::logInfo('MikrotikUnpaymentPppSecret', [
            'customer_id' => $customer->id,
            'router_id'   => $router->id,
            'username'    => $customer->username,
            'secret_id'   => $customer->secret_id,
        ], 200, 'Starting PPP unpayment secret process', $traceId, $timer->lap());

        try {
            $day = (int) now()->format('j');
            $dueDate = (int) ($autoIsolir->activation_date === 'false'
                ? $autoIsolir->due_date
                : now()->parse($customer->activation_date)->format('j'));

            LogHelper::logInfo('MikrotikUnpaymentPppSecret', [
                'customer_id' => $customer->id,
                'current_day' => $day,
                'due_date'    => $dueDate,
                'is_overdue'  => $day > $dueDate,
            ], 200, 'Checked due date condition', $traceId, $timer->lap());

            if ($day <= $dueDate) {
                // Sebelum jatuh tempo: Hanya perbarui komentar
                LogHelper::logInfo('MikrotikUnpaymentPppSecret', [
                    'customer_id' => $customer->id,
                    'secret_id'   => $customer->secret_id,
                    'comment'     => $comment,
                ], 200, 'Before due date: Updating PPP secret comment only', $traceId, $timer->lap());

                $this->secretService->addCommentSecret($router, $comment, $customer->secret_id, $traceId);
            } else {
                // Setelah jatuh tempo: Putus koneksi, ubah ke profil Isolir, update komentar
                LogHelper::logInfo('MikrotikUnpaymentPppSecret', [
                    'customer_id' => $customer->id,
                    'username'    => $customer->username,
                    'profile_id'  => $autoIsolir->profile_id,
                    'secret_id'   => $customer->secret_id,
                    'comment'     => $comment,
                ], 200, 'After due date: Isolating PPP secret (disconnect active, set isolir profile, update comment)', $traceId, $timer->lap());

                $this->secretService->deleteActiveSecret($router, $customer->username, $traceId);
                $this->secretService->updateProfileSecret($router, $autoIsolir->profile_id, $customer->secret_id, $traceId);
                $this->secretService->addCommentSecret($router, $comment, $customer->secret_id, $traceId);
            }

            LogHelper::logInfo('MikrotikUnpaymentPppSecret', [
                'customer_id' => $customer->id,
            ], 200, 'Successfully completed PPP unpayment secret process', $traceId, $timer->lap());

            return $this->mikrotikHelper->responseSuccess("PPP unpayment processed directly on Mikrotik");
        } catch (Exception $e) {
            LogHelper::logError('MikrotikUnpaymentPppSecret', [
                'customer_id' => $customer->id,
                'router_id'   => $router->id,
                'username'    => $customer->username,
            ], 'Error processing PPP unpayment secret: ' . $e->getMessage(), $traceId, $timer->lap());

            return $this->mikrotikHelper->responseError($e->getMessage());
        }
    }
}
