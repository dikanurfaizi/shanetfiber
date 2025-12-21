<?php

namespace App\Services\Customers;

use App\Models\Billings\Billing;
use App\Models\Websystem;
use App\Models\Billings\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Helpers\LogHelper;

class PaymentService
{
    public function getBillings(int $customerId, ?string $state, int $perPage = 10, string $traceId)
    {
        $billings = Billing::query()
            ->with(['billingSubscription.billingPeriode', 'customer'])
            ->join('customers', 'customers.id', '=', 'billings.customer_id')
            ->where('billings.customer_id', $customerId)
            ->when($state, fn($q) => $q->where('billings.status', $state))
            ->orderBy('billings.created_at', 'DESC')
            ->orderBy('customers.name', 'ASC')
            ->select('billings.*')
            ->paginate($perPage);

        $billings->getCollection()->transform(function ($billing) {
            $periode = $billing->billingSubscription->billingPeriode->name ?? null;

            $billing->periode_text = $periode
                ? Carbon::parse($periode)->locale('id')->translatedFormat('F Y')
                : '-';

            return $billing;
        });

        LogHelper::logInfo(
            null,
            [
                'customer_id' => $customerId,
                'status' => $state,
                'per_page' => $perPage,
                'billing_count' => $billings->count(),
                'billing_ids' => $billings->pluck('id')->values()
            ],
            200,
            'Billing list compiled',
            $traceId
        );

        return $billings;
    }

    public function getWebsystem(string $traceId)
    {
        $websystem = Websystem::where('slug', 'websystem')->first();

        LogHelper::logInfo(
            null,
            [
                'found' => (bool) $websystem,
                'payment_gateway' => $websystem?->payment_gateway,
                'tax_enabled' => $websystem?->enable_tax,
                'tax_rate' => $websystem?->tax_rate,
            ],
            $websystem ? 200 : 404,
            'Websystem config resolved',
            $traceId
        );

        return $websystem;
    }

    public function getPaymentDetail(Billing $billing, string $traceId)
    {
        $log = [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
        ];

        $websystem = Websystem::where('slug', 'websystem')->first();

        if ($websystem?->payment_gateway !== 'enable') {
            $log['result'] = 'payment_gateway_disabled';

            LogHelper::logInfo(null, $log, 503, 'Payment detail resolved', $traceId);

            return [
                'view' => 'errors.notfound',
                'data' => [],
            ];
        }

        $payment_gateways = PaymentGateway::where('disabled', 'false')->get();
        $log['available_gateways'] = $payment_gateways->pluck('slug');

        foreach ($payment_gateways as $gateway) {

            if ($gateway->slug === 'midtrans') {
                $log['selected_gateway'] = 'midtrans';

                LogHelper::logInfo(null, $log, 200, 'Payment detail resolved', $traceId);

                return [
                    'view' => 'customers.pages.payments.show',
                    'data' => compact('billing', 'websystem'),
                ];
            }

            if ($gateway->slug === 'tripays') {
                $tripay = new TripayController();
                $channels = $tripay->getPaymentChannels();

                $order = Order::where('customer_id', $billing->customer_id)
                    ->where('billing_id', $billing->id)
                    ->latest()
                    ->first();

                $log['selected_gateway'] = 'tripays';
                $log['order_id'] = $order?->id;
                $log['channel_count'] = count($channels ?? []);

                LogHelper::logInfo(null, $log, 200, 'Payment detail resolved', $traceId);

                return [
                    'view' => 'customers.pages.payments.tripays.show',
                    'data' => compact('billing', 'websystem', 'gateway', 'channels', 'order'),
                ];
            }
        }

        $log['result'] = 'no_gateway_matched';

        LogHelper::logInfo(null, $log, 404, 'Payment detail resolved', $traceId);

        return [
            'view' => 'errors.notfound',
            'data' => [],
        ];
    }
}
