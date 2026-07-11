<?php

namespace App\Services;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Models\AutoIsolir;
use App\Models\Billings\Billing;
use App\Models\Mikrotik;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerSubscription;
use App\Http\Resources\Pppoe\SecretResource;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Jobs\CancelPaymentBeforeDueDateJob;
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticBeforeDueDateJob;
use App\Jobs\CancelPaymentIpStaticAfterDueDateJob;
use Illuminate\Support\Carbon;

class RouterCommentSyncService
{
    private SecretService $secretService;
    private IpStaticService $ipStaticService;

    public function __construct(SecretService $secretService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
    }

    /**
     * SINKRONISASI COMMENT UNTUK YANG SUDAH BAYAR (LS)
     */
    public function syncPaymentComment(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $customer = $billing->customer;

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if ($this->isRouterInvalid($router, $autoIsolir, $customer, $traceId, $timer)) return;

        $commentPayment = $this->calculatePaymentComment($autoIsolir, $billing, $traceId);

        if ($customer->internet_service === 'ppp') {
            $this->dispatchPppPaymentJob($router, $customer, $commentPayment, $traceId, $timer);
        } elseif ($customer->internet_service === 'ip_static') {
            $this->dispatchIpStaticPaymentJob($router, $customer, $autoIsolir, $commentPayment, $traceId, $timer);
        }
    }

    /**
     * SINKRONISASI COMMENT UNTUK YANG BELUM BAYAR (UNPAYMENT)
     */
    public function syncUnpaymentComment(Billing $billing, string $traceId): void
    {
        $timer = new ExecutionTimer();
        $customer = $billing->customer;

        $router = Mikrotik::find($customer->mikrotik_id);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

        if ($this->isRouterInvalid($router, $autoIsolir, $customer, $traceId, $timer)) return;

        $day = Carbon::now()->format('d');
        $dueDate = ($autoIsolir->activation_date === 'false') ? $autoIsolir->due_date : Carbon::parse($customer->activation_date)->format('d');
        $commentUnpayment = $this->calculateUnpaymentComment($autoIsolir, $customer);

        $context = [
            'customer_username' => $customer->username,
            'router_name' => $router->name,
            'service' => $customer->internet_service,
            'comment' => $commentUnpayment
        ];

        if ($customer->internet_service === 'ppp') {
            if ($day <= $dueDate) {
                dispatch(new CancelPaymentBeforeDueDateJob($router, $commentUnpayment, $customer->secret_id, $traceId));
                $context['job'] = 'CancelPaymentBeforeDueDateJob';
            } else {
                dispatch(new CancelPaymentAfterDueDateJob($router, $commentUnpayment, $autoIsolir->profile_id, $customer->secret_id, $customer->username, $traceId));
                $context['job'] = 'CancelPaymentAfterDueDateJob';
            }
            LogHelper::logInfo('SyncUnpaymentComment', $context, 200, "Unpayment job dispatched for PPP customer", $traceId, $timer->lap());
        } elseif ($customer->internet_service === 'ip_static') {
            $comment = $customer->username . '_' . $commentUnpayment;
            $context['comment_static'] = $comment;

            if ($day <= $dueDate) {
                dispatch(new CancelPaymentIpStaticBeforeDueDateJob($router, $customer->simpleque_id, $commentUnpayment, $traceId));
                $context['job'] = 'CancelPaymentIpStaticBeforeDueDateJob';
            } else {
                dispatch(new CancelPaymentIpStaticAfterDueDateJob($router, $autoIsolir->address_list_isolir, $customer->ip_static, $customer->simpleque_id, $comment, $traceId));
                $context['job'] = 'CancelPaymentIpStaticAfterDueDateJob';
            }
            LogHelper::logInfo('SyncUnpaymentComment', $context, 200, "Unpayment job dispatched for IP Static customer", $traceId, $timer->lap());
        }
    }

    // --- HELPER METHODS ---

    private function isRouterInvalid(Mikrotik $router, AutoIsolir $autoIsolir, Customer $customer, string $traceId, ExecutionTimer $timer): bool
    {
        if (!$router || $router->disabled === 'true' || !$autoIsolir || $autoIsolir->disabled === 'true') {
            LogHelper::logError('RouterValidation', [
                'customer_id' => $customer->id,
                'customer_username' => $customer->username,
                'router_exists' => (bool)$router,
                'router_enabled' => $router ? $router->disabled !== 'true' : false,
                'auto_isolir_exists' => (bool)$autoIsolir,
                'auto_isolir_enabled' => $autoIsolir ? $autoIsolir->disabled !== 'true' : false,
            ], 422, "Skipping sync: Router or AutoIsolir configuration is invalid/disabled", $traceId, $timer->lap());
            return true;
        }
        return false;
    }

