<?php

namespace App\Http\Controllers\Tripay;

use Exception;
use App\Models\Order;
use App\Models\Packages\Package;
use App\Models\Customers\Customer;

use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use Illuminate\Support\Carbon;
use App\Models\Billings\Billing;
use App\Http\Controllers\Controller;
use App\Models\Billings\BillingPeriode;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Services\Midtrans\CallbackService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
use Illuminate\Support\Str;

class PaymentCallbackController extends Controller
{
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
    }

    public function receive()
    {
        $traceId = (string) Str::uuid();
        $callback = new CallbackService;

        if ($callback->isSignatureKeyVerified()) {
            $notification = $callback->getNotification();
            $order = $callback->getOrder();

            if ($callback->isSuccess()) {
                Order::where('id', $order->id)->update([
                    'payment_status' => 2,
                    'transaction_id' => $notification->transaction_id,
                    'payment_type' => $notification->payment_type,
                    'payment_time' => $notification->transaction_time,
                ]);
                $sameOrdersNotUse = Order::where('billing_id', $order->billing_id)->where('payment_status', '!=', 2)->get();
                foreach ($sameOrdersNotUse as $orderNotUse) {
                    $orderNotUse->delete();
                }


                $billingId = Order::where('id', $order->id)->value('billing_id');
                $billing = Billing::where('id', $billingId)->first();
                $billing->update([
                    'payment_time' => Carbon::parse($notification->transaction_time)->format('Y-m-d h:i:s'),
                    'teller_name' => $notification->payment_type,
                    'status' => 'LS'
                ]);

                //  $activationDate = Carbon::parse($notification->transaction_time)->format('d F Y');
                $customerId = Order::where('id', $order->id)->value('customer_id');
                $customer = Customer::where('id', $customerId)->first();
                $mikrotikId = $customer->mikrotik_id;
                $router = Mikrotik::where('id', $mikrotikId)->first();
                $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();


                //Pembayaran Bulanan memperpanjang aktivasi router
                $customerPackageId = $customer->package_id;
                $customerProfile = Package::where('id', $customerPackageId)->value('profile');
                $customerSecretId = $customer->secret_id;
                $customerUsername = $customer->username;
                $day = Carbon::now()->format('d');
                $date = Carbon::parse($customer->activation_date)->format('d');
                $periodeName = BillingPeriode::latest('created_at')->value('name');
                $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
                if ($autoIsolir != NULL) {
                    if ($autoIsolir->activation_date == 'false') {
                        $comment_payment = $autoIsolir->comment_payment;
                        $dueDate = $autoIsolir->due_date;
                    } else {
                        $comment_payment = $autoIsolir->comment_unpayment . '_' . $date . '_' . $nextMonth;
                        $dueDate = $date;
                    }

                    if ($router->disabled == 'false') {
                        try {
                            //dispatch(new TestConnectionJob($router))->onQueue('default');
                            $this->testConnectionService->checkOnline($router, $traceId);
                            $serverOnline = 'true';
                        } catch (\Exception $e) {
                            // anywhere
                            $serverOnline = 'false';
                        }
                        if ($serverOnline == 'true') {
                            if ($day <= $dueDate) {
                                $customerSecret = $this->secretService->getSecretById($router, $customerSecretId, $traceId);
                                $secretProfiles = SecretResource::collection($customerSecret)->value('profile');
                                if ($secretProfiles == $customerProfile) {
                                    dispatch(new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerSecretId, $traceId))->onQueue('default');
                                } else {
                                    dispatch(new AddPaymentAfterDueDateJob($router, $customerProfile, $comment_payment, $customerSecretId, $customerUsername, $traceId))->onQueue('default');
                                }
                            } else {
                                dispatch(new AddPaymentAfterDueDateJob($router, $customerProfile, $comment_payment, $customerSecretId, $customerUsername, $traceId))->onQueue('default');
                            }
                        }
                    }
                }
            }

            if ($callback->isExpire()) {
                Order::where('id', $order->id)->update([
                    'payment_status' => 3,
                ]);
            }

            if ($callback->isCancelled()) {
                Order::where('id', $order->id)->update([
                    'payment_status' => 4,
                ]);
            }

            return response()
                ->json([
                    'success' => true,
                    'message' => 'Notifikasi berhasil diproses',
                ]);
        } else {
            return response()
                ->json([
                    'error' => true,
                    'message' => 'Signature key tidak terverifikasi',
                ], 403);
        }
    }
}
