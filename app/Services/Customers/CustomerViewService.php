<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Customers\CustomerOnt;
use App\Services\Routerboard\Ppp\SecretService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;

class CustomerViewService
{
    private CustomerDataService $customerDataService;

    public function __construct(
        private SecretService $secretService,
        CustomerDataService $customerDataService
    ) {
        $this->customerDataService = $customerDataService;
    }

    public function getCustomerViewData(Customer $customer, string $traceId): array
    {
        $formatter = new FormatReceiver();

        try {
            $router = Mikrotik::find($customer->mikrotik_id);

            if (!$router) {
                $error = 'Router not found for customer ID: ' . $customer->id;
                LogHelper::logError(null, ['customer_id' => $customer->id], 404, $error, $traceId);
                return $this->getOfflineData(null, $customer, $traceId, 'Router not found');
            }

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'router_id'   => $router->id,
                'router_name' => $router->name
            ], 200, 'Fetching customer view data', $traceId);

            $secretData = $this->getSecretData($router, $customer, $traceId);
            $customer->refresh();

            // 💡 PERBAIKAN 1: Pengambilan IP Address secara aman
            $activeAddress = $secretData['secretActive']['address'] ?? null;
            if (!empty($activeAddress)) {
                $customer->ip_address = $activeAddress;
            }

            // 💡 PERBAIKAN 2: Pengambilan MAC Address (last-caller-id) secara aman
            $lastCallerId = $secretData['pppsecret']['last-caller-id'] ?? null;
            if (!empty($lastCallerId)) {
                $customer->mac_address = $lastCallerId;
            }

            $customer->save();

            $result = array_merge(
                $secretData,
                [
                    'customer'         => $customer,
                    'customer_contact' => $formatter->formatReceiver($customer->contact, 'display', false),
                    'ont'              => CustomerOnt::where('customer_id', $customer->id)->first(),
                    'mikrotik'         => $router
                ],
                $this->customerDataService->getCustomerDetails($customer, $traceId)
            );

            // 💡 PERBAIKAN 3: Safe logging menggunakan variabel aman (tanpa direct array key access)
            LogHelper::logInfo(null, [
                'ip_address'  => $activeAddress,
                'mac_address' => $lastCallerId,
                'customer_id' => $customer->id,
                'data_count'  => count($result)
            ], 200, 'Successfully retrieved customer view data', $traceId);

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_id'   => $customer->mikrotik_id ?? null,
                    'code'        => $e->getCode(),
                    'message'     => $e->getMessage()
                ],
                500,
                'Failed to get customer view data',
                $traceId
            );

            return $this->getOfflineData($router ?? null, $customer, $traceId, $e->getMessage());
        }
    }

    private function getSecretData($router, $customer, $traceId): array
    {
        try {
            $secret = SecretResource::collection(
                $this->secretService->getSecretById($router, $customer->secret_id, $traceId)
            )->first();

            $secretActive = SecretResource::collection(
                $this->secretService->getOnePppSecretActive($router, $customer->username, $traceId)
            )->first();

            $data = [
                'pppsecret' => $secret,
                'secretActive' => $secretActive,
                'serverDisconnect' => 'false',
                'info_msg' => 'Server ' . $router->name . ' online'
            ];

            LogHelper::logInfo(null, [
                'customer_id' => $customer->id,
                'secret_id' => $customer->secret_id,
                'username' => $customer->username,
                'has_secret' => !is_null($secret),
                'has_active_secret' => !is_null($secretActive)
            ], 200, 'Successfully retrieved PPPoE secret data', $traceId);

            return $data;
        } catch (\Exception $e) {
            LogHelper::logError(
                $e,
                [
                    'customer_id' => $customer->id,
                    'router' => $router->name,
                    'secret_id' => $customer->secret_id,
                    'username' => $customer->username
                ],
                500,
                'Failed to get PPPoE secret data',
                $traceId
            );

            return [
                'pppsecret' => null,
                'secretActive' => null,
                'serverDisconnect' => 'true',
                'info_msg' => 'Failed to get PPPoE data from server ' . $router->name
            ];
        }
    }

    private function getOfflineData($router, $customer, $traceId, ?string $errorMessage = null): array
    {
        $formatter = new FormatReceiver();
        $customer->refresh();

        $infoMsg = $router
            ? 'Server ' . $router->name . ' offline, data displayed is local data.'
            : 'Router not available, showing local data only';

        if ($errorMessage) {
            $infoMsg .= ' (' . $errorMessage . ')';
        }

        $data = [
            'pppsecret' => null,
            'secretActive' => null,
            'serverDisconnect' => 'true',
            'info_msg' => $infoMsg,
            'customer' => $customer,
            'customer_contact' => $formatter->formatReceiver($customer->contact, 'display', false),
            'ont' => CustomerOnt::where('customer_id', $customer->id)->first(),
            'mikrotik' => $router,
            ...$this->customerDataService->getCustomerDetails($customer, $traceId)
        ];

        LogHelper::logInfo(null, [
            'customer_id' => $customer->id,
            'router_status' => $router ? 'offline' : 'not_found',
            'data_count' => count($data)
        ], 200, 'Returning offline customer data', $traceId);

        return $data;
    }
}