    private function calculatePaymentComment(AutoIsolir $autoIsolir, Billing $billing, string $traceId): string
    {
        if ($autoIsolir->activation_date === 'false') {
            return $autoIsolir->comment_payment;
        }

        $billing->loadMissing('billingSubscriptionPeriode.billingPeriode');
        $billingSubPeriode = $billing->billingSubscriptionPeriode;
        $billingPeriode = $billingSubPeriode ? $billingSubPeriode->billingPeriode : null;

        if (!$billingSubPeriode || !$billingPeriode) {
            return $autoIsolir->comment_payment;
        }

        $activationDate = Carbon::parse($billing->customer->activation_date)->format('d');
        $nextMonth = Carbon::parse($billingPeriode->name)->addMonth()->format('m_Y');
        $totalDaysInMonth = Carbon::parse($billingPeriode->name)->copy()->addMonth()->daysInMonth;

        if ((int)$activationDate > $totalDaysInMonth) {
            $activationDate = $totalDaysInMonth;
        }

        return $autoIsolir->comment_unpayment . '_' . $activationDate . '_' . $nextMonth;
    }

    private function calculateUnpaymentComment(AutoIsolir $autoIsolir, Customer $customer): string
    {
        if ($autoIsolir->activation_date === 'false') {
            return $autoIsolir->comment_unpayment;
        }

        $date = Carbon::parse($customer->activation_date)->format('d');
        $periodeName = \App\Models\Billings\BillingPeriode::latest('created_at')->value('name') ?? Carbon::now()->format('Y-m-d');

        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');

        return $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
    }

    private function dispatchPppPaymentJob(Mikrotik $router, Customer $customer, string $commentPayment, string $traceId, ExecutionTimer $timer)
    {
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logWarning('DispatchPppPayment', ['customer' => $customer->username], 200, "Mikrotik actions disabled by config", $traceId, $timer->lap());
            return;
        }

        $userSecretId = $this->secretService->getSecretById($router, $customer->secret_id, $traceId);
        if ($userSecretId === 'error') {
            LogHelper::logError('DispatchPppPayment', ['customer' => $customer->username, 'secret_id' => $customer->secret_id], 404, "Secret ID not found on Routerboard", $traceId, $timer->lap());
            return;
        }

        $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
        $customerSubscription = CustomerSubscription::with(['package'])
            ->where('customer_id', $customer->id)
            ->where('status', 'active')->first();
        $customerProfile = $customerSubscription->package->profile ?? null;

        $context = [
            'customer_username' => $customer->username,
            'router_name' => $router->name,
            'comment' => $commentPayment
        ];

        if ($secretProfiles === $customerProfile) {
            dispatch(new AddPaymentBeforeDueDateJob($router, $commentPayment, $customer->secret_id, $traceId));
            $context['job'] = 'AddPaymentBeforeDueDateJob';
        } else {
            dispatch(new AddPaymentAfterDueDateJob($router, $customerProfile, $commentPayment, $customer->secret_id, $customer->username, $traceId));
            $context['job'] = 'AddPaymentAfterDueDateJob';
        }

        LogHelper::logInfo('SyncPaymentComment', $context, 200, "Payment job dispatched for PPP customer", $traceId, $timer->lap());
    }

    private function dispatchIpStaticPaymentJob(Mikrotik $router, Customer $customer, AutoIsolir $autoIsolir, string $commentPayment, string $traceId, ExecutionTimer $timer)
    {
        if (!config('services.shanetfiber.allow_mikrotik_actions')) {
            LogHelper::logWarning('DispatchIpStaticPayment', ['customer' => $customer->username], 200, "Mikrotik actions disabled by config", $traceId, $timer->lap());
            return;
        }

        $addressListCustomer = $this->ipStaticService->getAddressListByIpAddress($router, $customer->ip_static, $traceId);
        $comment = $customer->username . '_' . $commentPayment;

        $context = [
            'customer_username' => $customer->username,
            'router_name' => $router->name,
            'comment' => $comment
        ];

        if (isset($addressListCustomer[0]['list']) && $addressListCustomer[0]['list'] == $autoIsolir->address_list_isolir) {
            dispatch(new AddPaymentIpStaticAfterDueDateJob($router, $customer->ip_static, $customer->simpleque_id, $autoIsolir->address_list_isolir, $commentPayment, $comment, $traceId));
            $context['job'] = 'AddPaymentIpStaticAfterDueDateJob';
        } else {
            dispatch(new AddPaymentBeforeDueDateJob($router, $customer->simpleque_id, $commentPayment, $traceId));
            $context['job'] = 'AddPaymentBeforeDueDateJob';
        }

        LogHelper::logInfo('SyncPaymentComment', $context, 200, "Payment job dispatched for IP Static customer", $traceId, $timer->lap());
    }
}
