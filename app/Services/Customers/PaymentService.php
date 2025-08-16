<?php

namespace App\Services\Customers;

use App\Models\Billing\Billing;
use App\Models\Websystem;
use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Carbon;
use App\Http\Controllers\PaymentGateway\TripayController;
use App\Helpers\LogHelper;

class PaymentService
{
    public function getBillings(int $customerId, ?string $state, int $perPage = 10, string $traceId)
    {
        LogHelper::logInfo(null, [
            'customer_id' => $customerId,
            'status' => $state,
            'per_page' => $perPage,
        ], 200, 'Mulai mengambil data billing', $traceId);

        $billings = Billing::with('billingSubscription')
            ->where('customer_id', $customerId)
            ->when($state, function ($query) use ($state) {
                $query->where('status', $state);
            })
            ->orderBy('created_at', 'DESC')
            ->orderBy('customer_name', 'ASC')
            ->paginate($perPage);

        $billings->getCollection()->transform(function ($billing) use ($traceId) {
            $periode = $billing->billingSubscription->billing_periode ?? null;

            if ($periode) {
                $billing->periode_text = Carbon::parse($periode)
                    ->locale('id')
                    ->translatedFormat('F Y');

                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                    'periode' => $billing->periode_text,
                ], 200, 'Periode billing berhasil diproses', $traceId);
            } else {
                $billing->periode_text = '-';
                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                ], 404, 'Periode billing tidak ditemukan', $traceId);
            }

            return $billing;
        });

        LogHelper::logInfo(null, [
            'count' => $billings->count(),
        ], 200, 'Data billing berhasil diambil', $traceId);

        return $billings;
    }

    public function getWebsystem(string $traceId)
    {
        LogHelper::logInfo(null, [], 200, 'Mengambil konfigurasi websystem', $traceId);

        $websystem = Websystem::where('slug', 'websystem')->first();

        if ($websystem) {
            LogHelper::logInfo(null, [
                'payment_gateway' => $websystem->payment_gateway,
            ], 200, 'Konfigurasi websystem ditemukan', $traceId);
        } else {
            LogHelper::logInfo(null, [], 404, 'Konfigurasi websystem tidak ditemukan', $traceId);
        }

        return $websystem;
    }

    public function getPaymentDetail(Billing $billing, string $traceId)
    {
        LogHelper::logInfo(null, [
            'billing_id' => $billing->id,
            'customer_id' => $billing->customer_id,
        ], 200, 'Mengambil detail pembayaran', $traceId);

        $websystem = Websystem::where('slug', 'websystem')->first();

        if ($websystem->payment_gateway !== 'enable') {
            LogHelper::logInfo(null, [], 503, 'Gateway pembayaran dinonaktifkan', $traceId);
            return [
                'view' => 'errors.notfound',
                'data' => [],
            ];
        }

        $payment_gateways = PaymentGateway::where('disabled', 'false')->get();

        foreach ($payment_gateways as $gateway) {
            LogHelper::logInfo(null, [
                'gateway' => $gateway->slug,
            ], 200, 'Memeriksa gateway pembayaran', $traceId);

            if ($gateway->slug === 'midtrans') {
                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                ], 200, 'Menggunakan Midtrans gateway', $traceId);

                return [
                    'view' => 'customers.pages.payments.show',
                    'data' => compact('billing', 'websystem'),
                ];
            }

            if ($gateway->slug === 'tripays') {
                LogHelper::logInfo(null, [
                    'billing_id' => $billing->id,
                ], 200, 'Menggunakan Tripay gateway', $traceId);

                $tripay = new TripayController();
                $channels = $tripay->getPaymentChannels();

                $order = Order::where('orders.customer_id', $billing->customer_id)
                    ->where('orders.billing_id', $billing->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($order) {
                    LogHelper::logInfo(null, [
                        'order_id' => $order->id,
                    ], 200, 'Order ditemukan untuk billing', $traceId);
                } else {
                    LogHelper::logInfo(null, [], 404, 'Order tidak ditemukan untuk billing', $traceId);
                }

                return [
                    'view' => 'customers.pages.payments.tripays.show',
                    'data' => compact('billing', 'websystem', 'gateway', 'channels', 'order'),
                ];
            }
        }

        LogHelper::logInfo(null, [], 404, 'Tidak ada gateway pembayaran yang cocok', $traceId);

        return [
            'view' => 'errors.notfound',
            'data' => [],
        ];
    }
}
