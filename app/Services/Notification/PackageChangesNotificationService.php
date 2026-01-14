<?php

namespace App\Services\Notification;

use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Customers\Customer;
use App\Services\Whatsapp\CloudAPIService;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;

class PackageChangesNotificationService
{
    public function __construct(
        private TemplateMessageService $templateMessageService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
    ) {}

    public function send(
        Customer $customer,
        string $oldPackageName,
        int $oldPackagePrice,
        string $traceId
    ): void {
        try {
            // === Resolve WA account & gateway ===
            [$gateway, $account, $number] =
                $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId)
                ?? [null, null, null];

            if (! $gateway || ! $account || ! $number) {
                return; // Resolver sudah melakukan log
            }

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 400, 'Customer has no contact for package-change notification.', $traceId);
                return;
            }

            // === Format nomor customer dan admin ===
            $formatter = new FormatReceiver;

            $to = $formatter->formatReceiver($customer->contact, null, false);
            $adminNumber = $formatter->formatReceiver(
                $customer->user?->contact,
                'display',
                false
            );

            // === Ambil paket baru ===
            $newPackage = $customer->active_package;

            if (! $newPackage) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                ], 404, 'Customer has no new package relation for package-change notification.', $traceId);
                return;
            }

            // === TEMPLATE PARAMS sesuai template WABA "perubahan_paket" ===
            $templateParams = [
                $customer->name,                                       // {{1}}  Yth Bpk/Ibu (HEADER text)
                $customer->customer_id ?? $customer->id,              // {{2}}  Customer ID
                $oldPackageName,                                      // {{3}}  Paket sebelumnya
                'Rp ' . number_format($oldPackagePrice, 0, ',', '.'), // {{4}}  Harga lama
                $newPackage->name,                                    // {{5}}  Paket baru
                'Rp ' . number_format($newPackage->price, 0, ',', '.'), // {{6}} Harga baru
                $customer->user->name,                                // {{7}} Nama admin
                $adminNumber,                                         // {{8}} Nomor admin
            ];

            // // === 1. Kirim via WhatsApp ===
            // if ($gateway->type === 'cloud') {
            //     $this->cloudAPIService->sendTemplateMessage(
            //         account: $account,
            //         templateName: 'perubahan_paket',
            //         phoneNumberId: $number->meta_phone_number_id,
            //         to: $to,
            //         bodyParams: $templateParams,
            //         buttonParams: [],
            //         traceId: $traceId,
            //     );
            // } else {
            // Fallback ke GOWA
            $this->gowaService->changesPackage(
                customerId: $customer->id,
                oldPackageName: $oldPackageName,
                oldPackagePrice: $oldPackagePrice,
                traceId: $traceId
            );
            // }

            // === 2. Kirim Telegram ===
            // $telegramJobData = new TelegramJobDto(
            //     customerId: $customer->id,
            //     action: TelegramAction::CHANGES_PACKAGE->value,
            //     traceId: $traceId,
            //     billingId: null,
            //     receiptId: null,
            //     chatId: null,
            // );

            // $this->telegramMessageService->process($telegramJobData, $traceId);

            // === Log Success ===
            LogHelper::logInfo(
                null,
                [
                    'customer_id'        => $customer->id,
                    'old_package'        => $oldPackageName,
                    'old_price'          => $oldPackagePrice,
                    'new_package'        => $newPackage->name,
                    'new_price'          => $newPackage->price,
                    'gateway_type'       => $gateway->type,
                ],
                200,
                'Package change notification dispatched successfully',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                $e,
                [
                    'customer_id'   => $customer->id,
                    'old_package'   => $oldPackageName,
                    'old_price'     => $oldPackagePrice,
                    'error_message' => $e->getMessage(),
                ],
                500,
                'Failed to send package-change notification',
                $traceId
            );
        }
    }
}
