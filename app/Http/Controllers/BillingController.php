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
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Http\Requests\BillingStoreRequest;
use App\Jobs\CancelPaymentAfterDueDateJob;
use App\Jobs\CancelPaymentBeforeDueDateJob;
use App\Http\Resources\Pppoe\SecretResource;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticAfterDueDateJob;
use App\Jobs\CancelPaymentIpStaticBeforeDueDateJob;
use App\Jobs\CreateBillingIpStaticJob;
use App\Models\Bank;
use App\Models\Receipt;
use App\Models\Websystem;
use App\Models\Order;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
// use PDF;
use App\Services\WhatsappMessageService;
use Barryvdh\DomPDF\Facade\PDF;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendBillingToWhatsAppJob;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\UserAuth;

use Illuminate\Contracts\Database\Eloquent\Builder;
//use Barryvdh\DomPDF\PDF;

class BillingController extends Controller
{
    private WhatsappMessageService $messageService;
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    private IpStaticService $ipStaticService;
    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->ipStaticService = $ipStaticService;
        $this->middleware('permission:billing-list|billing-create|billing-edit|billing-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:billing-create', ['only' => ['create', 'store', 'create_one', 'store_one']]);
        $this->middleware('permission:billing-edit', ['only' => ['edit', 'update', 'payment', 'unpayment', 'transfer', 'paylater', 'post_paylater']]);
        $this->middleware('permission:billing-delete', ['only' => ['deletePeriode', 'destroyPeriode']]);
        $this->messageService = app(WhatsappMessageService::class);
    }

    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();

        $paginate = 25;
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $print = $request->cetak_pdf;

        // Parameter pencarian
        $searchName = $request->search_name;
        $searchMonth = $request->search_month;
        $searchYear = $request->search_year;
        $searchPeriode = ($searchMonth && $searchYear) ? "{$searchMonth} {$searchYear}" : null;
        $searchStatus = $request->search_status;
        $searchAddress = $request->search_address;
        $searchDuedate = $request->duedate;
        $customerSlug = $request->customer;

        // Konversi admin_name (ID -> nama user)
        $searchUserId = null;
        if ($request->filled('admin_name')) {
            $userById = User::find($request->admin_name);
            $searchUserId = $userById?->name;
        }

        // Fallback: jika tidak ada dari request, pakai user yang login jika punya izin
        if (empty($searchUserId) && $user && $user->can('view-customers-widget-related')) {
            $searchUserId = auth()->user()->name;
        }

        // Default periode jika tidak ada filter
        if (!$customerSlug && !$searchName && !$searchMonth && !$searchYear && !$searchStatus && !$searchAddress && !$searchDuedate) {
            $latestPeriode = BillingPeriode::latest('created_at')->value('name');
            $searchPeriode = $latestPeriode;
            $searchMonth = Carbon::parse($latestPeriode)->format('F');
            $searchYear = Carbon::parse($latestPeriode)->format('Y');
        }

        // Query utama
        $billingsQuery = Billing::query()
            ->when($searchName, fn($query) => $query->where('customer_name', 'like', "%{$searchName}%"))
            ->when($customerSlug, fn($query) => $query->whereHas('customer', fn($q) => $q->where('slug', $customerSlug)))
            ->when($searchAddress, function ($query) use ($searchAddress) {
                $query->whereHas('customer', function ($q) use ($searchAddress) {
                    $searchAddress === 'no-address'
                        ? $q->whereNull('address')
                        : $q->where('address', $searchAddress);
                });
            })
            ->when($searchPeriode, fn($query) => $query->whereHas('billing_periode', fn($q) => $q->where('name', 'like', "%{$searchPeriode}%")))
            ->when(!is_null($searchStatus), function ($query) use ($searchStatus, $searchDuedate) {
                Log::info('[Billing Index] search_status (raw): ' . $searchStatus);

                $statuses = explode('-', $searchStatus);
                Log::info('[Billing Index] Parsed statuses: ' . json_encode($statuses));

                // Ambil status yang dimulai dengan tanda "!" (artinya dikecualikan)
                $excludes = array_filter($statuses, fn($s) => str_starts_with($s, '!'));
                Log::info('[Billing Index] Raw excludes: ' . json_encode($excludes));

                if (count($excludes) > 0) {
                    $excludeValues = array_map(fn($s) => ltrim($s, '!'), $excludes);
                    Log::info('[Billing Index] → Masuk ke if(count($excludes) > 0)');
                    Log::info('[Billing Index] Cleaned excludes (tanpa !): ' . json_encode($excludeValues));

                    $query->whereNotIn('status', $excludeValues);

                    if ($searchDuedate) {
                        Log::info('[Billing Index] Filter by due_date (karena searchDuedate ada)');
                        $query->where('due_date', 'like', "%{$searchDuedate}%");
                    }
                } elseif (count($statuses) === 1) {
                    Log::info('[Billing Index] → Masuk ke elseif(count($statuses) === 1)');
                    Log::info('[Billing Index] Mencari status langsung: ' . $statuses[0]);

                    $query->where('status', $statuses[0]);

                    if ($searchDuedate) {
                        $field = $statuses[0] === 'LS' ? 'payment_time' : 'pay_later';
                        Log::info("[Billing Index] Filter by field: {$field}, value: {$searchDuedate}");
                        $query->where($field, 'like', "%{$searchDuedate}%");
                    }
                } else {
                    Log::info('[Billing Index] → Tidak masuk ke if/elseif, mungkin status kosong atau tidak sesuai format.');
                }
            })
            ->when($searchUserId, function ($query) use ($searchUserId) {
                $query->whereHas('customer', function ($q) use ($searchUserId) {
                    $q->whereColumn('billings.customer_id', 'customers.id')
                        ->whereNull('customers.deleted_at');

                    if ($searchUserId === 'Not Linked User' || $searchUserId === 0) {
                        $q->whereNull('customers.user_id');
                    } else {
                        $q->where('customers.user_id', function ($subQuery) use ($searchUserId) {
                            $subQuery->select('id')->from('users')->where('name', $searchUserId)->limit(1);
                        });
                    }
                });
            })
            ->orderBy(
                BillingPeriode::select('created_at')
                    ->whereColumn('billing_periodes.id', 'billings.billing_periode_id'),
                'DESC'
            )
            ->orderBy(
                Customer::select('address')->whereColumn('customers.id', 'billings.customer_id')
            )
            ->orderBy('customer_name', 'ASC');

        $totalPrice = $billingsQuery->sum('paket_price');
        $data = $billingsQuery->paginate($paginate);

        // Data dropdown dan filter
        $bank = Bank::orderBy('name')->where('disabled', 'false')->pluck('name', 'id');
        $address = Customer::whereNotNull('address')->pluck('address', 'address');
        $billingsPeriode = BillingPeriode::all();
        $months = BillingPeriode::pluck('month', 'month');
        $years = BillingPeriode::pluck('year', 'year');

        $userAdmin = Customer::orderBy('user_id')
            ->get()
            ->mapWithKeys(function ($customer) {
                if ($customer->user_id) {
                    $user = User::find($customer->user_id);
                    return [$user->id => $user->name];
                }
                return [0 => 'Not Linked User'];
            });

        $tellers = User::pluck('name', 'name');

        // Cetak PDF
        if ($print === 'true') {
            $dataPrint = $billingsQuery->get();
            $pdf = PDF::loadView('print.billing', [
                'dataPrint' => $dataPrint,
                'search' => compact('searchMonth', 'searchYear', 'searchStatus', 'searchName', 'searchAddress')
            ]);
            return $pdf->download("Tagihan_{$searchName}_{$searchAddress}_{$searchStatus}_{$months}_{$years}.pdf");
        }

        return view('billings.index', compact(
            'data',
            'totalPrice',
            'months',
            'years',
            'searchStatus',
            'searchName',
            'searchAddress',
            'bank',
            'address',
            'tellers',
            'userAdmin',
            'billingsPeriode'
        ))->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    public function create()
    {
        $traceId = (string) Str::uuid();
        $billingPeriodeName = BillingPeriode::latest('created_at')->value('name');
        $periodeNow = Carbon::now()->format('F Y');
        if ($periodeNow == $billingPeriodeName) {
            $billingPeriodes = CarbonPeriod::create(now()->addmonth(), '1 month', now()->addMonths(12));
        } else {
            $billingPeriodes = CarbonPeriod::create(now(), '1 month', now()->addMonths(11));
        }
        $data = BillingPeriode::paginate(12);

        $websystem = Websystem::first();
        $billingAutoGenerate = $websystem?->billing_auto_generate ?? false;
        $billingAutoDay = $websystem?->billing_auto_day;

        //dd($billingPeriodes, $data);

        return view('billings.create', compact('data', 'billingPeriodes', 'billingAutoGenerate', 'billingAutoDay'))
            ->with('i', (request()->input('page', 1) - 1) * 12);
    }

    public function create_one()
    {
        $traceId = (string) Str::uuid();
        $periodes = BillingPeriode::latest()->get();
        return view('billings.create_one', compact('periodes'));
    }
    /**
     * Store a newly created resource in storage.
     * Add Comment "Belum Lunas" on User Secret Mikrotik
     */
    public function store(Request $request)
    {
        $traceId = (string) Str::uuid();
        try {
            $periode = $request->name ?? Carbon::now()->format('F Y');
            $billingPeriode = $this->createBillingPeriode($periode);
            $totalBillingCreated = $this->processRouters($billingPeriode);
            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalBillingCreated],
                200,
                "Billing have been successfully created.",
                $traceId
            );
            $message = "{$totalBillingCreated} Billing for {$periode} have been successfully created.";
            return redirect()->route('billings.index')
                ->with('success', $message);
        } catch (QueryException $e) {
            $message = "Database error: {$e->getMessage()}";
            LogHelper::logInfo(null, null, 500, $message, $traceId);
            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan Pada Proses Store Data.');
        } catch (Exception $e) {
            $message = "Something went wrong: {$e->getMessage()}";
            LogHelper::logInfo(null, null, 500, $message, $traceId);
            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    public function store_one(BillingStoreRequest $request)
    {
        $traceId = (string) Str::uuid();
        try {
            $billingPeriode = BillingPeriode::where('id', $request->name)->first();
            if (!$billingPeriode) {
                throw new Exception('Billing period not found');
            }
            $totalBillingCreated = $this->processRouters($billingPeriode);
            LogHelper::logInfo(
                null,
                ['total_billing_created' => $totalBillingCreated],
                200,
                "Billing have been successfully created.",
                $traceId
            );
            $message = "{$totalBillingCreated} Billing for {$billingPeriode->name} have been successfully created.";
            return redirect()->route('billings.index')
                ->with('success', $message);
        } catch (QueryException $e) {
            $message = "Database error: {$e->getMessage()}";
            LogHelper::logInfo(null, null, 500, $message, $traceId);
            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan Pada Proses Store Data.');
        } catch (Exception $e) {
            $message = "Something went wrong: {$e->getMessage()}";
            LogHelper::logInfo(null, null, 500, $message, $traceId);
            return redirect()->route('billings.index')->with('error', 'Terjadi Kesalahan, Silakan Periksa Kembali Inputan Atau Hubungi Tim IT.');
        }
    }

    private function createBillingPeriode($periode)
    {
        $periodeExplode = explode(' ', $periode);
        return BillingPeriode::create([
            'name' => $periode,
            'month' => $periodeExplode[0],
            'year' => $periodeExplode[1],
        ]);
    }

    private function processRouters($billingPeriode)
    {
        $totalBillingCreated = 0;
        $websystem = Websystem::where('slug', 'websystem')->first();
        $routers = Mikrotik::where('disabled', 'false')->get();

        foreach ($routers as $router) {
            $customers = Customer::where('disabled', 'false')
                ->where('activation', 'true')
                ->where('mikrotik_id', $router->id)
                ->get();

            $billing = Billing::select('customer_id')
                ->where('billing_periode_id', $billingPeriode->id)
                ->get();

            $customersUnbilling = $customers->diff(
                Customer::whereIn('id', $billing)->get()
            );

            $totalBillingCreated += $this->createBillings($customersUnbilling, $billingPeriode, $websystem, $router);
        }

        return $totalBillingCreated;
    }

    private function createBillings($customersUnbilling, $billingPeriode, $websystem, $router)
    {
        $totalBillingCreated = 0;
        $autoIsolir = AutoIsolir::where('mikrotik_id', $router->id)->first();

        foreach ($customersUnbilling as $customer) {
            $customerPriceAfterDiscount = $this->calculateCustomerPrice($customer, $billingPeriode->name);
            $customerTax = $websystem->enable_tax == 'true'
                ? ceil($customerPriceAfterDiscount * ($websystem->tax_rate / 100))
                : 0;

            if ($autoIsolir && $autoIsolir->disabled == 'false') {
                try {
                    $comment = $customerPriceAfterDiscount > 0
                        ? $autoIsolir->comment_unpayment
                        : $autoIsolir->comment_payment;

                    $this->dispatchBillingJob($customer, $router, $comment);
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch billing job.', ['error' => $e->getMessage()]);
                    $billingPeriode->delete();
                    return 0;
                }
            }

            Billing::create([
                'billing_periode_id' => $billingPeriode->id,
                'slug' => str($customer->id . '-' . Str::random(4))->slug(),
                'billing_number' => str($customer->id . '-' . Str::random(4) . '-' . Carbon::parse($billingPeriode->name)->format('m') . '-' . Carbon::parse($billingPeriode->name)->format('Y'))->slug(),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'paket_name' => $customer->paket->name,
                'paket_price' => $customerPriceAfterDiscount,
                'tax' => $customerTax,
                'created_name' => auth()->user()->name,
                'status' => $customerPriceAfterDiscount > 0 ? 'BL' : 'LS',
                'due_date' => Carbon::createFromFormat('d F Y', "15 " . Carbon::parse($billingPeriode->name)->format('F Y'))
                    ->endOfDay(),
            ]);

            $totalBillingCreated++;
        }

        return $totalBillingCreated;
    }

    private function calculateCustomerPrice($customer, $periodeName)
    {
        $paket_price = $customer->paket->price;
        $discount = optional($customer->member)->discount ?? 0;

        if ($customer->billing_option == 2 && $customer->billing_period === $periodeName) {
            return $paket_price - $discount;
        } elseif ($customer->billing_option == 1) {
            return $paket_price - $discount;
        }

        return $paket_price;
    }

    private function dispatchBillingJob($customer, $router, $comment)
    {
        if ($customer->internet_service == 'ppp') {
            dispatch(new CreateBillingJob($router, $comment, $customer->secret_id))->onQueue('default');
        } else if ($customer->internet_service == 'ip_static') {
            dispatch(new CreateBillingIpStaticJob($router, $customer->simpleque_id, $comment))->onQueue('default');
        }
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
        // dd($billing, $totalPrice, $websystem);
        return view('billings.show', compact('billing', 'totalPrice', 'websystem'), [
            'billings' => $billings,
        ]);
    }

    public function paylater(Request $request, Billing $billing)
    {
        return view('billings.paylater', compact('billing'));
    }

    public function post_paylater(Request $request, Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $request->validate([
            'pay_later' => ['required', 'date', 'after_or_equal:' . now()->format('Y-m-d')],
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

            if ($autoIsolir->activation_date == 'false') {
                $comment_payment = $autoIsolir->comment_payment;
            } else {
                $comment_payment = $autoIsolir->comment_unpayment . '_' . $paylaterDate . '_' . $paylaterMonth;
            }
            if ($customer->internet_service == 'ppp') {
                try {
                    //dispatch(new TestConnectionJob($router))->onQueue('default');
                    $userSecretId = $this->secretService->getSecretById($router, $customerSecretId);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
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
            } else if ($customer->internet_service == 'ip_static') {
                $ip_address = $customer->ip_static;
                $simpleque_id = $customer->simpleque_id;
                $address_list_isolir = $autoIsolir->address_list_isolir;
                $comment = $customer->username . '_' . $comment_payment;
                try {
                    //Get Address List Name
                    $address_list_customer = $this->ipStaticService->getAddressListByIpAddress($router, $ip_address);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Cant connect to Server ' . $router->name . '.']);
                }
                // return dd($address_list);
                if (isset($address_list_customer[0]['list'])) {
                    if ($address_list_customer[0]['list'] == $address_list_isolir) {
                        // $address_list_id = $address_list[0]['.id'];
                        dispatch(new AddPaymentIpStaticAfterDueDateJob($router, $ip_address, $simpleque_id, $address_list_isolir, $comment_payment, $comment))->onQueue('default');
                    } else {
                        dispatch(new AddPaymentBeforeDueDateJob($router, $simpleque_id, $comment_payment))->onQueue('default');
                    }
                } else {
                    dispatch(new AddPaymentIpStaticAfterDueDateJob($router, $ip_address, $simpleque_id, $address_list_isolir, $comment_payment, $comment))->onQueue('default');
                }
            }
        }

        $billing->update([
            'pay_later' => Carbon::parse($request->pay_later),
            'teller_name' => auth()->user()->name,
            'status' => 'PL'
        ]);

        try {
            // Cek jika WhatsappGateway ditemukan dan tidak dalam status disabled
            $gatewayStatus = WhatsappGateway::where('slug', 'gateway')->value('disabled');

            if ($gatewayStatus === null) {
                throw new \Exception("Whatsapp Gateway with slug 'gateway' not found.");
            }

            // Pastikan gateway tidak dalam keadaan disabled dan customer memiliki contact
            if ($gatewayStatus == false && $customer->contact) {
                // Panggil method payment_paket
                $this->messageService->payment_paket($billing, null, 'paylater');
            } else {
                if ($gatewayStatus) {
                    LogHelper::logWarning(null, [], 202, "Whatsapp Gateway is disabled", $traceId);
                } elseif (!$customer->contact) {
                    LogHelper::logWarning(null, [], "Customer contact not found", $traceId);
                }
            }
        } catch (\Exception $e) {
            // Tangani error atau exception
            LogHelper::logError(null, ["error" => $e->getMessage()], "Error occured while processing payment package", $traceId);
            // Opsional: Menampilkan pesan error ke pengguna
            return response()->json(['error' => 'There was an issue processing the payment package. Please try again later.']);
        }


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
        $traceId = (string) Str::uuid();
        $paymentMethode = $request->payment_methode;
        if ($paymentMethode == 'transfer') {
            return redirect()->route('billings.transfer', $billing->slug);
        } else if ($paymentMethode == 'paylater') {
            return redirect()->route('billings.paylater', $billing->slug);
        } else if ($paymentMethode == 'not-active-periode') {
            $billing->update([
                'teller_name' => auth()->user()->name,
                'status' => 'NP',
            ]);
            return redirect()->back()->with('success', 'The billing status for ' . $billing->customer_name . ' has been marked as No Usage for this period.');
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
                        //  'reference_id' => 'required|unique:receipts,reference_id,',
                        //'deposit_bank_name' => 'required',
                        //'deposit_account_name' => 'required',
                        //'deposit_account_number' => 'required',
                    ],
                    [
                        'bank_transfer.required' => 'Bank transfer required!',
                        'reference_id.required' => 'Reference id required!',
                        'reference_id.unique' => 'Reference id already in use!',
                        //'deposit_bank_name.required' => 'Deposit Bank Name required!',
                        //'deposit_account_name.required' => 'Deposit Account Name required!',
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

            // $customer = Customer::where('id', $billing->customer_id)->first();
            $customer = $billing->customer;
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
            if ($autoIsolir->activation_date == 'false') {
                $comment_payment = $autoIsolir->comment_payment;
            } else {
                $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
                if ($activationDate > $totalDaysInMonth) {
                    $activationDate = $totalDaysInMonth;
                }
                $comment_payment = $autoIsolir->comment_unpayment . '_' . $activationDate . '_' . $nextMonth;
            }
            if ($router->disabled == 'false' && $autoIsolir != NULL && $autoIsolir->disabled == 'false') {
                if ($customer->internet_service == 'ppp') {
                    try {
                        //dispatch(new TestConnectionJob($router))->onQueue('default');
                        $userSecretId = $this->secretService->getSecretById($router, $customerSecretId);
                    } catch (\Exception $e) {
                        return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your server.']);
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
                } else if ($customer->internet_service == 'ip_static') {
                    $ip_address = $customer->ip_static;
                    $simpleque_id = $customer->simpleque_id;
                    $address_list_isolir = $autoIsolir->address_list_isolir;
                    $comment = $customer->username . '_' . $comment_payment;
                    try {
                        //Get Address List Name
                        $address_list_customer = $this->ipStaticService->getAddressListByIpAddress($router, $ip_address);
                    } catch (\Exception $e) {
                        return redirect()->back()->withErrors(['msg' => 'Cant connect to Server ' . $router->name . '.']);
                    }

                    if (isset($address_list_customer[0]['list'])) {
                        if ($address_list_customer[0]['list'] == $address_list_isolir) {
                            // $address_list_id = $address_list[0]['.id'];
                            dispatch(new AddPaymentIpStaticAfterDueDateJob($router, $ip_address, $simpleque_id, $address_list_isolir, $comment_payment, $comment))->onQueue('default');
                        } else {
                            dispatch(new AddPaymentBeforeDueDateJob($router, $simpleque_id, $comment_payment))->onQueue('default');
                        }
                    } else {
                        dispatch(new AddPaymentIpStaticAfterDueDateJob($router, $ip_address, $simpleque_id, $address_list_isolir, $comment_payment, $comment))->onQueue('default');
                    }
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

            $discount = 0;
            $price_after_discount = $customer->paket->price;
            $member = 'Non Member / Tidak Ada';

            if ($customer->billing_option == 1) {
                $discount = optional($customer->member)->discount ?? 0;
                $price_after_discount = $customer->paket->price - $discount;
                $member = optional($customer->member)->type;
            } elseif (
                $customer->billing_option == 2 &&
                $billing->billing_periode->name === $customer->billing_period
            ) {
                $discount = optional($customer->member)->discount ?? 0;
                $price_after_discount = $customer->paket->price - $discount;
                $member = optional($customer->member)->type;

                $customer->update([
                    'billing_option' => '0',
                    'billing_period' => null,
                    'member_id'      => null,
                    'discount'       => '0',
                ]);
            } else {
                $customer->update([
                    'billing_option' => '0',
                    'billing_period' => null,
                    'member_id'      => null,
                    'discount'       => '0',
                ]);
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
                // 'paket_price' => $customer->paket->price * ((100 - $customer->discount) / 100),
                'paket_price' => $customer->paket->price,
                'discount' => $discount,
                'price_after_discount' => $price_after_discount,
                'member_type' => $member,
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
            if (WhatsappGateway::where('slug', 'gateway')->value('disabled') == false && $customer->contact) {
                $this->messageService->payment_paket($billing, $getReceipt, $payment);
            }
            return redirect()->back()->with('success', 'Payment ' . $billing->customer_name . ' successfully.');
        }
    }

    public function unpayment(Billing $billing)
    {
        $traceId = (string) Str::uuid();
        $day = Carbon::now()->format('d');
        $customer = Customer::where('id', $billing->customer_id)->first();
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
            if ($customer->internet_service == 'ppp') {
                $secretId = $customer->secret_id;
                $username = $customer->username;
                try {
                    // dispatch(new TestConnectionJob($router))->onQueue('default');
                    if ($day <= $dueDate) {
                        dispatch(new CancelPaymentBeforeDueDateJob($router, $comment_unpayment, $secretId))->onQueue('default');
                    } else {
                        dispatch(new CancelPaymentAfterDueDateJob($router, $comment_unpayment, $profileIsolir, $secretId, $username))->onQueue('default');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Cant connect to server ' . $router->name . '.']);
                }
            } else if ($customer->internet_service == 'ip_static') {
                $simpleque_id = $customer->simpleque_id;
                $comment = $customer->username . '_' . $comment_unpayment;
                try {
                    if ($day <= $dueDate) {
                        // return dd('tes');
                        dispatch(new CancelPaymentIpStaticBeforeDueDateJob($router, $simpleque_id, $comment_unpayment))->onQueue('default');
                    } else {
                        $ip_address = $customer->ip_static;
                        $address_list_isolir = $autoIsolir->address_list_isolir;
                        $comment = $customer->username . '_' . $comment_unpayment;
                        dispatch(new CancelPaymentIpStaticAfterDueDateJob($router, $address_list_isolir, $ip_address, $simpleque_id, $comment))->onQueue('default');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Cant connect to Server ' . $router->name . '.']);
                }
            }
        }
        $receipt = Receipt::where('id', $billing->receipt_id);

        // Simpan receipt_id sebelum di-null
        $originalReceipt = $receipt->first();

        $billing->update([
            'teller_name' => auth()->user()->name,
            'status' => 'BL',
            'receipt_id' => null, // Diubah jadi null
        ]);

        $receipt->delete();

        // 1. Gunakan first() jika hanya butuh 1 order, atau get() untuk multiple
        $orders = Order::where('billing_id', $billing->id)->get();

        // 2. Check jika collection tidak kosong
        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                $order->update([
                    'payment_status' => Order::STATUS_CANCELED
                ]);
            }

            // Optional: Log jumlah order yang diupdate
            LogHelper::logInfo(null, ['order_count' => $orders->count(), 'billing_id' => $billing->id], 200, "Canceled orders", $traceId);
        } else {
            // Handle case ketika tidak ada order
            LogHelper::logWarning(null, ['billing_id' => $billing->id], 404, "No orders found", $traceId);

            // Jika perlu return response error:
            // return response()->json(['error' => 'No orders found'], 404);
        }

        if (WhatsappGateway::where('slug', 'gateway')->value('disabled') == false && $customer->contact) {
            $this->messageService->payment_paket($billing, $originalReceipt, 'unpayment');
        }

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

    public function billing_auto_generate(Request $request)
    {
        $request->validate([
            'billing_auto_day' => 'nullable|integer|min:1|max:28',
            'billing_auto_generate' => 'nullable|boolean',
        ]);

        $websystem = Websystem::first();

        // Jika input tidak ada, dianggap 0 (false)
        $billingAutoGenerate = $request->input('billing_auto_generate', 0);

        $websystem->billing_auto_generate = (bool) $billingAutoGenerate;
        $websystem->billing_auto_day = $request->input('billing_auto_day');
        $websystem->save();

        return redirect()->back()->with('success', 'Automatic billing configuration has been set');
    }
}
