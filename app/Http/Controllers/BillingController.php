<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Customer;
use App\Models\Mikrotik;
use Carbon\CarbonPeriod;
use App\Models\AutoIsolir;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\CreateBillingJob;
use Illuminate\Support\Carbon;
use App\Jobs\TestConnectionJob;
use App\Models\Billing\Billing;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\BillingStoreRequest;
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentBeforeDueDateJob;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Requests\BillingStoreOneRequest;
use App\Models\Bank;
use App\Models\Receipt;
use App\Models\Websystem;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
use PDF;

use Illuminate\Contracts\Database\Eloquent\Builder;
//use Barryvdh\DomPDF\PDF;

class BillingController extends Controller
{

    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:billing-create', ['only' => ['create', 'store', 'create_one', 'store_one']]);
        $this->middleware('permission:billing-edit', ['only' => ['edit', 'update', 'payment', 'unpayment', 'transfer', 'paylater', 'post_paylater']]);
        $this->middleware('permission:billing-delete', ['only' => ['deletePeriode', 'destroyPeriode']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $paginate = 25;
        $print = $request->cetak_pdf;
        $searchName = $request->search_name;
        $searchMonth = $request->search_month;
        $searchYear = $request->search_year;
        $searchPeriode = $searchMonth . ' ' . $searchYear;
        $searchStatus = $request->search_status;
        // return dd($searchStatus);
        $searchAddress = $request->search_address;
        $searchDuedate = $request->duedate;
        if ($request->duedate) {
            $searchDuedate = Carbon::parse($request->duedate)->format('Y-m-d');
        }
        $customer = $request->customer;
        // $searchDuedate = $request->search_name;
        //return dd(Carbon::parse($searchDuedate));
        if (!$customer && !$searchName && !$request->search_month && !$request->search_year && !$searchStatus && !$searchAddress && !$searchDuedate) {

            $searchPeriode = BillingPeriode::latest('created_at')->value('name');
            $searchMonth = Carbon::parse($searchPeriode)->format('F');
            $searchYear = Carbon::parse($searchPeriode)->format('Y');
        }
        $oldPaymentsOff = Billing::where('status', '!=', 'LS')->oldest('created_at')->get();
        $billings = Billing::query()
            ->when($searchName, function ($query) use ($searchName) {
                $query->where('customer_name', 'like', "%{$searchName}%");
            })

            ->whereHas('customer', function ($query) use ($customer) {
                if ($customer) {
                    $query->where('slug', $customer);
                }
            })
            ->whereHas('customer', function ($query) use ($searchAddress) {
                if ($searchAddress && $searchAddress != 'no-address') {
                    $query->where('address', $searchAddress);
                } else if ($searchAddress == 'no-address') {
                    $query->where('address', null);
                }
            })
            ->whereHas('billing_periode', function ($query) use ($searchPeriode) {
                $query->where('name', 'like', "%{$searchPeriode}%");
            })
            ->when($searchStatus, function ($query) use ($searchStatus, $searchDuedate) {
                if ($searchStatus == '!LS') {
                    $searchStatus = 'BL & PL';
                    if (isset($searchDuedate)) {
                        $query->where('status', 'BL');
                        $query->where('due_date', 'like', "%{$searchDuedate}%");
                    } else {
                        $query->where('status', '!=', 'LS');
                    }
                } else {
                    $query->where('status', $searchStatus);
                    if (isset($searchDuedate)) {
                        if ($searchStatus == 'LS') {
                            $query->where('payment_time', 'like', "%{$searchDuedate}%");
                        } else if ($searchStatus == 'PL') {
                            $query->where('pay_later', 'like', "%{$searchDuedate}%");
                        }
                    }
                }
            })
            ->orderBy(
                BillingPeriode::select('created_at')
                    ->whereColumn('billing_periodes.id', 'billings.billing_periode_id'),
                'DESC'
            )
            ->orderBy(
                Customer::select('address')
                    ->whereColumn('customers.id', 'billings.customer_id')
            )
            ->orderBy('customer_name', 'ASC');
        $dataPrint = $billings->get();
        $totalPrice = $billings->sum('paket_price');
        $data = $billings->paginate($paginate);
        // return dd($data);
        if ($searchStatus == '!LS') {
            $searchStatus = 'BL & PL';
        }
        $search = [
            'month' => $searchMonth,
            'year' => $searchYear,
            'status' => $searchStatus,
            'name' => $searchName,
            'address' => $searchAddress,
        ];
        if ($print == 'true') {
            $pdf = PDF::loadView('print.billing', ['dataPrint' => $dataPrint], $search);
            return $pdf->download('Tagihan_' . str($search['name'] ?? 'all_name')->slug() . '_' . str($search['address'] ?? 'all_address')->slug() . '_' . str($search['status'] ?? 'all_status')->slug() . '_' . str($search['month'] ?? 'all_month')->slug() . '_' . $search['year'] ?? 'all_years' . '.pdf');
        } else {
            return view('billings.index', compact('data', 'totalPrice', 'search', 'oldPaymentsOff'))
                ->with('i', (request()->input('page', 1) - 1) * $paginate);
        }
    }

    public function create()
    {
        $billingPeriodeName = BillingPeriode::latest('created_at')->value('name');
        $periodeNow = Carbon::now()->format('F Y');
        if ($periodeNow == $billingPeriodeName) {
            $billingPeriodes = CarbonPeriod::create(now()->addmonth(), '1 month', now()->addMonths(12));
        } else {
            $billingPeriodes = CarbonPeriod::create(now(), '1 month', now()->addMonths(11));
        }
        $data = BillingPeriode::paginate(12);

        return view('billings.create', compact('data', 'billingPeriodes'))
            ->with('i', (request()->input('page', 1) - 1) * 12);
    }

    public function create_one()
    {
        $periodes = BillingPeriode::latest()->get();
        return view('billings.create_one', compact('periodes'));
    }
    /**
     * Store a newly created resource in storage.
     * Add Comment "Belum Lunas" on User Secret Mikrotik 
     */
    // public function store(BillingStoreRequest $request)
    public function store(Request $request)
    {
        $periode = $request->name;
        if (!$periode) {
            $periode = Carbon::now()->format('F Y');
        }
        $periodeExplode = explode(' ', $periode);
        $monthPeriode = $periodeExplode[0];
        $yearPeriode = $periodeExplode[1];

        $this->validate(
            $request,
            [
                'name' => 'unique:billing_periodes',
            ],
            [
                'name.unique' => 'Periode ' . $request->name . ' already exist.',
            ]
        );
        $billingPeriode = BillingPeriode::create([
            'name' => $periode,
            'month' => $monthPeriode,
            'year' => $yearPeriode,
        ]);
        $newBillingPeriode = $billingPeriode->fresh();
        $websystem = Websystem::where('slug', 'websystem')->first();
        $totalBillingCreated = 0;
        $routers = Mikrotik::where('disabled', 'false')->get();
        foreach ($routers as $router) {
            $customers = Customer::where('disabled', 'false')
                ->where('activation', 'true')
                ->where('mikrotik_id', $router->id)
                ->get();
            // return dd($customers);
            // $billingId = Billing::where('billing_periode_id', $newBillingPeriode->id)
            // $billingId = Billing::where('billing_periode_id', 3)
            //     ->value('id');
            $billing = Billing::select('customer_id')
                ->where('billing_periode_id', $newBillingPeriode->id)
                ->get();
            //return dd($billing);
            $customersUnbilling = $customers->diff(
                Customer::whereIn('id', $billing)
                    ->get()
            );
            $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();
            //return dd($customersUnbilling);
            foreach ($customersUnbilling as $customer) {
                // $previousCustomerBill = Billing::where('customer_id', $customer->id)
                //      ->where('status', 'BL')
                //      ->value('paket_price');
                $discountPrice = $customer->paket->price * ($customer->discount / 100);
                $customerPriceAfterDiscount = ceil($customer->paket->price - $discountPrice);
                if ($websystem->enable_tax == 'true') {
                    $customerTax = ceil($customerPriceAfterDiscount * ($websystem->tax_rate / 100));
                } else {
                    $customerTax = 0;
                }
                $customerActivationDate = Carbon::parse($customer->activation_date)->format('d');
                $periodeName = $newBillingPeriode->name;
                $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
                $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
                if ($customerActivationDate > $totalDaysInMonth) {
                    $customerActivationDate = $totalDaysInMonth;
                }
                $secretId = $customer->secret_id;
                // $customerMikrotikId = $customer->mikrotik_id;
                // $router = Mikrotik::where('id', $customerMikrotikId)->first();


                // if ($router->disabled == 'false') {
                if ($autoIsolir != NULL) {
                    if ($autoIsolir->disabled == 'false') {
                        if ($autoIsolir->activation_date == 'false') {
                            if ($customerPriceAfterDiscount > 0) {
                                $comment = $autoIsolir->comment_unpayment;
                            } else {
                                $comment = $autoIsolir->comment_payment;
                            }

                            try {
                                dispatch(new CreateBillingJob($router, $comment, $secretId))->onQueue('default');
                            } catch (\Exception $e) {
                                $billingPeriode->delete();
                                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
                            }
                        } else {
                            if ($customerPriceAfterDiscount == 0.0) {
                                $comment = $autoIsolir->comment_unpayment . '_' . $customerActivationDate . '_' . $nextMonth;
                                try {
                                    dispatch(new CreateBillingJob($router, $comment, $secretId))->onQueue('default');
                                } catch (\Exception $e) {
                                    $billingPeriode->delete();
                                    return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
                                }
                            }
                        }
                    }
                }
                //}

                if ($customerPriceAfterDiscount > 0) {
                    $statusPayment = 'BL';
                } else {
                    $statusPayment = 'LS';
                }

                $dueDate = $customerActivationDate . ' ' . Carbon::parse($billingPeriode->name)->format('F Y');
                Billing::create([
                    'billing_periode_id' => $billingPeriode->id,
                    'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                    'billing_number' => str($customer->id . '-' . Str::random(4)  . '-' . Carbon::parse($newBillingPeriode->name)->format('m') . '-' . Carbon::parse($newBillingPeriode->name)->format('Y'))->slug(),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'paket_name' => $customer->paket->name,
                    'paket_price' => $customerPriceAfterDiscount,
                    'tax' => $customerTax,
                    'created_name' => auth()->user()->name,
                    'status' => $statusPayment,
                    'due_date' => Carbon::parse($dueDate),
                ]);
                $totalBillingCreated++;
            }
        }

        return redirect()->route('billings.index')
            ->with('success', $totalBillingCreated . ' Billing created successfully.');
    }

    public function store_one(BillingStoreRequest $request)
    {
        $billingPeriodeId = $request->name;
        $billingPeriode = BillingPeriode::where('id', $billingPeriodeId)->first();
        $websystem = Websystem::where('slug', 'websystem')->first();
        $totalBillingCreated = 0;
        //$routers = Mikrotik::all();
        $routers = Mikrotik::where('disabled', 'false')->get();
        foreach ($routers as $router) {
            $customers = Customer::where('disabled', 'false')->where('mikrotik_id', $router->id)->where('activation', 'true')->get();
            $billing = Billing::select('customer_id')
                ->where('billing_periode_id', $billingPeriodeId)
                ->get();
            $customersUnbilling = $customers->diff(
                Customer::whereIn('id', $billing)
                    ->get()
            );
            foreach ($customersUnbilling as $customer) {
                // $customerPriceAfterDiscount = $customer->paket->price * ((100 - $customer->discount) / 100);

                $discountPrice = $customer->paket->price * ($customer->discount / 100);
                $customerPriceAfterDiscount = ceil($customer->paket->price - $discountPrice);
                if ($websystem->enable_tax == 'true') {
                    $customerTax = ceil($customerPriceAfterDiscount * ($websystem->tax_rate / 100));
                } else {
                    $customerTax = 0;
                }

                $customerActivationDate = Carbon::parse($customer->activation_date)->format('d');
                $periodeName = BillingPeriode::where('id', $billingPeriodeId)->value('name');
                $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
                $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
                if ($customerActivationDate > $totalDaysInMonth) {
                    $customerActivationDate = $totalDaysInMonth;
                }
                $secretId = $customer->secret_id;
                // $mikrotikId = $customer->mikrotik_id;
                // $router = Mikrotik::where('id', $mikrotikId)->first();
                $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();
                //  if ($router->disabled == 'false') {
                if ($autoIsolir != NULL) {
                    if ($autoIsolir->disabled == 'false') {
                        if ($autoIsolir->activation_date == 'false') {
                            if ($customerPriceAfterDiscount > 0) {
                                $comment = $autoIsolir->comment_unpayment;
                            } else {
                                $comment = $autoIsolir->comment_payment;
                            }
                            try {
                                dispatch(new CreateBillingJob($router, $comment, $secretId))->onQueue('default');
                            } catch (\Exception $e) {
                                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
                            }
                        } else {
                            if ($customerPriceAfterDiscount == 0.0) {
                                // $comment = $autoIsolir->comment_payment;
                                $comment = $autoIsolir->comment_unpayment . '_' . $customerActivationDate . '_' . $nextMonth;
                                try {
                                    dispatch(new CreateBillingJob($router, $comment, $secretId))->onQueue('default');
                                } catch (\Exception $e) {
                                    return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
                                }
                            }
                        }
                    }
                }
                // }

                if ($customerPriceAfterDiscount > 0) {
                    $statusPayment = 'BL';
                } else {
                    $statusPayment = 'LS';
                }
                $dueDate = $customerActivationDate . ' ' . Carbon::parse($periodeName)->format('F Y');
                Billing::create([
                    'billing_periode_id' => $billingPeriodeId,
                    'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                    'billing_number' => str($customer->id . '-' . Str::random(4)  . '-' . Carbon::parse($billingPeriode->name)->format('m') . '-' . Carbon::parse($billingPeriode->name)->format('Y'))->slug(),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'paket_name' => $customer->paket->name,
                    'paket_price' => $customerPriceAfterDiscount,
                    'tax' => $customerTax,
                    'created_name' => auth()->user()->name,
                    'status' => $statusPayment,
                    'due_date' => Carbon::parse($dueDate),
                ]);
                $totalBillingCreated++;
            }
        }

        return redirect()->route('billings.index')
            ->with('success', $totalBillingCreated . ' Billing created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Billing $billing)
    {
        $billingCustomerId = $billing->customer_id;
        $websystem = Websystem::where('slug', 'websystem')->first();
        $billings = Billing::where('customer_id', $billingCustomerId)
            ->where('status', '!=', 'LS')
            ->get();
        $totalPrice = Billing::where('customer_id', $billingCustomerId)
            ->where('status', '!=', 'LS')
            ->sum('paket_price');
        return view('billings.show', compact('billing', 'totalPrice', 'websystem'), [
            'billings' => $billings,
        ]);
    }


    public function paylater(Request $request, Billing $billing)
    {
        $billingCustomerId = $billing->customer_id;
        $billings = Billing::where('customer_id', $billingCustomerId)->where('status', 'BL')->get();
        return view('billings.paylater', compact('billing'), [
            'billings' => $billings,
        ]);
    }

    public function post_paylater(Request $request, Billing $billing)
    {
        $this->validate($request, [
            'pay_later' => 'required',
        ]);
        $paylaterDate = Carbon::parse($request->pay_later)->format('d');
        $paylaterMonth = Carbon::parse($request->pay_later)->format('m_Y');
        $customer = Customer::where('id', $billing->customer_id)->first();
        $customerPaketId = $customer->paket_id;
        $customerProfile = Paket::where('id', $customerPaketId)->value('profile');
        $customerSecretId = $customer->secret_id;
        $customerUsername = $customer->username;
        $mikrotikId = $customer->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotikId)->first();
        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
        if ($router->disabled == 'false' && $autoIsolir != NULL && $autoIsolir->disabled == 'false') {
            try {
                //dispatch(new TestConnectionJob($router))->onQueue('default');
                $userSecretId = $this->secretService->getSecretById($router, $customerSecretId);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
            }
            if ($autoIsolir->activation_date == 'false') {
                $comment_payment = $autoIsolir->comment_payment;
            } else {
                $comment_payment = $autoIsolir->comment_unpayment . '_' . $paylaterDate . '_' . $paylaterMonth;
            }

            if ($userSecretId != 'error') {
                $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
                if ($secretProfiles == $customerProfile) {
                    dispatch(new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerSecretId))->onQueue('default');
                } else {
                    dispatch(new AddPaymentAfterDueDateJob($router, $customerProfile, $comment_payment, $customerSecretId, $customerUsername))->onQueue('default');
                }
            } else {
                return redirect()->back()->withErrors(['msg' => $customerSecretId . '! Canot find user "' . $customer->username . '" on server ' . $router->name . '.']);
            }
        }


        $billing->update([
            'pay_later' => Carbon::parse($request->pay_later),
            'teller_name' => auth()->user()->name,
            'status' => 'PL'
        ]);
        return redirect()->back()->with('success', 'Pay later successfully.');
    }


    public function transfer(Request $request, Billing $billing)
    {
        $banks = Bank::orderBy('name', 'ASC')->where('disabled', 'false')->get();
        $billingCustomerId = $billing->customer_id;
        $billings = Billing::where('customer_id', $billingCustomerId)->where('status', 'BL')->get();
        return view('billings.transfer', compact('billing', 'banks'), [
            'billings' => $billings,
        ]);
    }

    public function payment(Request $request, Billing $billing)
    {
        $paymentMethode = $request->payment_methode;
        if ($paymentMethode == 'transfer') {
            return redirect()->route('billings.transfer', $billing->slug);
        } else if ($paymentMethode == 'paylater') {

            return redirect()->route('billings.paylater', $billing->slug);
        } else {
            $payment = 'cash';
            $bankName = null;
            $accountNumber = null;
            $accountName = null;
            $depositBankName = null;
            $depositAccountName = null;
            $depositAccountNumber = null;
            $referenceId = null;
            if ($request->transfer_payment) {
                $this->validate(
                    $request,
                    [
                        'bank_transfer' => 'required',
                        'reference_id' => 'required|unique:receipts,reference_id,',
                        'deposit_bank_name' => 'required',
                        'deposit_account_name' => 'required',
                        'deposit_account_number' => 'required',
                    ],
                    [
                        'bank_transfer.required' => 'Bank transfer required!',
                        'reference_id.required' => 'Reference id required!',
                        'reference_id.unique' => 'Reference id already in use!',
                        'deposit_bank_name.required' => 'Deposit Bank Name required!',
                        'deposit_account_name.required' => 'Deposit Account Name required!',
                        'deposit_account_number.required' => 'Deposit Account Number required!',

                    ]
                );
                $payment = 'transfer';
                $bank = Bank::where('id', $request->bank_transfer)->first();
                $bankName = $bank->bank;
                $accountNumber = $bank->account_number;
                $accountName = $bank->name;
                $depositBankName = $request->deposit_bank_name;
                $depositAccountName = $request->deposit_account_name;
                $depositAccountNumber = $request->deposit_account_number;
                $referenceId = $request->reference_id;
            }

            $customer = Customer::where('id', $billing->customer_id)->first();
            $customerPaketId = $customer->paket_id;
            $customerSecretId = $customer->secret_id;
            $customerUsername = $customer->username;
            $activationDate = Carbon::parse($customer->activation_date)->format('d');
            $periodeName = BillingPeriode::latest('created_at')->value('name');

            $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');
            $mikrotikId = $customer->mikrotik_id;
            $customerProfile = Paket::where('id', $customerPaketId)->value('profile');
            $router = Mikrotik::where('id', $mikrotikId)->first();
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

            if ($router->disabled == 'false' && $autoIsolir != NULL && $autoIsolir->disabled == 'false') {
                try {
                    //dispatch(new TestConnectionJob($router))->onQueue('default');
                    $userSecretId = $this->secretService->getSecretById($router, $customerSecretId);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your server.']);
                }
                if ($autoIsolir->activation_date == 'false') {
                    $comment_payment = $autoIsolir->comment_payment;
                } else {
                    $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
                    if ($activationDate > $totalDaysInMonth) {
                        $activationDate = $totalDaysInMonth;
                    }
                    $comment_payment = $autoIsolir->comment_unpayment . '_' . $activationDate . '_' . $nextMonth;
                }


                if ($userSecretId != 'error') {
                    $secretProfiles = SecretResource::collection($userSecretId)->value('profile');
                    if ($secretProfiles == $customerProfile) {
                        dispatch(new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerSecretId))->onQueue('default');
                    } else {
                        dispatch(new AddPaymentAfterDueDateJob($router, $customerProfile, $comment_payment, $customerSecretId, $customerUsername))->onQueue('default');
                    }
                } else {
                    return redirect()->back()->withErrors(['msg' => $customerSecretId . '! Canot find user "' . $customer->username . '" on server ' . $router->name . '.']);
                }
            }
            $getLastReceipt = Receipt::latest('created_at')->value('number');
            if (!$getLastReceipt) {
                $numberReceipt = 1;
            } else {
                $receipt = explode('-', $getLastReceipt);
                $numberReceipt = (int)$receipt[1];
                $numberReceipt++;
            }
            $receipt = Receipt::create([
                'slug' => $billing->slug,
                'periode' => $billing->billing_periode->name,
                'number' => str('KWT-' . sprintf("%05d", $numberReceipt) . '-' . Carbon::now()->format('F') . '-' . Carbon::now()->format('Y')),
                'billing_number' => $billing->billing_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_address' => $customer->address,
                'paket_name' => $customer->paket->name,
                'paket_price' => $customer->paket->price * ((100 - $customer->discount) / 100),
                'created_name' => auth()->user()->name,
                'teller_name' => auth()->user()->name,
                'payment_time' => Carbon::now(),
                'payment_methode' => $payment,
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'account_number' => $accountNumber,
                'deposit_bank_name' => $depositBankName,
                'deposit_account_name' => $depositAccountName,
                'deposit_account_number' => $depositAccountNumber,
                'reference_id' => $referenceId,
                'tax' => $billing->tax,
            ]);
            $getReceipt = $receipt->fresh();
            $billing->update([
                'payment_time' => Carbon::now(),
                'teller_name' => auth()->user()->name,
                'status' => 'LS',
                'receipt_id' => $getReceipt->id,
            ]);

            return redirect()->back()->with('success', 'Payment ' . $billing->customer_name . ' successfully.');
        }
    }

