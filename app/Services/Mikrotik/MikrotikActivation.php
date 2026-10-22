<?php

namespace App\Services\Mikrotik;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Models\Mikrotik;
use App\Services\Mikrotik\IpStatic\MikrotikActivateIpStaticSecret;
use App\Services\Mikrotik\Ppp\MikrotikActivatePppSecret;

class MikrotikActivation
{
    public function __construct(
        private MikrotikComment $mikrotikComment,
        private MikrotikHelper $mikrotikHelper,
        private MikrotikActivateIpStaticSecret $mikrotikActivateIpStaticSecret,
        private MikrotikActivatePppSecret $mikrotikActivatePppSecret,
    ) {}

    public function handle(Customer $customer, CustomerSubscription $subs, string $traceId): array
    {
        $timer = new ExecutionTimer();

        try {
            $router = Mikrotik::findOrFail($customer->mikrotik_id);

            if ($router->disabled === 'true') {
                $error = "Server {$router->name} disabled, please enable first.";
                LogHelper::logError(null, ['server' => $router->name], 400, $error, $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($error);
            }

            $profile = $subs->package->profile;
            $commentPayment = $this->mikrotikComment->getCommentForActivation($customer, $traceId);

            $result = match ($customer->internet_service) {
                'ppp'       => $this->mikrotikActivatePppSecret->handle($customer, $router, $profile, $commentPayment, $traceId),
                'ip_static' => $this->mikrotikActivateIpStaticSecret->handle($customer, $router, $profile, $commentPayment, $traceId),
                default     => $this->mikrotikHelper->responseError("Unsupported service type: {$customer->internet_service}"),
            };

            if (isset($result['error'])) {
                LogHelper::logError(null, [
                    'cust_id'   => $customer->id,
                    'cust_name' => $customer->name,
                    'server'    => $router->name,
                    'service'   => $customer->internet_service,
                ], 500, $result['message'], $traceId, $timer->lap());

                return $this->mikrotikHelper->responseError($result['message']);
            }

            LogHelper::logInfo(
                null,
                [
                    'cust_id'   => $customer->id,
                    'cust_name' => $customer->name,
                    'server'    => $router->name,
                    'service'   => $customer->internet_service,
                ],
                200,
                '[MikrotikActivation] Mikrotik activate successfully',
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseSuccess('Mikrotik activated successfully');
        } catch (\Throwable $e) {
            $exceptionCode = $e->getCode();
            $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                ? (int) $exceptionCode
                : 500;

            LogHelper::logError(
                null,
                [
                    'customer_id'     => $customer->id,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ],
                $statusCode,
                '[MikrotikActivation] Failed to activate single customer',
                $traceId,
                $timer->lap()
            );

            return $this->mikrotikHelper->responseError($e->getMessage());
        }
    }
}
