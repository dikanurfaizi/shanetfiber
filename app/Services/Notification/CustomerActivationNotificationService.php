<?php

namespace App\Services\Notification;

use App\DataTransferObjects\TelegramJobDto;
use App\Enums\TelegramAction;
use App\Enums\WhatsAppAction;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Models\Billings\Billing;
use App\Models\User;
use App\Models\Receipt;
use App\Models\Customers\CustomerSubscription;
use App\Services\Messaging\Collectors\AdminBankCollector;
use App\Services\Notification\Traits\MapsBroadcastBankPayload;
use App\Services\Whatsapp\WhatsappGatewayResolver;
use App\Services\Whatsapp\GowaService;
use App\Services\Telegrams\TelegramMessageService;
use App\Services\Whatsapp\CloudAPI\Messages\TemplateMessageService;
use Illuminate\Support\Carbon;

class CustomerActivationNotificationService
{
    use MapsBroadcastBankPayload;

    public function __construct(
        private TemplateMessageService $templateMessageService,
        private GowaService $gowaService,
        private TelegramMessageService $telegramMessageService,
        private WhatsappGatewayResolver $gatewayResolver,
        private AdminBankCollector $bankCollector
    ) {}

    public function send(
        ?int $customerMembershipId,
        CustomerSubscription $customerSubscription,
        Billing $billing,
        Receipt $receipt,
        ?int $eventId,
        string $traceId
    ): void {
        try {
            // Resolve WA account & gateway
            [$gateway, $account, $number] = $this->gatewayResolver->resolveActiveGatewayAndAccount($traceId, 'aktivasi')
                ?? [null, null, null];

            if (!$gateway || !$account || !$number) {
                return; // resolver sudah log
            }

            if (empty($customerSubscription->customer->contact)) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customerSubscription->customer_id,
                ], 400, 'Customer has no contact for activation notification.', $traceId);
                return;
            }

            // Format nomor customer + admin
            $formatter = new FormatReceiver;
            $recipient = $formatter->formatReceiver($customerSubscription->customer->contact, null, false);

            $user = User::find($customerSubscription->customer->user_id);
            $adminNumber = $formatter->formatReceiver($user->contact, 'display', false);

            // === 💡 AMBIL DATA BANK ADMIN VIA COLLECTOR ===
            $bankAccounts = $this->bankCollector->getBanksByUserId($user->id ?? 0);

            LogHelper::logInfo(null, [
                'customer_id'   => $customerSubscription->customer_id,
                'user_id'       => $user->id,
                'bank_count'    => $bankAccounts->count(),
                'bank_accounts' => $bankAccounts->map(fn($bank) => [
                    'bank'           => $bank->bank,
                    'account_number' => $bank->account_number,
                    'name'           => $bank->name,
                ]),
            ], 200, 'Fetched user bank accounts for activation notification.', $traceId);

            if ($bankAccounts->isEmpty()) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customerSubscription->customer_id,
                    'user_id'     => $user->id,
                ], 200, 'User has no bank accounts, activation WA will show blank bank list.', $traceId);
            }

            /** Get Format Bank String */
            $formattedBanks = $bankAccounts->map(
                fn($bank) => "{$bank->bank} - {$bank->account_number} a.n {$bank->name}"
            )->implode(' | ');

            $customer = $customerSubscription->customer;

            // === 💡 LOGIKA PRORATE BERDASARKAN DATA DATABASE (CARBON) ===
            $createdAt = Carbon::parse($customer->created_at);

            // 1. Cek apakah tanggal pendaftaran dibuat setelah tanggal 15 di bulan tersebut
            $isProrate = $createdAt->day > 15 ? 1 : 0;

            // 2. Hitung total hari dalam bulan pendaftaran tersebut secara dinamis
            $totalDaysInMonth = $createdAt->daysInMonth;

            // 3. Sisa hari aktif pendaftaran: (Total Hari - Tanggal Pendaftaran) + 1
            $activeDays = ($totalDaysInMonth - $createdAt->day) + 1;

            // 4. Ambil total tagihan resmi langsung dari relasi billing yang di-pass ke function
            // Jika ada addon, billing biasanya sudah mengakumulasikan total_amount secara menyeluruh
            $grandTotalBilling = $billing->total_amount ?? 0;

            // Definisikan tipe perhitungan bahasa lokal/statis sesuai template migrasi WA
            $calculationMethod = ($isProrate == 1) ? 'Prorate' : 'Biaya Penuh';
            $prorateNote       = ($isProrate == 1) ? "_(Dihitung proporsional {$activeDays} hari s.d akhir bulan)_" : "";

            // Pasang semua payload yang akan dibaca oleh pemeta pesan gateway
            $data = [
                'recipient'          => $recipient,
                'customer_name'      => $customer->name,
                'customer_id'        => $customer->customer_id,
                'package_name'       => $customerSubscription->package->name ?? '-',
                'package_price'      => $customerSubscription->package->price ?? 0,
                'calculation_method' => $calculationMethod,
                'prorate_note'       => $prorateNote,
                'grand_total'        => 'Rp ' . number_format($grandTotalBilling, 0, ',', '.'),
                'member_type'        => $customer->activeMembership ? $customer->activeMembership->membership->name : 'Non Member',
                'admin_name'         => $user->name,
                'admin_contact'      => $adminNumber,
                'bank'               => $formattedBanks,
                'event_id'           => $eventId,
                'account'            => $account,
            ];

            if ($gateway->type === 'cloud') {
                $this->templateMessageService->sendCustomerActivation($data);
            } else {
                $deviceId = $number->device_id;

                $formattedBanks = $bankAccounts
                    ->map(fn($b) => "- {$b->bank} - {$b->account_number} (a.n {$b->name})")
                    ->implode("\n");

                $this->gowaService->activation(
                    customerMembershipId: $customerMembershipId,
                    customerSubscription: $customerSubscription,
                    action: WhatsAppAction::ACTIVATION->value,
                    eventId: $eventId,
                    deviceId: $deviceId,
                    traceId: $traceId,
                    extraProrateData: [
                        'calculation_method' => $calculationMethod,
                        'prorate_note'       => $prorateNote,
                        'grand_total'        => 'Rp ' . number_format($grandTotalBilling, 0, ',', '.'),
                        'formatted_banks'    => $formattedBanks
                    ]
                );
            }

            $telegramJobData = new TelegramJobDto(
                customerId: $customerSubscription->customer_id,
                action: TelegramAction::ACTIVATION->value,
                traceId: $traceId,
                billingId: $billing->id,
                receiptId: $billing->billingPayment?->receipt?->id ?? $receipt->id,
                chatId: null
            );

            $this->telegramMessageService->process($telegramJobData, $traceId);

            LogHelper::logInfo(null, [
                'customer_id'  => $customerSubscription->customer_id,
                'billing_id'   => $billing->id,
                'receipt_id'   => $receipt->id,
                'gateway_type' => $gateway->type,
            ], 200, 'Customer activation notification dispatched', $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError($e, [
                'customer_id'   => $customerSubscription->customer_id,
                'billing_id'    => $billing->id,
                'receipt_id'    => $receipt->id,
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to send activation notification', $traceId);
        }
    }
}
