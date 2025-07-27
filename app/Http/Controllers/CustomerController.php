<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\PppType;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\MerkRouter;
use App\Models\TypeRouter;
use App\Models\CustomerOnt;
use App\Models\User;
use App\Models\Paket\PppProfile;
use App\Models\Billing\Billing;
use App\Models\DeletedCustomer;
use App\Models\Billing\BillingPeriode;
use App\Models\Member;
use App\Models\Websystem;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Models\Receipt;

use App\Jobs\EnablePppSecretJob;
use App\Jobs\DisablePppSecretJob;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
use App\Jobs\DeleteIpStaticJob;
use App\Jobs\WhatsApp\ProcessWhatsappMessageJob;
use App\Jobs\Telegram\ProcessTelegramMessageJob;

use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use RouterOS\Exceptions\QueryException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Requests\CustomerPppUpdateRequest;
use RouterOS\Exceptions\BadCredentialsException;
use App\Http\Requests\CustomerRouterUpdateRequest;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Resources\InterfaceResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\LogHelper;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Helpers\UserAuth;

class CustomerController extends Controller
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    private QueueService $queueService;
    private IpStaticService $ipStaticService;

    public function __construct(
        SecretService $secretService,
        TestConnectionService $testConnectionService,
        QueueService $queueService,
        IpStaticService $ipStaticService,
    ) {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->queueService = $queueService;
        $this->ipStaticService = $ipStaticService;
        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete|secret-disable|secret-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store', 'activation']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update', 'activation', 'editRouter', 'updateRouter']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy', 'delete']]);
        $this->middleware('permission:secret-disable', ['only' => ['disable', 'enable']]);
        $this->middleware('permission:secret-edit', ['only' => ['editPpp', 'updatePpp', 'activation_all_customer']]);
    }
    /**
     * Display a listing of the resource.
     */
    private function getUserAuthData()
    {
        return UserAuth::getUserDetails();
    }

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 25); // Default 25
        $auth = $this->getUserAuthData();
        $user = $auth['user'];
        $searchParams = $request->only([
            'search_name',
            'search_address',
            'server_search',
            'search_paket',
            'status',
            'search_admin'
        ]);

        // Ambil semua router yang aktif
        $routersEnable = Mikrotik::where('disabled', 'false')
            ->when($searchParams['server_search'] ?? null, fn($query, $server) => $query->where('id', $server))
            ->get();

        if (in_array($searchParams['status'] ?? null, ['isolir', 'offline', 'online'])) {
            return $this->filterCustomersByStatus($request, $routersEnable, $searchParams, $perPage);
        }

        if (empty($searchParams['search_admin']) && $user && $user->can('view-customers-widget-related')) {
            $searchParams['search_admin'] = $user->id;
        }

        // DB::enableQueryLog();

        // Query pelanggan
        $customers = Customer::with(['user'])
            ->when($searchParams['search_name'] ?? null, fn($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($searchParams['search_address'] ?? null, function ($query, $address) {
                if ($address === 'NULL') {
                    $query->whereNull('address');
                } else {
                    $query->where('address', $address);
                }
            })
            ->when($searchParams['search_admin'] ?? null, function ($query, $admin) {
                if ($admin === 'NULL') {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $admin);
                }
            })
            ->when($searchParams['status'] ?? null, fn($query, $status) => $this->applyStatusFilter($query, $status))
            ->when($searchParams['server_search'] ?? null, fn($query, $server) => $query->where('mikrotik_id', $server))
            ->when($searchParams['search_paket'] ?? null, fn($query, $paket) => $query->where('paket_id', $paket))
            // ->orderBy('address')->orderBy('name');
            ->orderBy('name');

        $data = $customers->paginate($perPage)->appends($request->query());

        // $queries = DB::getQueryLog();
        // LogHelper::logInfo(
        //     null,
        //     [
        //         'queries' => $queries,
        //         'params' => $searchParams,
        //     ],
        //     200,
        //     'Debug customer query'
        // );

        // Hitung total pemasukan
        $totalIncoming = $data->sum(fn($customer) => $customer->paket->price * ((100 - $customer->discount) / 100));

        return view('customers.index', [
            'data' => $data,
            'totalIncoming' => $totalIncoming,
            'address' => Customer::select('address')->distinct()->pluck('address', 'address'),
            'allServers' => Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id'),
            'allAdmin' => User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id'),
            'periodeName' => BillingPeriode::latest('created_at')->value('name'),
            'perPage' => $perPage
        ])->with('i', (request()->input('page', 1) - 1) * $perPage);
    }

    private function filterCustomersByStatus($request, $routersEnable, $searchParams, $paginate)
    {
        $status = $searchParams['status'] ?? null;
        $searchAddress = $searchParams['address'] ?? null;
        $searchServer = $searchParams['server'] ?? null;
        $searchPaket = $searchParams['paket'] ?? null;

        $startTime = microtime(true);
        $maxExecutionTime = ini_get('max_execution_time');
        $safeLimit = $maxExecutionTime > 0 ? $maxExecutionTime - 2 : 28;

        $dataCustomers = [];

        if (count($routersEnable) == 0) {
            return redirect()->route('customers.index')
                ->withErrors(['msg' => 'All server disable, please check your server setting.', 'Or you don\'t have server data.']);
        }

        foreach ($routersEnable as $router) {
            if ((microtime(true) - $startTime) >= $safeLimit) {
                Log::warning('Execution time approaching limit, returning partial data.');
                break;
            }

            try {
                $this->testConnectionService->checkOnline($router);
            } catch (\Exception $e) {
                return redirect()->route('customers.index')
                    ->withErrors(['msg' => 'Server ' . $router->name . ' Offline.', 'Please enable first or check your configuration!']);
            }

            $secretsStatus = [];

            if ($status === 'isolir') {
                $profileIsolir = AutoIsolir::where('mikrotik_id', $router->id)->value('profile_id');
                if ($profileIsolir) {
                    $getSecretStatus = $this->secretService->getSecretByProfile($router, $profileIsolir);
                    $collectionSecretStatus = SecretResource::collection($getSecretStatus)->where('disabled', 'false');
                    $secretsStatus = $collectionSecretStatus->pluck('name')->toArray();
                } else {
                    return redirect()->route('customers.index')
                        ->withErrors(['msg' => 'Please config auto isolir on Server ' . $router->name . ' first.']);
                }
            } else {
                $getAllSecretActive = $this->secretService->getPppActive($router);
                $collectionSecretActive = SecretResource::collection($getAllSecretActive);
                $secretActive = $collectionSecretActive->pluck('name')->toArray();

                if ($status === 'offline') {
                    $usernameCustomers = Customer::where('mikrotik_id', $router->id)
                        ->where('disabled', 'false')
                        ->pluck('username')
                        ->toArray();

                    $secretsStatus = collect($usernameCustomers)->diff($secretActive)->values()->all();
                } elseif ($status === 'online') {
                    $secretsStatus = $secretActive;
                }
            }

            foreach ($secretsStatus as $secretName) {
                $customer = Customer::where('username', $secretName)->first();
                if ($customer) {
                    $dataCustomers[] = $customer;
                }
            }
        }

        $filteredCustomers = collect($dataCustomers)
            ->filter(function ($customer) use ($searchAddress) {
                if ($searchAddress === 'NULL') {
                    return $customer->address === null;
                } elseif ($searchAddress !== null) {
                    return $customer->address === $searchAddress;
                }
                return true;
            })
            ->filter(function ($customer) use ($searchServer) {
                return $searchServer ? $customer->mikrotik_id == $searchServer : true;
            })
            ->filter(function ($customer) use ($searchPaket) {
                return $searchPaket ? $customer->paket_id == $searchPaket : true;
            })
            ->where('activation', 'true')
            ->sortBy('name')
            ->values();

        // Paginasi manual
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentPageResults = $filteredCustomers->slice(($currentPage - 1) * $paginate, $paginate)->all();
        $paginatedSearchResults = new LengthAwarePaginator($currentPageResults, $filteredCustomers->count(), $paginate, $currentPage, [
            'path' => $request->url(),
            'query' => $request->except('page'),
        ]);

        return view('customers.offline', [
            'data' => $paginatedSearchResults,
            'status' => $status,
            'dataCustomers' => $filteredCustomers,
        ])->with('i', ($currentPage - 1) * $paginate);
    }

    private function applyStatusFilter($query, $status)
    {
        $query = match ($status) {
            'active' => $query
                ->where('activation', 'true')
                ->where('disabled', 'false'),

            'inactive' => $query
                ->where('activation', 'false')
                ->where('disabled', 'true'),

            default => $query,
        };

        return $query;
    }

    public function export()
    {
        $timestamp = now()->format('Ymd_His'); // Format: YYYYMMDD_HHMMSS
        $fileName = "customers_{$timestamp}.xlsx"; // Contoh: customers_20250325_143530.xlsx

        return Excel::download(new CustomersExport, $fileName);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        $pppservices = PppType::orderBy('name', 'ASC')->pluck('name', 'id');
        // $interfaces = $this->ipStaticService->getInterfaces($mikrotiks);
        $pppprofiles = DB::table('pakets')
            ->join('ppp_profiles', 'pakets.profile', '=', 'ppp_profiles.profile_name')
            ->where('ppp_profiles.disabled', 'false')
            ->orderBy('pakets.name')
            ->select('pakets.id', 'pakets.name', 'pakets.price')
            ->get()
            ->mapWithKeys(function ($paket) {
                $label = $paket->name . ' - Rp ' . number_format($paket->price, 0, ',', '.');
                return [$paket->id => $label];
            });
        $users = User::all();
        $members = Member::all();
        return view('customers.create', [
            'mikrotiks' => $mikrotiks,
            'pppservices' => $pppservices,
            'users' => $users,
            'members' => $members,
            'pppprofiles' => $pppprofiles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request): RedirectResponse
    {
        // dd($request);
        $discount = $request->discount ?? 0;
        $periode = BillingPeriode::latest('created_at')->first();

        if (!$periode) {
            $periode = BillingPeriode::create(['name' => Carbon::now()->format('F Y')]);
        }

        $periodeName = $periode->name;
        $periodeId = $periode->id;
        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');
        $billingPeriode = NULL;

        $firstName = explode(' ', $request->name)[0];
        $creds = $this->generateCredential($firstName);
        $username = $creds['username'];
        $billing_option = $request->billing_option ? $request->billing_option : '0';
        if ($billing_option !== '0' || $billing_option !== '1') {
            $billingPeriode = $periodeName;
        }

        // $profile = pppProfile::find($request->paket_id);
        // if (!$profile) {
        //     return back()->withError('Profile PPP tidak ditemukan');
        // }

        $paket = Paket::where('id', $request->paket_id)->first();
        if (!$paket) {
            return back()->withError('Paket untuk profile ini tidak ditemukan');
        }

        $paketId = $paket->id;

        // dd($profile, $paket, $paketId);

        $customerData = [
            'name' => $request->name,
            'email' => $request->email,
            'slug' => str($firstName . '-' . Str::random(6))->slug(),
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'contact' => $this->formatContact($request->contact),
            'internet_service' => $request->internet_service,
            'primary_server' => $request->mikrotik_id,
            'discount' => $discount,
            'mikrotik_id' => $request->mikrotik_id,
            'paket_id' => $paketId,
            'username' => $username,
            'user_id' => $request->user_id,
            'member_id' => $request->member_id,
            'billing_option' => $billing_option,
            'billing_period' => $billingPeriode
        ];

        // dd($customerData);

        if ($request->internet_service === 'ppp') {
            $customerData['password_ppp'] = $creds['password'];
            $customerData['ppp_type_id'] = $request->ppp_type_id;
        } elseif ($request->internet_service === 'ip_static') {
            $customerData['simpleque_name'] = $username;
            $customerData['ip_static'] = $request->ip_static;
            $customerData['mac_address'] = $request->mac_address;
            $customerData['interface'] = $request->interface;
        }

        $customer = Customer::create($customerData);
        $customer = $customer->fresh();

        $this->generateCustomerId($customer, $request->mikrotik_id);
        CustomerOnt::create(['customer_id' => $customer->id]);

        $this->createBilling($customer, $periodeId, $periodeName);

        $routerName = Mikrotik::find($request->mikrotik_id)->name ?? 'router';
        return redirect()->route('customers.index')
            ->with('success', 'Customer added successfully. Please activate first to create user ppp on server ' . $routerName . '!');
    }

    private function generateCredential(string $name): array
    {
        $today = Carbon::today();

        // Hitung berapa banyak customer yang sudah terdaftar hari ini
        $countToday = Customer::whereDate('created_at', $today)->count();

        // Tambah 1 untuk yang sekarang
        $activationNumber = str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);

        // Format tanggal seperti 20250529
        $datePrefix = $today->format('Ymd') . $activationNumber;

        // Slug nama
        $nameSlug = strtolower(Str::slug($name));

        return [
            'username' => $datePrefix . '@' . $nameSlug,
            'password' => $datePrefix,
        ];
    }

    private function generateCustomerId(Customer $customer, int $mikrotikId): void
    {
        $websystem = Websystem::where('slug', 'websystem')->first();
        $longCode = sprintf('%%0%dd', $websystem->long_customer_code);
        $customerId = sprintf(
            '%s%02d-%s',
            $websystem->customer_code,
            $mikrotikId,
            sprintf($longCode, $customer->id)
        );

        $customer->update(['customer_id' => $customerId]);
    }

    private function createBilling(Customer $customer, int $periodeId, string $periodeName): void
    {
        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');

        $paket_price = $customer->paket->price;

        // Pastikan $customer->member tidak null sebelum ambil discount
        $discount = optional($customer->member)->discount ?? 0;

        // Set default biar nggak undefined
        $total_price = $paket_price;

        if ($customer->billing_option == 2 && $customer->billing_period === $periodeName) {
            $total_price -= $discount;
        } elseif ($customer->billing_option == 1) {
            $total_price -= $discount;
        }

        Billing::create([
            'billing_periode_id' => $periodeId,
            'slug' => str($customer->id . '-' . Str::random(4))->slug(),
            'billing_number' => str($customer->id . '-' . Str::random(4) . '-' . $month . '-' . $year)->slug(),
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'paket_name' => $customer->paket->name,
            'paket_price' => $total_price,
            'created_name' => auth()->user()->name,
            'status' => 'BL',
        ]);
    }

    public function activation(Customer $customer): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $internet_service = $customer->internet_service;
        $activationDate = Carbon::now();
        $mikrotikId = $customer->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotikId)->first();
        $profile = Paket::where('id', $customer->paket_id)->value('profile');
        if ($router->disabled == 'true') {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first.']);
        }
        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
        if (isset($autoIsolir)) {
            if ($autoIsolir->activation_date == 'true') {
                $totalDaysInNextMonth = Carbon::now()->addMonth()->daysInMonth;
                $day = Carbon::now()->format('d');
                if ($day > $totalDaysInNextMonth) {
                    $day = $totalDaysInNextMonth;
                }
                $nextMonth = Carbon::now()->addMonth()->format('m_Y');
                $comment_payment = $autoIsolir->comment_unpayment . '_' . $day . '_' . $nextMonth;
            } else {
                $comment_payment = $autoIsolir->comment_payment;
            }
        } else {
            $comment_payment = 'create_from_customer_management';
        }
        $username = $customer->username;
        if ($internet_service == 'ppp') {
            $password = $customer->password_ppp;
            $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');
            try {
                $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $comment_payment);
                if (isset($createSecret['after']['message'])) {
                    return redirect()->back()->withErrors(['msg' => 'Cant create user ppp on server ' . $router->name . '.']);
                } else {
                    $secretId = $createSecret['after']['ret'];
                    $customer->update([
                        'activation_date' => $activationDate,
                        'activation' => 'true',
                        'disabled' => 'false',
                        'secret_id' => $secretId,
                    ]);
                    $this->secretService->enableSecret($router, $username);
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }
        } else if ($internet_service == 'ip_static') {
            $comment = $customer->username ?? 'Create from Customer Management';
            $profile_on_mikrotik = $this->secretService->getPppProfileByName($router, $profile);
            $profile = $profile_on_mikrotik[0];
            $valueLimitasi = $profile['rate-limit'] ?? '';
            if (strlen($valueLimitasi) == 0) {
                return redirect()->back()->withErrors(['msg' => 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!']);
            }
            $parts = explode(' ', $valueLimitasi);
            $ip_static = $customer->ip_static;
            $mac_address = $customer->mac_address;
            $interface = $customer->interface;
            $parent = $profile['parent-queue'] ?? 'none';
            $priority = $parts[4] . '/' . $parts[4];
            $queuetype = $profile['queue-type'] ?? '';
            $limitat = $parts[5];
            $maxlimit = $parts[0];
            try {
                $createLimitasi = $this->queueService->createSimpleQueue($router, $username, $ip_static, $parent, $priority, $queuetype, $limitat, $maxlimit, $comment_payment);
                if (isset($createLimitasi['after']['message'])) {
                    return redirect()->back()->withErrors(['msg' => 'Cant create limitasi on server ' . $router->name . ', ' . $createLimitasi['after']['message'] . '.']);
                } else {
                    $simpleque_id = $createLimitasi['after']['ret'];
                }
                $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $mac_address, $interface, $comment);
                // $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $interface, $comment);

                if (isset($createArp['after']['message'])) {
                    //if ($simpleque_id) {
                    $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleque_id);
                    //  }

                    return redirect()->back()->withErrors(['msg' => 'Cant create arp to ip ' . $ip_static . ' on server ' . $router->name . ', ' . $createArp['after']['message'] . ' .']);
                } else {
                    $arp_id = $createArp['after']['ret'];
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }

            $customer->update([
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
                //'address_list_id' => null,
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id,
            ]);
        }
        $billing = Billing::where('customer_id', $customer->id)->first();
        $getLastReceipt = Receipt::latest('created_at')->value('number');
        if (!$getLastReceipt) {
            $numberReceipt = 1;
        } else {
            $receipt = explode('-', $getLastReceipt);
            $numberReceipt = (int)$receipt[1];
            $numberReceipt++;
        }
        $receipt = Receipt::create([
            'slug' => str('inv-' . $customer->id . '-' . Str::random(4))->slug(),
            'periode' => $billing->billing_periode->name,
            'number' => str(sprintf("%05d", $numberReceipt) . '-KWT-' . Carbon::now()->format('F') . '-' . Carbon::now()->format('Y')),
            'billing_number' => $billing->billing_number,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_address' => $customer->address,
            'paket_name' => $customer->paket->name,
            'paket_price' => $customer->paket->price * ((100 - $customer->discount) / 100),
            'payment_methode' => 'cash',
            'created_name' => auth()->user()->name,
            'teller_name' => auth()->user()->name,
            'payment_time' => Carbon::now(),
        ]);
        $getReceipt = $receipt->fresh();
        $billing->update([
            'payment_time' => Carbon::now(),
            'teller_name' => auth()->user()->name,
            'status' => 'LS',
            'receipt_id' => $getReceipt->id,
        ]);
        $action = 'activation_customer';
        ProcessWhatsappMessageJob::dispatch(
            $customer->id,
            $action,
            $traceId,
            null,
            null,
            null,
            null,
            null,
            null
        )->onQueue('default');
        ProcessTelegramMessageJob::dispatch(
            $customer->id,
            $action,
            $traceId,
            null,
            null,
            null,
        )->onQueue('default');
        return redirect()->back()
            ->with('success', 'Customer activated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $billingPeriodeActive = BillingPeriode::latest('created_at')->first();
        $billingLastPayment = Billing::where('customer_id', $customer->id)
            ->where('status', 'LS')
            ->latest('payment_time')
            ->first();
        $data = Billing::where('billings.customer_id', $customer->id)
            ->join('billing_periodes as bp', 'billings.billing_periode_id', '=', 'bp.id')
            ->join('customers as c', 'billings.customer_id', '=', 'c.id')
            ->leftJoin('members as m', 'c.member_id', '=', 'm.id')
            ->orderBy('bp.created_at', 'DESC')
            ->select('c.billing_period as billing_period_name', 'm.discount')
            ->first();

        $billingPeriods = $data->billing_period_name ?? null;
        $discount = $data->discount ?? null;
        $unpaid = Billing::where('customer_id', $customer->id)
            ->where('status', '!=', 'LS')
            ->orderByRaw('COALESCE(pay_later, due_date) DESC')
            ->get();
        $notYetPaid = collect($unpaid)->sum('paket_price');
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        $ont = CustomerOnt::where('customer_id', $customer->id)->first();
        try {
            $customerSecret = $this->secretService->getSecretById($router, $customer->secret_id);

            if ($customerSecret == 'error') {
                return redirect()->route('customers.index')->withErrors([
                    'msg' => 'Cannot find user with name ' . $customer->username . ' on router ' . $customer->mikrotik->name . '!'
                ]);
            } else {
                $secret = SecretResource::collection($customerSecret)->first();
                LogHelper::logInfo(null, ['secret' => $secret], 200, 'Secret Resource (Non-active)', $traceId);

                $customerSecretActive = $this->secretService->getOnePppSecretActive($router, $customer->username);
                $secretActive = SecretResource::collection($customerSecretActive)->first();
                LogHelper::logInfo(null, ['secretActive' => $secretActive], 200, 'Secret Resource (Active)', $traceId);
            }

            $serverDisconnect = 'false';
            $message = 'Server ' . $router->name . ' online';

            LogHelper::logInfo(null, [
                'router' => $router->name,
                'customer' => $customer->username,
                'passwordppp' => $customer->password_ppp,
                'status' => 'connected'
            ], 200, 'Router check success', $traceId);
        } catch (\Exception $e) {
            $secret = '';
            $secretActive = '';
            $serverDisconnect = 'true';
            $message = 'Server ' . $router->name . ' offline, data displayed is local data. You cannot change this information.';

            LogHelper::logError(null, [
                'router' => $router->name,
                'customer' => $customer->username ?? null,
                'error' => $e->getMessage(),
            ], 500, 'Router check failed', $traceId);
        }
        // dd($customer);
        return view('customers.show', [
            'customer' => $customer,
            'customer_contact' => $this->formatContact($customer->contact, false),
            'pppsecret' => $secret,
            'mikrotik' => $router,
            'secretActive' => $secretActive,
            'ont' => $ont,
            'billingLastPayment' => $billingLastPayment,
            'notYetPaid' => $notYetPaid,
            'serverDisconnect' => $serverDisconnect,
            'info_msg' => $message,
            'billingPeriods' => $billingPeriods,
            'discount' => $discount,
            'billingPeriodeActive' => $billingPeriodeActive,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();

        if ($router->disabled == 'false') {
            try {
                $this->testConnectionService->checkOnline($router);
                $serverDisconnect = 'false';
            } catch (\Exception $e) {
                $serverDisconnect = 'true';
            }
        } else {
            $serverDisconnect = 'true';
        }

        $customer_number = $this->formatContact($customer->contact, false);
        $users = User::all();
        $pakets = Paket::where('disabled', 'false')
            ->orderBy('price', 'ASC')
            ->get();

        $members = Member::all();

        LogHelper::logInfo(
            null,
            [
                'mikrotik_name' => $router->name,
                'customer_name' => $customer->name,
                'paket_name' => $customer->paket->name,
                'customer_number' => $customer_number,
                'admin_name' => $customer->user->name ?? null,
                'status_server' => $serverDisconnect,
                'member_type' => $customer->member->type ?? null,
            ],
            200, // Internal Server Error
            "Successfully get detail customers"
        );

        $currentPeriode = BillingPeriode::latest('created_at')->first();

        $availablePeriodes = collect();
        $current = Carbon::createFromFormat('F Y', $currentPeriode->name); // misal '202507'

        for ($i = 0; $i < 3; $i++) {
            $availablePeriodes->push($current->copy()->format('F Y')); // ex: July 2025, August 2025, September 2025
            $current->addMonth();
        }

        return view('customers.edit', compact('availablePeriodes', 'customer', 'pakets', 'customer_number', 'users', 'serverDisconnect', 'members'));
    }


    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $discount = $request->discount;
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
        $profile = Paket::where('id', $request->paket_id)->value('profile');
        if ($request->paket_id != $customer->paket_id) {
            if ($router->disabled == 'false') {
                try {
                    $this->testConnectionService->checkOnline($router);
                    $serverDisconnect = 'false';
                } catch (\Exception $e) {
                    $serverDisconnect = 'true';
                }
                if ($serverDisconnect == 'false') {
                    if ($customer->internet_service == 'ppp') {

                        $customerSecretId = $customer->secret_id;
                        $userSecret = $this->secretService->getSecretById($router, $customerSecretId);
                        // return dd($userSecret);

                        $username = $customer->username;
                        if ($userSecret != 'error') {

                            if ($autoIsolir != NULL && $autoIsolir->disabled == 'false') {
                                $secretProfiles = SecretResource::collection($userSecret)->value('profile');
                                if ($autoIsolir->profile_id != $secretProfiles) {
                                    $response = $this->secretService->updateProfileSecret($router, $profile, $customerSecretId);
                                } else {
                                    $response = '';
                                }
                            } else {
                                $response = $this->secretService->updateProfileSecret($router, $profile, $customerSecretId);
                            }
                            if (isset($response['after']['message'])) {
                                return redirect()->back()->with('error', $response['after']['message']);
                            } else if ($response == 'error') {
                                return redirect()->back()->with('error', 'Name secret ' . $username . ' not found on your server ' . $router->name . ' !');
                            } else {
                                $this->secretService->deleteActiveSecret($router, $username);
                                $customer->update([
                                    'paket_id' => $request->paket_id,
                                ]);
                            }
                        } else {
                            return redirect()->back()->withErrors(['msg' => $customerSecretId . '! Canot find user "' . $customer->username . '" on server ' . $router->name . '.']);
                        }
                    } else if ($customer->internet_service == 'ip_static') {
                        // $addressList = $this->ipStaticService->getAddressList($router);
                        // return dd($addressList);
                        $customerArpId = $customer->arp_id;
                        $ipAddress = $this->ipStaticService->getIpByArpId($router, $customerArpId);
                        $username = $customer->username;

                        if ($ipAddress != 'error') {
                            $profile_on_mikrotik = $this->secretService->getPppProfileByName($router, $profile);
                            $profile_on_mikrotik = $profile_on_mikrotik[0];
                            $valueLimitasi = $profile_on_mikrotik['rate-limit'] ?? '';
                            if (strlen($valueLimitasi) == 0) {
                                return redirect()->back()->withErrors(['msg' => 'Please create limitasi PPP Profile ' . $profile_on_mikrotik['name'] . ' on server ' . $router->name . '!']);
                            }
                            $parts = explode(' ', $valueLimitasi);

                            $parent = $profile['parent-queue'] ?? 'none';
                            $priority = $parts[4] . '/' . $parts[4];
                            $queuetype = $profile['queue-type'] ?? 'default-small';
                            $limitat = $parts[5];
                            $maxlimit = $parts[0];

                            // return dd($customer->simpleque_id);
                            try {
                                $updateLimitasi = $this->queueService->setLimitSimplequeById($router, $customer->simpleque_id, $parent, $priority, $queuetype, $limitat, $maxlimit);
                                // return dd($updateLimitasi);
                                if (isset($updateLimitasi['after']['message'])) {
                                    return redirect()->back()->withErrors(['msg' => 'Cant update limitasi on server ' . $router->name . '.']);
                                }
                            } catch (\Exception $e) {
                                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
                            }
                        } else {
                            return redirect()->back()->withErrors(['msg' => $customerArpId . '! Canot find user "' . $customer->username . '" on server ' . $router->name . '.']);
                        }
                    }
                }
            }
        }
        if ($request->activation_date && $request->activation_date != Carbon::parse($customer->activation_date)->format('d F Y')) {
            if ($router->disabled == 'false' && $autoIsolir != NULL && $autoIsolir->disabled == 'false') {
                $customerPayment = Billing::where('customer_id', $customer->id)->where('status', 'LS')->latest('updated_at')->first();
                $customerPaylater = Billing::where('customer_id', $customer->id)->where('status', 'PL')->latest('updated_at')->first();

                $activationDate = Carbon::parse($request->activation_date)->format('d');
                $totalDaysInMonth = Carbon::now()->addMonth()->daysInMonth;
                if ($activationDate > $totalDaysInMonth) {
                    $activationDate = $totalDaysInMonth;
                }
                if ($customerPaylater && $customerPayment) {
                    $paylater = Carbon::parse($customerPaylater->pay_later);
                    $payment = Carbon::parse($customerPayment->payment_time);
                    $result = $paylater->gt($payment);
                    if ($result) {
                        $lastBillPayment = Carbon::parse($paylater)->format('d_m_Y');
                    } else {
                        $lastMonthPayment = Carbon::parse($customerPayment->billing_periode->name)->addMonth()->format('m.Y');
                        $lastBillPayment = Carbon::parse($activationDate . '.' . $lastMonthPayment)->format('d_m_Y');
                    }
                } else if ($customerPayment) {
                    $lastMonthPayment = Carbon::parse($customerPayment->billing_periode->name)->addMonth()->format('m.Y');
                    $lastBillPayment = Carbon::parse($activationDate . '.' . $lastMonthPayment)->format('d_m_Y');
                } else if ($customerPaylater) {
                    $lastBillPayment = Carbon::parse($customerPaylater->pay_later)->format('d_m_Y');
                } else {
                    $lastBillPayment = Carbon::parse($request->activation_date)->format('d_m_Y');
                }
                $mikrotikId = $customer->mikrotik_id;
                $customerSecretId = $customer->secret_id;
                $router = Mikrotik::where('id', $mikrotikId)->first();
                $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();

                if ($autoIsolir->activation_date == 'true') {
                    try {
                        $comment_payment = $autoIsolir->comment_unpayment . '_' . $lastBillPayment;
                        if ($customer->internet_service == 'ppp') {
                            dispatch(new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerSecretId))->onQueue('default');
                        } else if ($customer->internet_service == 'ip_static') {
                            $ip_address = $customer->ip_static;
                            $simpleque_id = $customer->simpleque_id;
                            $address_list_isolir = $autoIsolir->address_list_isolir;
                            $comment = $customer->username . '_' . $comment_payment;
                            dispatch(new AddPaymentIpStaticBeforeDueDateJob($router, $ip_address, $simpleque_id, $address_list_isolir, $comment_payment, $comment))->onQueue('default');
                        }
                    } catch (\Exception $e) {
                        return redirect()->back()
                            ->with('error', 'Server offline, please try again!.');
                    }
                }
                // return dd(Carbon::parse($request->activation_date));
                $customer->update([
                    'activation_date' => Carbon::parse($request->activation_date)
                ]);
            }
        }
        if ($request->password) {
            $customer->update(['password' => Hash::make($request->password)]);
        }

        $profile = pppProfile::find($request->paket_id);
        if (!$profile) {
            return back()->withError('Profile PPP tidak ditemukan');
        }

        $paket = Paket::where('profile', $profile->profile_name)->first();
        if (!$paket) {
            return back()->withError('Paket untuk profile ini tidak ditemukan');
        }

        $paketId = $paket->id;

        $member_id = null;
        $billing_option = null;
        $billing_period = null;

        if (!empty($request->member_id)) {
            $member_id = $request->member_id;

            if (!empty($request->billing_option)) {
                $billing_option = $request->billing_option;
                $billing_period = $request->periode_billing;
            }
        }

        $oldMemberId = $customer->member_id;

        $customer->update([
            'name' => $request->name,
            'paket_id' => $paketId,
            'email' => $request->email,
            'address' => $request->address,
            'contact' => $this->formatContact($request->contact),
            'discount' => $discount,
            'user_id' => $request->user_id,
            'member_id' => $member_id,
            'billing_option' => $billing_option,
            'billing_period' => $billing_period
        ]);

        if ($oldMemberId !== $customer->member_id) {

            // ── Hitung harga baru ──────────────────────────────────────────────────
            $customer->loadMissing(['paket', 'member']);

            $basePrice = (int) $customer->paket->price;
            $discount  = (int) ($customer->member->discount ?? 0);
            $newPrice  = max(0, $basePrice - $discount);   // tidak boleh negatif

            // ── Tentukan apakah perlu update ke tabel billings ────────────────────
            $currentPeriode = BillingPeriode::latest('created_at')->first();   // contoh: July 2025
            $shouldUpdate   = false;
            $periodeId      = null;

            if ($customer->billing_option == 1) {
                // Flat/bulanan → selalu update periode berjalan
                $shouldUpdate = true;
                $periodeId    = $currentPeriode->id;
            } elseif (
                $customer->billing_option == 2
                && $customer->billing_period == $currentPeriode->name
            ) {

                // 1‑Time Bill & periodenya sama dgn periode aktif
                $shouldUpdate = true;
                $periodeId    = $currentPeriode->id;
            }

            // ── Jalankan update bila syarat terpenuhi ─────────────────────────────
            if ($shouldUpdate && $periodeId) {
                Billing::where('customer_id', $customer->id)
                    ->where('billing_periode_id', $periodeId)
                    ->whereIn('status', ['BL', 'PL'])          // hanya tagihan belum lunas
                    ->update(['paket_price' => $newPrice]);
            }
        }

        return redirect()->back()
            ->with('success', 'Customer ' . $customer->name . ' updated!');
    }

    public function editPpp(Customer $customer)
    {
        $callerID = request('caller_id');
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        // return dd($router);
        if ($customer->internet_service == 'ppp') {
            try {
                $services = PppType::orderBy('name', 'ASC')->pluck('name', 'id');
                return view('customers.editppp', compact('customer', 'services', 'callerID'));
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }
        } else if ($customer->internet_service == 'ip_static') {
            try {
                $getInterfaces = $this->ipStaticService->getInterfaces($router);
                if ($getInterfaces == null || empty($getInterfaces) || !isset($getInterfaces)) {
                    return redirect()->back()->withErrors(['msg' => 'Something wrong !!']);
                }
                $collectionInterfaces = InterfaceResource::collection($getInterfaces)->where('disabled', 'false');
                $interfaces = $collectionInterfaces->pluck(['name'], ['name'])->toArray();
                //return dd($interfaces);
                return view('customers.editppp', compact('customer', 'interfaces'));
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePpp(CustomerPppUpdateRequest $request, Customer $customer)
    {
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        if ($customer->internet_service == 'ppp') {
            $customerSecretId = $customer->secret_id;
            $username = $request->username;
            $password = $request->password_ppp;
            $serviceName = PppType::where('id', $request->ppp_type_id)->value('name');
            $callerID = $request->caller_id;
            if ($request->save_with_last_caller_id) {
                $callerID = $request->last_caller_id;
            }
            try {
                $response = $this->secretService->updateUserPasswordServiceSecret($router, $username, $password, $serviceName, $customerSecretId, $callerID);
                if (isset($response['after']['message'])) {
                    return redirect()->back()->with('error', $response['after']['message']);
                } else {
                    if ($response == 'error') {
                        return redirect()->back()->with('error', 'Name secret ' . $username . ' not found on your server ' . $router->name . ' !');
                    } else {
                        $customer->update([
                            'username' => $username,
                            'password_ppp' => $password,
                            'ppp_type_id' => $request->ppp_type_id,
                            'caller_id' => $callerID
                        ]);
                        if (!$request->dont_disconnect_client_button) {
                            $this->secretService->deleteActiveSecret($router, $username);
                        }
                        return redirect()->back()
                            ->with('success', 'PPP updated successfully');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
            }
        } else if ($customer->internet_service == 'ip_static') {
            $ip_address = $request->ip_static;
            $mac_address = $request->mac_address;
            $interface = $request->interface;
            $arp_id = $customer->arp_id;
            $simpleque_id = $customer->simpleque_id;
            //
            try {
                $response_arp = $this->ipStaticService->setIpAddressAndMacAddress($router, $arp_id, $ip_address, $mac_address, $interface);
                $response_limitasi = $this->ipStaticService->setIpSimpleque($router, $simpleque_id, $ip_address);
                if (isset($response_arp['after']['message'])) {
                    return redirect()->back()->with('error', 'ARP -> ' . $response_arp['after']['message']);
                } else if (isset($response_limitasi['after']['message'])) {
                    return redirect()->back()->with('error', 'Limitasi -> ' . $response_limitasi['after']['message']);
                } else {
                    if ($response_arp == 'error' || $response_limitasi == 'error') {
                        return redirect()->back()->with('error', 'Arp ' . $ip_address . '-' . $mac_address . '-' . $interface . ' not found on your server ' . $router->name . ' !');
                    } else {
                        $customer->update([
                            'ip_address' => $ip_address,
                            'mac_address' => $mac_address,
                            'interface' => $interface
                        ]);

                        return redirect()->back()
                            ->with('success', 'ARP updated successfully');
                    }
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
            }
        }
    }

    public function editRouter(Customer $customer)
    {
        $customerRouter = CustomerOnt::where('customer_id', $customer->id)->first();

        $merkRouters = MerkRouter::where('disabled', 'false')
            ->orderBy('name', 'ASC')
            ->get();

        $merkRouterId = null;
        $typeRouters = collect();

        if ($customerRouter && !empty($customerRouter->merk_ont)) {
            $merkRouterId = MerkRouter::where('disabled', 'false')
                ->where('name', $customerRouter->merk_ont)
                ->value('id');

            if ($merkRouterId) {
                $typeRouters = TypeRouter::where('disabled', 'false')
                    ->where('merk_router_id', $merkRouterId)
                    ->orderBy('name', 'ASC')
                    ->get();
            }
        }

        return view('customers.edit_router', compact(
            'merkRouters',
            'customer',
            'customerRouter',
            'typeRouters'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateRouter(CustomerRouterUpdateRequest $request, Customer $customer)
    {
        $customerId = $customer->id;
        $customer = CustomerOnt::where('customer_id', $customerId)->update([
            'merk_ont' => $request->merk_ont,
            'type_ont' => $request->type_ont,
            'username' => $request->username,
            'password' => $request->password,
            'port' => $request->port,
            'mac_address' => $request->mac_address,
            'description' => $request->description
        ]);
        return redirect()->back()
            ->with('success', 'Router updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Customer $customer)
    {
        $hasUnpaidBills = Billing::where('customer_id', $customer->id)
            ->whereNotIn('status', ['NP', 'LS'])
            ->exists();

        if ($hasUnpaidBills) {
            // return redirect()->route('billings.index', [
            //     'search_name' => $customer->name,
            // ])->with('warning', 'Customer memiliki tagihan aktif. Harap konfirmasi status billing sebelum melanjutkan penghapusan.');
            return redirect()->route('billings.index', [
                'search_name' => $customer->name,
                'source' => 'customer-delete',
                'customer_name' => $customer->name,
            ])->with('warning', 'Customer memiliki tagihan aktif. Harap konfirmasi status billing sebelum melanjutkan penghapusan.');
        }

        return view('customers.delete', compact('customer'));
    }

    public function destroy(Customer $customer)
    {
        if ($customer->activation === 'true') {
            $router = Mikrotik::find($customer->mikrotik_id);
            $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            try {
                $this->handleInternetService($customer, $router, $autoisolir);
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                    500,
                    "Error handling internet service."
                );
                return redirect()->back()
                    ->withErrors(['msg' => 'An error occurred while handling the router. Please try again.']);
            }
        }

        try {
            $customer->update([
                'activation' => 'false',
                'disabled' => 'true',
            ]);
            $customer->delete();
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "Customer soft deleted."
            );

            return redirect()->route('customers.index')
                ->with('success', 'Customer has been deleted, please check the router for confirmation.');
        } catch (ModelNotFoundException $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                404,
                "Customer not found."
            );
            return redirect()->back()
                ->withErrors(['msg' => 'Customer not found.']);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                404,
                "Error occurred while deleting customer."
            );
            return redirect()->back()
                ->withErrors(['msg' => 'An error occurred while deleting the customer. Please try again.']);
        }
    }

    /**
     * Handle the internet service logic for the customer.
     */
    private function handleInternetService(Customer $customer, $router, $autoisolir)
    {
        if (!$router) {
            throw new \Exception('Router not found for customer ID: ' . $customer->id);
        }

        $this->testConnectionService->checkOnline($router);
        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            404,
            "Connection to router succeeded."
        );

        if ($customer->internet_service === 'ppp') {
            $this->handlePppService($router, $customer->username);
        } elseif ($customer->internet_service === 'ip_static') {
            $this->handleIpStaticService($router, $customer, $autoisolir);
        }
    }

    /**
     * Handle PPP service logic.
     */
    private function handlePppService($router, $username)
    {
        $this->secretService->deleteSecretByName($router, $username);
        $this->secretService->deleteActiveSecret($router, $username);
        LogHelper::logInfo(
            null,
            ['username' => $username],
            200,
            "PPP service deleted."
        );
    }

    /**
     * Handle IP static service logic.
     */
    private function handleIpStaticService($router, Customer $customer, $autoisolir)
    {
        $simpleque_id = $customer->simpleque_id;
        $ip_address = $customer->ip_static;
        $arp_id = $customer->arp_id;
        $address_list_name = $autoisolir->address_list_isolir ?? 'none';

        dispatch(new DeleteIpStaticJob($router, $simpleque_id, $arp_id, $ip_address, $address_list_name))->onQueue('default');
        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            "PPP service deleted."
        );
    }

    /**
     * Move customer data to the deleted_customers table.
     */
    private function moveCustomerToDeleted(Customer $customer)
    {
        $deleteCustomer = [
            'id' => $customer->id, // penting: agar id tetap sama untuk keperluan restore
            'name' => $customer->name,
            'slug' => $customer->slug,
            'password' => $customer->password,
            'email' => $customer->email,
            'paket_id' => $customer->paket_id,
            'mikrotik_id' => $customer->mikrotik_id,
            'internet_service' => $customer->internet_service,
            'secret_id' => $customer->secret_id,
            'username' => $customer->username,
            'password_ppp' => $customer->password_ppp,
            'ppp_type_id' => $customer->ppp_type_id,
            'simpleque_id' => $customer->simpleque_id,
            'simpleque_name' => $customer->simpleque_name,
            'ip_static' => $customer->ip_static,
            'mac_address' => $customer->mac_address,
            'interface' => $customer->interface,
            'arp_id' => $customer->arp_id,
            'address_list_id' => $customer->address_list_id,
            'address' => $customer->address,
            'contact' => $this->formatContact($customer->contact),
            'disabled' => $customer->disabled,
            'activation' => $customer->activation,
            'created_at' => $customer->created_at,
            'updated_at' => $customer->updated_at,
            'deleted_at' => now(), // waktu dihapus
            'primary_server' => $customer->primary_server,
            'discount' => $customer->discount,
            'customer_id' => $customer->customer_id,
            'activation_date' => $customer->activation_date,
            'caller_id' => $customer->caller_id,
            'user_id' => $customer->user_id,
            'member_id' => $customer->member_id,
            'billing_option' => $customer->billing_option,
            'billing_period' => $customer->billing_period,
        ];

        DeletedCustomer::create($deleteCustomer);

        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            "Customer data moved to deleted_customers."
        );
    }

    public function disable(Customer $customer)
    {
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }
        $username = $customer->username;
        $job = new DisablePppSecretJob($router, $username);
        dispatch($job);
        $response = $job->getResponse();
        if ($response == 'success') {
            $customer->update([
                'disabled' => 'true'
            ]);
            return redirect()->back()->with('success', 'Disable customer successfully');
        } else {
            return redirect()->back()->withErrors(['msg' => 'Disable user failure, canot find user on mikrotik']);
        }
    }

    public function forceDisable(Customer $customer)
    {
        $customer->update([
            'disabled' => 'true'
        ]);
        return redirect()->back()->with('success', 'Disable customer successfully but not in server');
    }

    public function enable(Customer $customer)
    {
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
        }

        $username = $customer->username;
        $job = new EnablePppSecretJob($router, $username);
        dispatch($job);
        $response = $job->getResponse();
        if ($response == 'success') {
            $customer->update([
                'disabled' => 'false'
            ]);
            return redirect()->back()->with('success', 'Enable customer successfully');
        } else {
            return redirect()->back()->withErrors(['msg' => 'Enable user failure, canot find user on mikrotik']);
        }
    }

    public function forceEnable(Customer $customer)
    {
        $customer->update([
            'disabled' => 'false'
        ]);
        return redirect()->back()->with('success', 'Enable customer successfully but not in server');
    }

    public function activation_all_customer(Request $request)
    {
        $traceId = (string) Str::uuid();
        $mikrotikId = $request->servers_activation;
        $server = Mikrotik::where('id', $mikrotikId)->value('name');
        $activationDate = Carbon::now()->format('d F Y');
        $customers = Customer::where('mikrotik_id', $mikrotikId)->get();
        foreach ($customers as $customer) {
            $customer->update([
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
            ]);
            $action = 'activation_customer';
            ProcessWhatsappMessageJob::dispatch(
                $customer->id,
                $action,
                $traceId,
                null,
                null,
                null,
                null,
                null,
                null
            )->onQueue('default');
            ProcessTelegramMessageJob::dispatch(
                $customer->id,
                $action,
                $traceId,
                null,
                null,
                null
            )->onQueue('default');
        }
        return redirect()->back()
            ->with('success', 'Activated ' . count($customers) . ' customers on server ' . $server . '.');
    }

    public function getPppSecretsActiveByName(Request $request, Mikrotik $mikrotik)
    {
        try {
            $pppActive = $this->secretService->getPppSecretActiveByName($mikrotik);
            if ($pppActive) {
                return response()->json([
                    'status' => 'online',
                ]);
            } else {
                return response()->json([
                    'status' => 'offline',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',

            ], 500);
        }
    }

    private function formatContact($contact, $toInternational = true)
    {
        $gateway = WhatsappGateway::where('slug', 'gateway')->first();

        if ($gateway && !$gateway->disabled) {
            $countryCode = $gateway->country_code;
        } else {
            LogHelper::logWarning(
                null,
                null,
                200,
                "Whatsapp Gateway disabled or did not found, using default country code 62."
            );
            $countryCode = '62';
        }

        if ($contact) {
            if ($toInternational) {
                if (substr($contact, 0, 1) == '0') {
                    $contact = preg_replace('/^0?/', $countryCode, $contact);
                }
            } else {
                if (substr($contact, 0, strlen($countryCode)) == $countryCode) {
                    $contact = '0' . substr($contact, strlen($countryCode));
                }
            }
        }

        return $contact;
    }

    public function exportSecret(Customer $customer): RedirectResponse
    {
        $internet_service = $customer->internet_service;
        $mikrotikId = $customer->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotikId)->first();
        $profile = Paket::where('id', $customer->paket_id)->value('profile');
        if ($router->disabled == 'true') {
            return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' disable, please enable first.']);
        }
        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
        if (isset($autoIsolir)) {
            if ($autoIsolir->activation_date == 'true') {
                $totalDaysInNextMonth = Carbon::now()->addMonth()->daysInMonth;
                $day = Carbon::now()->format('d');
                if ($day > $totalDaysInNextMonth) {
                    $day = $totalDaysInNextMonth;
                }
                $nextMonth = Carbon::now()->addMonth()->format('m_Y');
                $comment_payment = $autoIsolir->comment_unpayment . '_' . $day . '_' . $nextMonth;
            } else {
                $comment_payment = $autoIsolir->comment_payment;
            }
        } else {
            $comment_payment = 'create_from_customer_management';
        }
        $username = $customer->username;
        if ($internet_service == 'ppp') {
            $password = $customer->password_ppp;
            $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');
            try {
                $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $comment_payment);
                if (isset($createSecret['after']['message'])) {
                    return redirect()->back()->withErrors(['msg' => 'Cant create user ppp on server ' . $router->name . '.']);
                } else {
                    $secretId = $createSecret['after']['ret'];
                    $customer->update([
                        'activation' => 'true',
                        'disabled' => 'false',
                        'secret_id' => $secretId,
                    ]);
                    $this->secretService->enableSecret($router, $username);
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }
        } else if ($internet_service == 'ip_static') {
            $comment = $customer->username ?? 'Create from Customer Management';
            $profile_on_mikrotik = $this->secretService->getPppProfileByName($router, $profile);
            $profile = $profile_on_mikrotik[0];
            return dd($profile);
            if ($profile['rate-limit']) {
                return redirect()->back()->withErrors(['msg' => 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!']);
            }
            $valueLimitasi = $profile['rate-limit'] ?? '';
            if (strlen($valueLimitasi) == 0) {
                return redirect()->back()->withErrors(['msg' => 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!']);
            }
            $parts = explode(' ', $valueLimitasi);
            $ip_static = $customer->ip_static;
            $mac_address = $customer->mac_address;
            $interface = $customer->interface;
            $parent = $profile['parent-queue'] ?? 'none';
            $priority = $parts[4] . '/' . $parts[4];
            $queuetype = $profile['queue-type'] ?? '';
            $limitat = $parts[5];
            $maxlimit = $parts[0];
            try {
                $createLimitasi = $this->queueService->createSimpleQueue($router, $username, $ip_static, $parent, $priority, $queuetype, $limitat, $maxlimit, $comment_payment);
                if (isset($createLimitasi['after']['message'])) {
                    return redirect()->back()->withErrors(['msg' => 'Cant create limitasi on server ' . $router->name . ', ' . $createLimitasi['after']['message'] . '.']);
                } else {
                    $simpleque_id = $createLimitasi['after']['ret'];
                }
                $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $mac_address, $interface, $comment);
                // $createArp = $this->ipStaticService->addIpToArp($router, $ip_static, $interface, $comment);

                if (isset($createArp['after']['message'])) {
                    //if ($simpleque_id) {
                    $this->ipStaticService->deleteIpFromSimpleQueue($router, $simpleque_id);
                    //  }

                    return redirect()->back()->withErrors(['msg' => 'Cant create arp to ip ' . $ip_static . ' on server ' . $router->name . ', ' . $createArp['after']['message'] . ' .']);
                } else {
                    $arp_id = $createArp['after']['ret'];
                }
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Cant connect to the server ' . $router->name . '.']);
            }

            $customer->update([
                'activation' => 'true',
                'disabled' => 'false',
                'simpleque_id' => $simpleque_id,
                'arp_id' => $arp_id,
            ]);
        }
        return redirect()->back()
            ->with('success', 'Succesfully to Export Pppoe Secret Customer');
    }

    public function trashed(Request $request)
    {
        $perPage = $request->input('perPage', 25); // default 25
        $searchParams = $request->only([
            'search_name',
            'search_address',
            'server_search',
            'search_paket',
            'status',
            'search_admin'
        ]);

        $customers = Customer::onlyTrashed()
            ->with(['user', 'paket', 'member'])
            ->when($searchParams['search_name'] ?? null, fn($query, $name) => $query->where('name', 'like', "%{$name}%"))
            ->when($searchParams['search_address'] ?? null, function ($query, $address) {
                if ($address === 'NULL') {
                    $query->whereNull('address');
                } else {
                    $query->where('address', $address);
                }
            })
            ->when($searchParams['search_admin'] ?? null, function ($query, $admin) {
                if ($admin === 'NULL') {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $admin);
                }
            })
            ->when($searchParams['server_search'] ?? null, fn($query, $server) => $query->where('mikrotik_id', $server))
            ->when($searchParams['search_paket'] ?? null, fn($query, $paket) => $query->where('paket_id', $paket))
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        return view('customers.trashed', [
            'data' => $customers,
            'address' => Customer::onlyTrashed()->select('address')->distinct()->pluck('address', 'address'),
            'allServers' => Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id'),
            'allAdmin' => User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id'),
            'periodeName' => BillingPeriode::latest('created_at')->value('name'),
            'perPage' => $perPage
        ])->with('i', (request()->input('page', 1) - 1) * $perPage);
    }

    public function restore($slug)
    {
        $customer = Customer::onlyTrashed()->where('slug', $slug)->firstOrFail();

        $customer->restore();

        LogHelper::logInfo(
            null,
            ['customer_id' => $customer->id],
            200,
            "Customer restored."
        );

        return redirect()->route('customers.index')->with('success', 'Customer restored successfully.');
    }

    public function forceDelete($slug)
    {
        $customer = Customer::onlyTrashed()->where('slug', $slug)->firstOrFail();

        if ($customer->activation === 'true') {
            $router = Mikrotik::find($customer->mikrotik_id);
            $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            try {
                $this->handleInternetService($customer, $router, $autoisolir);
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                    500,
                    "Error handling internet service."
                );
                return redirect()->back()
                    ->withErrors(['msg' => 'An error occurred while handling the router. Please try again.']);
            }
        }

        try {
            $this->moveCustomerToDeleted($customer);
            $customer->forceDelete();
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "Customer soft deleted."
            );

            return redirect()->route('customers.trashed')->with('success', 'Customer permanently deleted.');
        } catch (ModelNotFoundException $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                404,
                "Customer not found."
            );
            return redirect()->back()
                ->withErrors(['msg' => 'Customer not found.']);
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id, 'error' => $e->getMessage()],
                404,
                "Error occurred while deleting customer."
            );
            return redirect()->back()
                ->withErrors(['msg' => 'An error occurred while deleting the customer. Please try again.']);
        }
    }
}
