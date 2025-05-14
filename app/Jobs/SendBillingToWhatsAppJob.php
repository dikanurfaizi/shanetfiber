<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Bank;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsappMessageService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use App\Models\WhatsAppGateway;
use App\Helpers\LogHelper;
use App\Jobs\SendSingleWhatsappMessageJob;
use Carbon\Carbon;

class SendBillingToWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WhatsappMessageService $messageService;

    public function __construct()
    {
        $this->messageService = app(WhatsappMessageService::class);
    }

    public function handle(): void
    {
        try {
            $gateway = WhatsAppGateway::where('disabled', false)->first();

            if (!$gateway) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No active WhatsApp gateway found."
                );
                return;
            }

            $lastBillingPeriode = BillingPeriode::latest('created_at')->first();

            if (!$lastBillingPeriode) {
                LogHelper::logWarning(
                    null,
                    null,
                    404, // Not Found
                    "No billing period found."
                );
                return;
            }

            $billings = Billing::query()
                ->join('customers', 'billings.customer_id', '=', 'customers.id')
                ->leftJoin('users', 'customers.user_id', '=', 'users.id')
                ->select(
                    'billings.id as billing_id',
                    'billings.customer_id',
                    'customers.customer_id as customer_unique_id',
                    'customers.name as customer_name',
                    'customers.contact as customer_contact',
                    'customers.address as customer_address',
                    'billings.due_date',
                    'users.name as admin_name',
                    DB::raw('COUNT(billings.id) as total_bills'),
                    DB::raw('SUM(billings.paket_price) as total_amount')
                )
                ->whereNotNull('customers.contact')
                ->where('customers.contact', '!=', '')
                ->where(function ($query) {
                    $query->where('billings.status', 'BL')
                        ->whereNotNull('billings.due_date');
                })
                ->orWhere(function ($query) {
                    $query->where('billings.status', 'PL')
                        ->whereNotNull('billings.pay_later');
                })
                ->groupBy(
                    'billings.id',
                    'billings.customer_id',
                    'customers.customer_id',
                    'customers.name',
                    'customers.contact',
                    'customers.address',
                    'billings.due_date',
                    'users.name'
                )
                ->orderBy('customers.name')
                ->cursor();

            $count = 0;
            $delayInSeconds = 0;

            foreach ($billings as $billing) {
                $count++;
                $delayInSeconds += 2; // Increment delay by 2 seconds for each message

                // Dispatch individual message job with cumulative delay
                SendWhatsappSingleJob::dispatch($billing, 'create_new_bill', $gateway)
                    ->delay(now()->addSeconds($delayInSeconds));
            }

            if ($count === 0) {
                LogHelper::logWarning(
                    null,
                    ['periode_id' => $lastBillingPeriode->id],
                    404,
                    "No billing data found."
                );
                return;
            }

            LogHelper::logInfo(
                null,
                [
                    'periode_id' => $lastBillingPeriode->id,
                    'count' => $count,
                    'gateway_id' => $gateway->id
                ],
                200,
                "Dispatched {$count} WhatsApp messages with 2-second intervals"
            );
        } catch (RequestException $e) {
            LogHelper::logError(
                null,
                ['billing_id' => null, 'customer_id' => null],
                500,
                "HTTP Request Exception: " . $e->getMessage()
            );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['billing_id' => null, 'customer_id' => null],
                500,
                "Exception occurred while creating billing: " . $e->getMessage()
            );
        }
    }
}
