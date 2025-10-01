<?php

namespace App\Services;

use App\Models\WhatsappGateway\WhatsappNumber;
use App\Models\WhatsappGateway\WhatsappMessage;
use App\Models\User;
use App\Models\Bank;
use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Websystem;
use App\Models\MessageLog;
use App\Models\MessageLogError;
use App\Models\Order;
use App\Helpers\LogHelper;
use App\Helpers\FormatReceiver;
use App\Jobs\WhatsApp\SendWhatsappMessageJob;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class WhatsappMessageService
{
    /**
     * Chesk status wa gateway
     * return false = enable, return true = disable
     */

    private FormatReceiver $formatter;

    public function __construct()
    {
        $this->formatter = new FormatReceiver();
    }

    public function getDefaultMessage($slug)
    {
        try {
            $message = WhatsappMessage::where('slug', $slug)
                ->where('disabled', false)
                ->where('default', true)
                ->value('reply_message');

            // Log jika pesan ditemukan
            if ($message) {
                LogHelper::logInfo(
                    null,
                    [
                        'slug' => $slug,
                        // 'message' => $message,
                    ],
                    200,
                    'Default WhatsApp message retrieved successfully from database.'
                );
            } else {
                // Log jika tidak ada data ditemukan
                LogHelper::logWarning(
                    null,
                    ['slug' => $slug],
                    404,
                    'No default WhatsApp message found for the given slug.'
                );
            }

            return $message ?? "Pesan default tidak tersedia.";
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500,
                "Error retrieving default message for slug: $slug | " . $e->getMessage()
            );
            return "Pesan default tidak tersedia.";
        }
    }

    /**
     * Ambil informasi bank dalam format string
     */
    public function getBankInfo($id)
    {
        try {
            // Ambil bank info berdasarkan user_id
            $banks = Bank::where('user_id', $id)
                ->where('disabled', 'false')
                ->select('name', 'bank', 'account_number')
                ->get();

            if ($banks->isEmpty()) {
                LogHelper::logWarning(
                    null,
                    null,
                    404,
                    "No bank information available for user_id: {$id}"
                );
                return 'Tidak ada informasi bank';
            }

            $bankInfo = $banks->map(fn($bank) => "- {$bank->bank} - {$bank->account_number} (a.n {$bank->name})")
                ->implode("\n");

            return $bankInfo;
        } catch (\Exception $e) {
            // Jika terjadi error
            LogHelper::logError(
                null,
                null,
                500, // Internal Server Error
                "Failed to retrieve bank information. Error: {$e->getMessage()}"
            );
            return 'Terjadi kesalahan saat mengambil informasi bank';
        }
    }

    /**
     * Ambil rincian paket pelanggan
     */
    public function getBillingPackages($cust_id)
    {
        try {
            $packages = Billing::query()
                ->leftJoin('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('pakets', 'customers.paket_id', '=', 'pakets.id')
                ->leftJoin('members', 'customers.member_id', '=', 'members.id')
                ->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')
                ->where('billings.customer_id', $cust_id)
                ->whereIn('billings.status', ['BL', 'PL'])
                ->select([
                    'pakets.name as paket_name',
                    'bsp.billing_periode_id',
                    'pakets.price',
                    'billings.base_price as paket_price',
                    'billings.discount_amount as discount',
                    'members.type',
                    'billings.status',
                    'billings.due_date',
                    'billings.pay_later',
                    'bsp.billing_periode as periode_name',
                    'billings.total_amount',
                ])
                ->orderByRaw("
                CASE
                    WHEN billings.status = 'BL' THEN billings.due_date
                    ELSE billings.pay_later
                END DESC
            ")
                ->get();

            if ($packages->isEmpty()) {
                LogHelper::logError(
                    null,
                    ['customer_id' => $cust_id],
                    404,
                    "No billing packages found for customer"
                );
            }

            return $packages;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $cust_id],
                500,
                "Error retrieving billing packages: {$e->getMessage()}"
            );
            return collect(); // return collection kosong biar aman
        }
    }

    /**
     * Format informasi paket menjadi string
     */
    public function formatPaketInfo($paket)
    {
        try {
            $periode = $paket->periode_name ?? 'Tidak ada periode';
            $formattedPeriode = $periode !== 'Tidak ada periode'
                ? Carbon::parse("1 " . $periode)->locale('id')->translatedFormat('F Y')
                : $periode;

            $member = $paket->type ?? 'Non Member';
            $discount = isset($paket->discount) ? number_format($paket->discount, 2) : number_format(0, 2);

            return sprintf(
                "📦 *Paket:* %s\n📆 *Periode:* %s\n💶 *Harga Paket:* Rp. %s\n\n🔹 *Rincian Tagihan:*\n🌟 *Kategori:* %s\n💸 *Potongan Harga:* Rp. %s",
                $paket->paket_name ?? 'Tidak ada nama paket',
                $formattedPeriode,
                number_format($paket->price ?? 0, 2),
                $member,
                $discount
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['paket' => $paket->paket_name ?? null],
                500,
                "Error formatting package info: {$e->getMessage()}"
            );
            return "Informasi paket tidak tersedia";
        }
    }

    /**
     * Ambil template pesan berdasarkan aksi
     */
    public function getMessageTemplate($action, $replace)
    {
        try {

            switch ($action) {
                case 'warning_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('past_due_warning'));

                case 'deadline_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('deadline_bill'));

                case 'reminder_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('reminder_bill'));

                case 'create_new_bill':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('create_new_bill'));

                case 'activation_customer':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('activation_customer'));

                case 'create_order':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('create_order'));

                case 'payment':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('payment'));

                case 'paylater':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('paylater'));

                case 'unpayment':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('unpayment'));

                case 'changes_paket':
                    return str_replace(array_keys($replace), array_values($replace), $this->getDefaultMessage('changes_paket'));

                default:
                    LogHelper::logWarning(
                        null,
                        ['action' => $action],
                        400,
                        "Invalid action provided"
                    );
                    return null;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['action' => $action, 'error' => $e->getMessage()],
                500,
                "Error processing message template"
            );
            return null;
        }
    }

    /**
     * Ambil nomor WhatsApp aktif
     */
    private function getWhatsAppNumber($id)
    {
        try {
            $user = User::where('id', $id)->first();
            $number = $user ? $user->contact : null;

            if (!$number) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Bad Request
                    "No active WhatsApp number found"
                );
            }

            return $number;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                null,
                500, // Bad Request
                "Error retrieving WhatsApp number: {$e->getMessage()}"
            );

            return null;
        }
    }

    // ============================================
    // =====             Activation           =====

    public function activation($customerId, $action, $traceId)
    {
        $admin_number = null;

        try {
            LogHelper::logInfo(null, compact('customerId', 'action', 'traceId'), 200, '⏳ Mulai proses aktivasi', $traceId);

            $customer = Customer::find($customerId);
            if (!$customer) {
                LogHelper::logWarning(null, ['customerId' => $customerId], 404, "❌ Customer ID tidak ditemukan", $traceId);
                return;
            }

            $customer_contact = $customer->contact;
            if (!$customer_contact) {
                LogHelper::logWarning(null, ['customer_id' => $customer->id], 400, "❌ Kontak customer tidak tersedia", $traceId);
                return;
            }

            $admin = User::find($customer->user_id);
            $admin_number = '-';

            if (!empty($admin) && !empty($admin->contact)) {
                $admin_number = $this->formatter->formatReceiver($admin->contact, null);
            }

            LogHelper::logInfo(null, [
                'admin_id' => $admin->id ?? null,
                'admin_contact' => $admin->contact ?? null,
                'admin_number' => $admin_number
            ], 200, '✅ Admin info berhasil diambil dan diformat', $traceId);

            $accountBank = $this->getBankInfo($admin->id ?? 1);
            LogHelper::logInfo(null, ['account_bank' => $accountBank], 200, '✅ Info rekening admin berhasil diambil', $traceId);

            $replace = [
                '%name%' => $customer->name,
                '%address%' => $customer->address,
                '%email%' => $customer->email ?? '-',
                '%customer_service%' => $admin_number,
                '%customer_id%' => $customer->customer_id,
                '%admin%' => $admin->name ?? '-',
                '%paket%' => $customer->paket->name ?? '-',
                '%bill%' => 'Rp. ' . number_format($customer->paket->price ?? 0, 2, ',', '.'),
                '%account_bank%' => $accountBank,
            ];

            LogHelper::logInfo(null, ['replace_keys' => array_keys($replace)], 200, '🧩 Placeholder pengganti disiapkan', $traceId);

            $message = $this->getMessageTemplate($action, $replace);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'action' => $action
                    ],
                    404,
                    "❌ Template pesan untuk action `{$action}` tidak ditemukan",
                    $traceId
                );
                return;
            }

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'customer_contact' => $customer_contact,
                    'final_message' => $message,
                    'action' => $action,
                ],
                200,
                "✅ Pesan WhatsApp siap dikirim ke {$customer_contact}",
                $traceId
            );

            SendWhatsappMessageJob::dispatch($action, $customer_contact, $message, $customer->id, $traceId)->onQueue('default');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'customer_id' => $customerId,
                    'admin_number' => $admin_number,
                    'action' => $action,
                    'trace_id' => $traceId,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ],
                500,
                "❌ Exception terjadi saat proses {$action}: {$e->getMessage()}",
                $traceId
            );
        }
    }

    // =====             Activation           =====
    // ============================================
    // =====            Create Order          =====

    public function createOrder($billingId, $orderId, $action)
    {
        $traceId = (string) Str::uuid();
        try {
            $billing = Billing::find($billingId);
            $order = Order::find($orderId);
            $customer_contact = $billing->customer->contact;

            if (!$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$billing->id}."
                );
                return;
            }

            $userId = $billing->customer->user_id ?? 1;
            $whatsapp_number = $this->formatter->formatReceiver($this->getWhatsAppNumber($userId), 'broadcast');

            $paymentMethod = strtolower(str_replace(['-', ' '], '', $order->payment_type));

            $expiredTime = Carbon::createFromTimestamp($order->expired_time)
                ->locale('id')
                ->translatedFormat('d F Y H:i') . ' WIB';

            $formattedAmount = 'Rp ' . number_format($order->total_amount, 0, ',', '.');

            $paymentDetails = match ($paymentMethod) {
                'virtualaccount' => <<<TXT
            *🧾 Metode Pembayaran:* _Virtual Account_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *🏦 Nomor VA :* _{$order->va_number}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                'ewallet' => <<<TXT
            *🧾 Metode Pembayaran:* _E-Wallet_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *👛 E-Wallet :* _{$order->payment_name}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                'conveniencestore' => <<<TXT
            *🧾 Metode Pembayaran:* _Gerai (Alfamart/Alfamidi/Indomaret)_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *🏪 Gerai :* _{$order->payment_name}_
            *🔢 Kode Bayar :* _{$order->va_number}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,

                default => <<<TXT
            *Metode Pembayaran:* _Tunggu konfirmasi_
            *🔖 Nomor Referensi:* _{$order->reference}_
            *💰 Nominal :* _{$formattedAmount}_
            *⏰ Expired :* _{$expiredTime}_
            TXT,
            };

            $replace = [
                '%name%' => $billing->customer->name,
                '%customer_id%' => $billing->customer->customer_id,
                '%billing_number%' => $billing->billing_number,
                '%paket%' => $billing->billingSubscription->customerSubscription->paket->name ?? 'Tidak tersedia informasi paket',
                '%admin%' => $billing->customer->user->name,
                '%customer_service%' => $whatsapp_number,
                '%payment_details%' => $paymentDetails,
            ];

            $messages = [
                'create_order' => 'create_order',
            ];

            if (!isset($messages[$action])) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'action' => $action
                    ],
                    400, // Bad Request
                    "Aksi tidak valid: {$action} untuk billing ID: {$billing->id}"
                );
                return;
            }

            $message = $this->getMessageTemplate($messages[$action], $replace);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    ['billing_id' => $billing->id, 'action' => $action],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk billing ID: {$billing->id}"
                );
                return;
            }
            SendWhatsappMessageJob::dispatch($action, $customer_contact, $message, $billing->customer_id, $traceId)->onQueue('default');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'billing_id' => $billingId ?? null,
                    'action' => $action,
                ],
                500, // Internal Error
                "Terjadi kesalahan dalam order_create: " . $e->getMessage()
            );
        }
    }

    // =====            Create Order          =====
    // ============================================
    // =====          Payment Package         =====

    /**Payment Paket
     * Unpayment Paket
     *
     */
    public function payment_paket($billing, $receipt, $action, $traceId)
    {
        try {
            $customer_contact = $billing->customer->contact;

            if (!$customer_contact) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'customer_contact' => $customer_contact ? 'Available' : 'Not Available',
                    ],
                    400, // Bad Request
                    "Gateway atau kontak pelanggan tidak tersedia untuk billing ID: {$billing->id}.",
                    $traceId
                );
                return;
            }

            $userId = $billing->customer->user_id ?? 1;
            $whatsapp_number = $this->formatter->formatReceiver($this->getWhatsAppNumber($userId), null);

            $discount = 0;
            $price_after_discount = $billing->total_amount;
            $member = 'Non Member / Tidak Ada';

            if ($action === 'paylater') {
                $billingOption = $billing->customer->billing_option;
                $billingPeriodMatch = $billing->customer->billing_period === $billing->billingSubscription->billing_periode;

                if ($billingOption == 1 || ($billingOption == 2 && $billingPeriodMatch)) {
                    $discount = $billing->discount_amount ?? 0;
                    $member = optional($billing->customer->member)->type ?? 'Non Member / Tidak Ada';
                }

                $price_after_discount = $billing->total_amount;
                $billing_number = $billing->billing_number;
                $paket_name = $billing->customer->paket->name;
            } else {
                $discount = $receipt?->discount ?? 0;
                $price_after_discount = $receipt?->price_after_discount ?? $billing->total_amount;
                $member = $receipt?->member_type ?? 'Non Member / Tidak Ada';
                $billing_number = $receipt->number;
                $paket_name = $receipt->paket_name;
            }

            $replace = [
                '%name%' => $billing->customer_name,
                '%address%' => $billing->customer_address,
                '%customer_id%' => $billing->customer->customer_id,
                '%billing_number%' => $billing_number,
                '%paket%' => $paket_name,
                '%periode%' => $billing->billingSubscription->billing_periode,
                '%bill%' => 'Rp. ' . number_format($receipt->paket_price ?? $billing->base_price, 2),
                '%discount%' => 'Rp. ' . number_format($discount, 2),
                '%total%' => 'Rp. ' . number_format($price_after_discount, 2),
                '%member%' => $member,
                '%payment_time%' => $receipt ? Carbon::parse($receipt->payment_time)
                    ->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y, H:i') . ' WIB'
                    : null,
                '%teller%' => $receipt->teller_name ?? $billing->teller_name,
                '%payment_methode%' => $receipt ? ($action == 'transfer'
                    ? "{$receipt->payment_methode} {$receipt->bank_name} - {$receipt->account_name} - {$receipt->account_number}"
                    : $receipt->payment_methode)
                    : 'Pay Later',
                '%paylater%' => $billing->pay_later ? Carbon::parse($billing->pay_later)->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d F Y') : null,
                '%whatsapp_number%' => $whatsapp_number
            ];

            $messages = [
                'transfer'          => 'payment',
                'cash'              => 'payment',
                'paylater'          => 'paylater',
                'unpayment'         => 'unpayment',
                'payment_gateway'   => 'payment'
            ];

            if (!isset($messages[$action])) {
                LogHelper::logWarning(
                    null,
                    [
                        'billing_id' => $billing->id,
                        'action' => $action
                    ],
                    400, // Bad Request
                    "Aksi tidak valid: {$action} untuk billing ID: {$billing->id}",
                    $traceId
                );
                return;
            }

            $message = $this->getMessageTemplate($messages[$action], $replace);
            if (!$message) {
                LogHelper::logWarning(
                    null,
                    ['billing_id' => $billing->id, 'action' => $action],
                    404, // Not Found
                    "Template pesan untuk aksi '{$action}' tidak ditemukan untuk billing ID: {$billing->id}",
                    $traceId
                );
                return;
            }

            SendWhatsappMessageJob::dispatch($action . '_payment', $customer_contact, $message, $billing->customer_id, $traceId)->onQueue('default');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage(),
                    'billing_id' => $billing->id ?? null,
                    'action' => $action,
                ],
                500,
                "Terjadi kesalahan dalam payment_paket",
                $traceId
            );
        }
    }

    // =====          Payment Package         =====
    // ============================================
    // ===== send Billing Open Period Message =====

    public function isBillingOpenPeriod(Carbon $startDate, Carbon $endDate, $action): void
    {
        $traceId = (string) Str::uuid();

        try {
            $periode = $this->getLastBillingPeriod($traceId);
            if (!$periode) return;

            $billings = $this->getEligibleBillings($startDate, $endDate, $periode->id);
            if ($billings->isEmpty()) {
                LogHelper::logWarning(null, [
                    'periode_id' => $periode->id,
                    'periode_name' => $periode->name,
                ], 404, "No billing data found.", $traceId);
                return;
            }
            $count = 0;
            foreach ($billings as $billing) {

                $success = $this->createNewBill($action, $billing, $traceId);

                // Log detail per billing
                LogHelper::logInfo(null, [
                    'billing_id'    => $billing->billing_id,
                    'customer_id'   => $billing->customer_id,
                    'customer_name' => $billing->customer_name,
                    'trace_id'      => $traceId,
                    'periode_id'    => $periode->id,
                    'status'        => $success ? 'success' : 'failed',
                ], $success ? 200 : 500, "Send billing reminder WhatsApp message.");

                $count++;
            }

            if ($count > 0) {
                LogHelper::logInfo(null, [
                    'periode_id'   => $periode->id,
                    'periode_name' => $periode->name,
                    'count'        => $count,
                ], 200, "Successfully dispatched {$count} WhatsApp messages.", $traceId);
            } else {
                LogHelper::logWarning(null, [
                    'periode_id'   => $periode->id,
                    'periode_name' => $periode->name,
                ], 404, "No WhatsApp messages dispatched.", $traceId);
            }
        } catch (RequestException $e) {
            LogHelper::logError(null, [], 500, "HTTP Request Exception: " . $e->getMessage(), $traceId);
        } catch (\Exception $e) {
            LogHelper::logError(null, [], 500, "Exception occurred while creating billing: " . $e->getMessage(), $traceId);
        }
    }

    private function getLastBillingPeriod(string $traceId): ?BillingPeriode
    {
        $periode = BillingPeriode::latest('created_at')->first();
        if (!$periode) {
            LogHelper::logWarning(null, null, 404, "No billing period found.", $traceId);
        }
        return $periode;
    }

    private function getEligibleBillings(Carbon $startDate, Carbon $endDate, int $billingPeriodeId): Collection
    {
        return Billing::query()
            ->select([
                'billings.id   as billing_id',
                'billings.billing_number',
                'billings.customer_id',
                'customers.customer_id      as customer_unique_id',
                'customers.name             as customer_name',
                'customers.contact          as customer_contact',
                'customers.address          as customer_address',
                'billings.due_date',
                'users.name    as admin_name',
                'users.id      as admin_id',
                'users.contact as admin_contact',
                DB::raw('COUNT(*)  as total_bills'),
                DB::raw('SUM(billings.total_amount) as total_amount'),
            ])
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')

            // join ke billing_subscription_periodes
            ->leftJoin('billing_subscription_periodes as bsp', 'billings.billing_subscription_id', '=', 'bsp.id')

            // filter message_logs
            ->leftJoin('message_logs as ml', function ($join) use ($startDate, $endDate) {
                $join->on('billings.customer_id', '=', 'ml.customer_id')
                    ->where('ml.message_type', '=', 'create_new_bill')
                    ->whereBetween('ml.created_at', [$startDate, $endDate]);
            })
            // filter message_log_errors
            ->leftJoin('message_log_errors as me', function ($join) use ($startDate, $endDate) {
                $join->on('billings.customer_id', '=', 'me.customer_id')
                    ->where('me.message_type', '=', 'create_new_bill')
                    ->whereBetween('me.created_at', [$startDate, $endDate]);
            })

            ->whereNull('ml.id')
            ->whereNull('me.id')
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '')
            ->where('bsp.billing_periode_id', $billingPeriodeId) // ✅ pakai bsp
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->where('billings.status', 'BL')
                        ->whereNotNull('billings.due_date');
                })->orWhere(function ($q2) {
                    $q2->where('billings.status', 'PL')
                        ->whereNotNull('billings.pay_later');
                });
            })

            ->groupBy([
                'billings.id',
                'billings.billing_number',
                'billings.customer_id',
                'customers.customer_id',
                'customers.name',
                'customers.contact',
                'customers.address',
                'billings.due_date',
                'users.name',
                'users.id',
                'users.contact',
            ])
            ->orderBy('customers.name')
            ->limit(20)
            ->get();
    }

    public function createNewBill($action, $billing, $traceId)
    {
        try {
            $websystem = Websystem::first();
            $accountBank = $this->getBankInfo($billing->admin_id ?: 1);
            $pakets = $this->getBillingPackages($billing->customer_id);
            $firstPaket = is_iterable($pakets) ? $pakets->first() : null;
            $dueDate = $firstPaket && $firstPaket->due_date
                ? Carbon::parse($firstPaket->due_date)->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y')
                : 'Tidak ada jatuh tempo';
            $admin_contact = $this->formatter->formatReceiver($billing->admin_contact, null);

            if (empty($pakets) || (method_exists($pakets, 'isEmpty') && $pakets->isEmpty())) {
                LogHelper::logError(null, [
                    'billing_number'       => $billing->billing_number,
                    'customer_id'      => $billing->customer_id,
                    'customer_name'    => $billing->customer_name,
                    'contact_customer' => $billing->customer_contact,
                    'trace_id'         => $traceId,
                ], 404, "Package or bill not found", $traceId);
                return false;
            }

            $paketsText = $pakets->map(fn($paket) => $this->formatPaketInfo($paket))->implode("\n---\n");

            $replace = [
                '%name%'            => $billing->customer_name,
                '%address%'         => $billing->customer_address,
                '%customer_id%'     => $billing->customer_unique_id,
                '%billing_number%'  => $billing->billing_number ?? 'Nomor tidak tersedia',
                '%account_bank%'    => $accountBank,
                '%whatsapp_number%' => $admin_contact,
                '%total-bill%'      => 'Rp. ' . number_format($billing->total_amount, 2),
                '%pakets%'          => $paketsText,
                '%company_address%' => $websystem->address . ', ' . $websystem->city . ' ' . $websystem->postal_code,
                '%deadline%'        => $dueDate,
                '%admin%'           => $billing->admin_name
            ];

            $message = $this->getMessageTemplate($action, $replace);

            SendWhatsappMessageJob::dispatch(
                $action,
                $billing->customer_contact,
                $message,
                $billing->customer_id,
                $traceId
            )->onQueue('default');

            LogHelper::logInfo(null, [
                'billing_number'       => $billing->billing_number,
                'customer_id'      => $billing->customer_id,
                'customer_name'    => $billing->customer_name,
                'contact_customer' => $billing->customer_contact,
                'trace_id'         => $traceId,
                'message_preview'  => mb_substr($message, 0, 100) . '...',
            ], 200, "Successfully queued WhatsApp billing reminder message");

            return true;
        } catch (\Exception $e) {
            LogHelper::logError($e, [
                'billing_number'       => $billing->billing_number,
                'customer_id'      => $billing->customer_id,
                'customer_name'    => $billing->customer_name,
                'contact_customer' => $billing->customer_contact,
                'trace_id'         => $traceId,
            ], 500, 'Failed to create bill or dispatch WhatsApp message');

            return false;
        }
    }

    // ===== send Billing Open Period Message =====
    // ============================================
    // =====  send Billing Reminder Message   =====

    public function sendPaymentReminders($traceId): void
    {
        $today = Carbon::today();

        $billings = [
            'past_due' => $this->getBillings($today, 'past_due'),
            'due_today' => $this->getBillings($today, 'due_today'),
            'upcoming' => $this->getBillings($today, 'upcoming')
        ];

        foreach ($billings as $type => $billingList) {
            $count = $billingList->count();
            LogHelper::logInfo(null, [], 200, "Found {$count} billing(s) for type '{$type}'", $traceId);

            $messageType = $this->getMessageType($type);
            foreach ($billingList as $billing) {
                $this->checkSendMessage($billing, $messageType, $traceId);
            }
        }
    }

    private function checkSendMessage($billing, $messageType, $traceId)
    {
        // Skip jika sudah pernah dikirim hari ini
        $alreadySuccessfullySent = MessageLog::where('customer_id', $billing->customer_id)
            ->where('message_type', $messageType)
            ->whereDate('sent_at', Carbon::today())
            ->exists();

        $alreadyFailedSent = MessageLogError::where('customer_id', $billing->customer_id)
            ->where('message_type', $messageType)
            ->whereDate('sent_at', Carbon::today())
            ->exists();

        if ($alreadySuccessfullySent || $alreadyFailedSent) {
            LogHelper::logWarning(null, [
                'customer_name' => $billing->customer_name,
                'message_type'  => $messageType,
            ], 409, "Skipping redundant message for {$billing->customer_name} with type {$messageType}.", $traceId);
            return;
        }

        return $this->sendBillingInfoMessage($billing, $messageType, $traceId);
    }

    private function getMessageType(string $billingType): string
    {
        return match ($billingType) {
            'past_due' => 'warning_bill',
            'due_today' => 'deadline_bill',
            'upcoming' => 'reminder_bill',
            default => 'reminder_bill'
        };
    }

    private function getBillings(Carbon $today, string $type)
    {
        $messageType = $this->getMessageType($type);

        $dateOps = match ($type) {
            'past_due' => '<',
            'due_today' => '=',
            'upcoming' => '>',
            default => null
        };

        if (!$dateOps) return collect();

        $baseQuery = Billing::query()
            ->join('customers', 'billings.customer_id', '=', 'customers.id')
            ->leftJoin('users', 'customers.user_id', '=', 'users.id')
            ->leftJoin('message_logs', function ($join) use ($today, $messageType) {
                $join->on('customers.id', '=', 'message_logs.customer_id')
                    ->whereDate('message_logs.created_at', '=', $today)
                    ->where('message_logs.message_type', '=', $messageType);
            })
            ->leftJoin('message_log_errors', function ($join) use ($today, $messageType) {
                $join->on('customers.id', '=', 'message_log_errors.customer_id')
                    ->whereDate('message_log_errors.created_at', '=', $today)
                    ->where('message_log_errors.message_type', '=', $messageType);
            })
            ->select(
                'billings.id as billing_id',
                'billings.customer_id',
                'billings.billing_number',
                'customers.customer_id as customer_unique_id',
                'customers.name as customer_name',
                'customers.contact as customer_contact',
                'customers.address as customer_address',
                'users.id as admin_id',
                'users.name as admin_name',
                'users.contact as admin_contact'
            )
            ->whereNotNull('customers.contact')
            ->where('customers.contact', '!=', '')
            ->whereNull('message_logs.id')
            ->whereNull('message_log_errors.id')
            ->where(function ($query) use ($today, $dateOps) {
                $query->where(function ($q) use ($today, $dateOps) {
                    $q->where('billings.status', 'BL')
                        ->whereNotNull('billings.due_date')
                        ->whereDate('billings.due_date', $dateOps, $today);
                })->orWhere(function ($q) use ($today, $dateOps) {
                    $q->where('billings.status', 'PL')
                        ->whereNotNull('billings.pay_later')
                        ->whereDate('billings.pay_later', $dateOps, $today);
                });
            });

        return $baseQuery->limit(20)->get();
    }

    public function sendBillingInfoMessage($billing, $action, $traceId)
    {
        if (!is_object($billing) || empty(trim($billing->customer_contact ?? ''))) {
            LogHelper::logError(null, [
                'billing_id' => $billing->id ?? null,
                'action' => $action,
            ], 400, "Billing atau contact customer tidak valid.", $traceId);
            return null;
        }

        $address = $billing->customer_address ?? 'Alamat tidak tersedia';
        $customer_unique_id = $billing->customer_unique_id ?? 'Customer ID tidak tersedia';

        $contacts = [
            'customer' => [
                'raw' => $billing->customer_contact ?? null,
                'label' => 'customer',
                'check' => 'broadcast',
                'id' => $billing->customer_id ?? null,
                'name' => $billing->customer_name ?? null,
            ],
            'admin' => [
                'raw' => $billing->admin_contact ?? null,
                'label' => 'admin',
                'check' => null,
                'id' => $billing->admin_id ?? null,
                'name' => $billing->admin_name ?? null,
            ]
        ];

        foreach ($contacts as $type => $data) {
            $processed = $this->formatter->formatReceiver($data['raw'], $data['check']);
            if (!$processed) {
                LogHelper::logError(null, [
                    "{$data['label']}_id" => $data['id'],
                    "{$data['label']}_name" => $data['name'],
                    "contact_{$data['label']}" => $data['raw'],
                ], 400, "Nomor kontak {$data['label']} tidak valid.");
                return null;
            }
            ${"{$type}_contact"} = $processed;
            ${"{$type}_id"} = $data['id'];
            ${"{$type}_name"} = $data['name'];

            if ($type === 'admin') {
                $admin_id = $data['id'];
                $admin_contact = $data['raw'];
            }
        }
        $accountBank = $this->getBankInfo($admin_id ?? 1);

        $pakets = $this->getBillingPackages($billing->customer_id);
        if ($pakets->isEmpty()) {
            LogHelper::logError(null, [
                'customer_id' => $billing->customer_id,
                'customer_name' => $customer_name ?? $billing->customer_name,
                'contact_customer' => $customer_contact ?? $billing->customer_contact,
            ], 404, "Paket atau tagihan tidak ditemukan.", $traceId);
            return null;
        }

        $totalAmount = $pakets->sum('total_amount');
        $dueDate = $pakets->pluck('due_date')->filter()->sort()->first();
        $remainingDays = $dueDate ? Carbon::today()->diffInDays(Carbon::parse($dueDate), false) : 0;

        $paketsText = $pakets->map(fn($paket) => $this->formatPaketInfo($paket))->implode("\n---\n");

        $replace = [
            '%name%' => $customer_name ?? $billing->customer_name,
            '%address%' => $address,
            '%customer_id%' => $customer_unique_id,
            '%billing_number%' => $billing->billing_number ?? 'Nomor tidak tersedia',
            '%account_bank%' => $accountBank,
            '%day%' => $remainingDays,
            '%pakets%' => $paketsText,
            '%whatsapp_number%' => $admin_contact,
            '%total-bill%' => 'Rp. ' . number_format($totalAmount, 2),
            '%count-bill%' => $pakets->count()
        ];

        $message = $this->getMessageTemplate($action, $replace);
        SendWhatsappMessageJob::dispatch($action, $billing->customer_contact, $message, $billing->customer_id, $traceId)->onQueue('whatsapp_broadcast');
        LogHelper::logInfo(null, [
            'customer_id' => $billing->customer_id,
            'customer_name' => $customer_name ?? $billing->customer_name,
            'contact_customer' => $customer_contact ?? $billing->customer_contact,
            'message_type' => $action
        ], 200, "Send Billing Info Message Successfully scheduled", $traceId);
        return true;
    }

    // =====  send Billing Reminder Message   =====
    // ============================================
    // =====            Changes paket         =====
    public function changesPackage(int $customerId, ?string $oldPaketName, ?int $oldPaketPrice, string $traceId): void
    {
        $action = 'changes_paket';
        $admin_number = '-';

        try {
            LogHelper::logInfo(null, compact('customerId', 'action', 'traceId'), 200, '⏳ Mulai proses changes package', $traceId);

            $customer = Customer::find($customerId);
            if (!$customer) {
                LogHelper::logWarning(null, ['customerId' => $customerId], 404, "❌ Customer ID tidak ditemukan", $traceId);
                return;
            }

            if (empty($customer->contact)) {
                LogHelper::logWarning(null, ['customer_id' => $customer->id], 400, "❌ Nomor kontak pelanggan kosong, tidak bisa mengirim pesan", $traceId);
                return;
            }

            $customer_contact = $this->formatter->formatReceiver($customer->contact);

            $admin = User::find($customer->user_id) ?? User::find(1);
            $admin_number = !empty($admin->contact)
                ? $this->formatter->formatReceiver($admin->contact, null, false)
                : '-';

            LogHelper::logInfo(null, [
                'admin_id' => $admin->id ?? null,
                'admin_contact' => $admin->contact ?? null,
                'admin_number' => $admin_number
            ], 200, '✅ Admin info berhasil diambil dan diformat', $traceId);

            $accountBank = $this->getBankInfo($admin->id ?? 1);

            LogHelper::logInfo(null, ['account_bank' => $accountBank], 200, '✅ Info rekening admin berhasil diambil', $traceId);

            $replace = [
                '%name%'              => $customer->name,
                '%address%'           => $customer->address,
                '%email%'             => $customer->email ?? '-',
                '%customer_service%'  => $admin_number,
                '%customer_id%'       => $customer->customer_id,
                '%admin%'             => $admin->name ?? '-',
                '%old_paket%'         => $oldPaketName ?? '-',
                '%old_bill%'          => 'Rp. ' . number_format($oldPaketPrice ?? 0, 2, ',', '.'),
                '%new_paket%'         => $customer->paket->name ?? '-',
                '%new_bill%'          => 'Rp. ' . number_format($customer->paket->price ?? 0, 2, ',', '.'),
                '%account_bank%'      => $accountBank,
            ];

            LogHelper::logInfo(null, ['replace_keys' => array_keys($replace)], 200, '🧩 Placeholder pengganti disiapkan', $traceId);

            $message = $this->getMessageTemplate($action, $replace);
            if (!$message) {
                LogHelper::logWarning(null, [
                    'customer_id' => $customer->id,
                    'action'      => $action
                ], 404, "❌ Template pesan untuk action `{$action}` tidak ditemukan", $traceId);
                return;
            }

            LogHelper::logInfo(null, [
                'customer_id'       => $customer->id,
                'customer_contact'  => $customer->contact,
                'final_message'     => $message,
                'action'            => $action,
            ], 200, "✅ Pesan WhatsApp siap dikirim ke {$customer->contact}", $traceId);

            SendWhatsappMessageJob::dispatch($action, $customer_contact, $message, $customer->id, $traceId)
                ->onQueue('default');
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'customer_id'    => $customerId,
                'admin_number'   => $admin_number,
                'action'         => $action,
                'trace_id'       => $traceId,
                'error_message'  => $e->getMessage(),
                'error_file'     => $e->getFile(),
                'error_line'     => $e->getLine()
            ], 500, "❌ Exception terjadi saat proses {$action}: {$e->getMessage()}", $traceId);
        }
    }

    // =====            Changes paket         =====
    // ============================================
    // =====   Test WhatsApp Message Manual   =====

    public function test_gateway($gateway, $receive_number, $sender_number)
    {
        $traceId = (string) Str::uuid();
        try {
            LogHelper::logInfo(
                null,
                [
                    'sender_number' => $sender_number,
                    'receive_number' => $receive_number,
                    'gateway_id' => $gateway->id ?? null
                ],
                100, // Continue
                "Starting test_gateway process"
            );

            $whatsapp_number = WhatsappNumber::where('number', $sender_number)->first();

            if (!$whatsapp_number) {
                LogHelper::logError(
                    null,
                    [
                        'sender_number' => $sender_number ?? null,
                        'whatsapp_number_id' => $whatsapp_number->id ?? null
                    ],
                    404,
                    "WhatsApp number not found"
                );
                return redirect()->back()->with('error', 'WhatsApp number or gateway not found.');
            }

            LogHelper::logInfo(
                null,
                ['whatsapp_number_id' => $whatsapp_number->id],
                101,
                "Found WhatsApp number"
            );

            if (!$receive_number) {
                LogHelper::logWarning(
                    null,
                    ['sender_number' => $sender_number],
                    400,
                    "Receive number is missing"
                );
                return redirect()->back()->with('error', 'Receive number is required.');
            }

            LogHelper::logInfo(
                null,
                [
                    'gateway_id' => $gateway->id ?? null,
                    'receive_number' => $receive_number
                ],
                102,
                "Attempting to send test message"
            );

            try {
                $message = 'Congratulations!! Test message from Customer Management WA Gateway';
                SendWhatsappMessageJob::dispatch('test_message', $receive_number, $message, null, $traceId)->onQueue('default');

                LogHelper::logInfo(
                    null,
                    [
                        'receive_number' => $receive_number,
                        'gateway_id' => $gateway->id
                    ],
                    200,
                    "Test message dispatched successfully"
                );
                return redirect()->back()->with('success', 'Test message sent successfully.');
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    ['receive_number' => $receive_number],
                    500,
                    "Failed to send WhatsApp message: {$e->getMessage()}"
                );
                return redirect()->back()->with('error', 'Failed to send test message.');
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'sender_number' => $sender_number,
                    'receive_number' => $receive_number
                ],
                500,
                "Unexpected error in test_gateway: {$e->getMessage()}"
            );
            return redirect()->back()->with('error', 'An unexpected error occurred. Please try again.');
        }
    }

    // =====   Test WhatsApp Message Manual   =====
}