    public function unpayment(Billing $billing)
    {
        $day = Carbon::now()->format('d');
        $customer = Customer::where('id', $billing->customer_id)->first();
        $secretId = $customer->secret_id;
        $username = $customer->username;
        $mikrotikId = $customer->mikrotik_id;

        $customerDueDate = $customer->activation_date;
        $date = Carbon::parse($customerDueDate)->format('d');

        $periodeName = BillingPeriode::latest('created_at')->value('name');
        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');

        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
        if ($autoIsolir != NULL) {
            $profileIsolir = $autoIsolir->profile_id;

            if ($autoIsolir->activation_date == 'false') {
                $comment_unpayment = $autoIsolir->comment_unpayment;
                $dueDate = $autoIsolir->due_date;
            } else {
                $comment_unpayment = $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
                $dueDate = $date;
            }
            $router = Mikrotik::where('id', $mikrotikId)->first();
            try {
                // dispatch(new TestConnectionJob($router))->onQueue('default');
                if ($day <= $dueDate) {
                    dispatch(new CancelPaymentBeforeDueDateJob($router, $comment_unpayment, $secretId))->onQueue('default');
                } else {
                    dispatch(new CancelPaymentAfterDueDateJob($router, $comment_unpayment, $profileIsolir, $secretId, $username))->onQueue('default');
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
            }
        }

        $receipt = Receipt::where('id', $billing->receipt_id);
        $billing->update([
            'teller_name' => auth()->user()->name,
            'status' => 'BL',
            'receipt_id' => null,
        ]);
        $receipt->delete();
        return redirect()->back()->with('success', 'Cancel payment successfully.');
    }

    public function deletePeriode(BillingPeriode $billingperiode)
    {
        return view('billings.delete', compact('billingperiode'));
    }

    public function destroyPeriode(BillingPeriode $billingperiode)
    {
        $billingperiode->delete();
        return redirect()->route('billings.create')->with('success', 'Periode billing deleted successfully.');
    }
}
