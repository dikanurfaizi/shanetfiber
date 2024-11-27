<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\PppType;
use App\Models\Customer;
use App\Models\Mikrotik;
use Illuminate\View\View;
use App\Models\AutoIsolir;
use App\Models\MerkRouter;
use App\Models\TypeRouter;
use App\Models\CustomerOnt;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Billing\Billing;
use App\Models\DeletedCustomer;
use Illuminate\Validation\Rule;
use App\Jobs\DeletePppSecretJob;
use App\Jobs\EnablePppSecretJob;
use App\Jobs\DisablePppSecretJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentAfterDueDateJob;
use RouterOS\Exceptions\QueryException;
use App\Jobs\AddPaymentBeforeDueDateJob;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Requests\CustomerPppUpdateRequest;
use App\Services\Routerboard\Ppp\SecretService;
use RouterOS\Exceptions\BadCredentialsException;
use App\Http\Requests\CustomerRouterUpdateRequest;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Resources\InterfaceResource;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticBeforeDueDateJob;
//use App\Jobs\ActivationCustomerJob;
use App\Jobs\DeleteIpStaticJob;
use App\Models\Receipt;
use App\Models\Websystem;
use App\Models\WhatsappGateway\WhatsappGateway;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\QueueService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\SystemService;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

//use ArielMejiaDev\LarapexCharts\Facades\LarapexChart;
use ArielMejiaDev\LarapexCharts\LarapexChart;

use function Laravel\Prompts\error;

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
    private SystemService $systemService;
    private QueueService $queueService;
    private IpStaticService $ipStaticService;


    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService, SystemService $systemService, QueueService $queueService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->systemService = $systemService;
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
    public function index(Request $request)
    {
        $paginate = 25;
        $searchName = $request->search_name;
        $searchAddress = $request->search_address;
        $searchServer = $request->server_search;
        $searchPaket = $request->search_paket;
        $searchStatus = $request->status;
        $searchService = $request->search_service;
        $routersEnable = Mikrotik::where('disabled', 'false')
            ->when($searchServer, function ($query) use ($searchServer) {
                $query->where('id', $searchServer);
            })
            ->get();
        if ($searchStatus == 'isolir' || $searchStatus == 'offline' || $searchStatus == 'online') {
            $status = $searchStatus;
            $dataCustomers = array();
            if (count($routersEnable) == 0) {
                return redirect()->route('customers.index')->withErrors(['msg' => 'All server disable, please check your server setting.', 'Or you dont have server data.']);
            }
            foreach ($routersEnable as $router) {
                try {
                    $this->testConnectionService->checkOnline($router);
                } catch (\Exception $e) {
                    return redirect()->route('customers.index')->withErrors(['msg' => 'Server ' . $router->name . ' Offline.', 'Please enable first or check your configuration!']);
                }
                if ($status == 'isolir') {
                    $profileIsolir = AutoIsolir::where('mikrotik_id', $router->id)->value('profile_id');
                    if ($profileIsolir) {
                        $getSecretStatus = $this->secretService->getSecretByProfile($router, $profileIsolir);
                        $collectionSecretStatus = SecretResource::collection($getSecretStatus)->where('disabled', 'false');
                        $secretIsolir = $collectionSecretStatus->pluck(['name'])->toArray();
                        $secretsStatus = $secretIsolir;
                    } else {
                        return redirect()->route('customers.index')->withErrors(['msg' => 'Please config auto isolir on Server ' . $router->name . ' first.']);
                    }
                } else {
                    $getAllSecretActive = $this->secretService->getPppActive($router);
                    $collectionSecretActive = SecretResource::collection($getAllSecretActive);
                    $secretActive = $collectionSecretActive->pluck(['name'])->toArray();
                    if ($status == 'offline') {
                        $usernameCustomers = Customer::where('mikrotik_id', $router->id)
                            ->where('disabled', 'false')
                            ->pluck('username')
                            ->toArray();
                        $collectCustomerUsernames = collect($usernameCustomers);
                        $secretsStatus = $collectCustomerUsernames->diff($secretActive);
                    } else {
                        $secretsStatus = $secretActive;
                    }
                }
                foreach ($secretsStatus as $secretName) {
                    $customers = Customer::where('username', $secretName)->first();
                    $dataCustomers[] = $customers;
                    $dataCustomers = collect($dataCustomers)
                        ->filter(function ($customers) use ($searchAddress) {
                            if ($searchAddress == 'NULL') {
                                return $customers->address == null;
                            } else if (isset($searchAddress)) {
                                return $customers->address == $searchAddress;
                            } else {
                                return (!isset($searchAddress));
                            }
                        })
                        ->filter(function ($customers) use ($searchServer) {
                            if (isset($searchServer)) {
                                return $customers->mikrotik_id == $searchServer;
                            } else {
                                return (!isset($searchServer));
                            }
                        })
                        ->filter(function ($customers) use ($searchPaket) {
                            if (isset($searchPaket)) {
                                return $customers->paket_id == $searchPaket;
                            } else {
                                return (!isset($searchPaket));
                            }
                        })
                        ->where('activation', 'true')
                        ->sortBy('name');
                }
            }
            //Get current page form url e.g. &page=6
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $collection = new Collection($dataCustomers);
            $currentPageSearchResults = $collection->slice(($currentPage - 1) * $paginate, $paginate)->all();
            $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($collection), $paginate);
            $paginatedSearchResults->setPath($request->url());
            $paginatedSearchResults->appends($request->except(['page']));
            return view('customers.offline', [
                'data' => $paginatedSearchResults,
                'status' => $status,
                'dataCustomers' => $dataCustomers,
            ])->with('i', (request()->input('page', 1) - 1) * $paginate);
            // $loop->iteration
        } else {
            $customers = Customer::query()
                ->when($searchName, function ($query) use ($searchName) {
                    $query->where('name', 'like', "%{$searchName}%");
                })
                ->when($searchAddress, function ($query) use ($searchAddress) {
                    if ($searchAddress == 'NULL') {
                        $query->where('address', NULL);
                    } else {
                        $query->where('address', $searchAddress);
                    }
                })
                ->when($searchService, function ($query) use ($searchService) {
                    if ($searchService) {
                        $query->where('internet_service', $searchService);
                    }
                })
                ->when($searchStatus, function ($query) use ($searchStatus) {
                    if ($searchStatus == 'deactivated') {
                        $query->where('activation', 'false');
                    } else if ($searchStatus == 'free') {
                        $query->where('discount', 100);
                    } else {
                        $query->where('disabled', $searchStatus);
                        $query->where('activation', 'true');
                    }
                })
                ->when($searchServer, function ($query) use ($searchServer) {
                    $query->where('mikrotik_id', $searchServer);
                })
                ->when($searchPaket, function ($query) use ($searchPaket) {
                    $query->where('paket_id', $searchPaket);
                })
                ->orderBy('address', 'ASC')
                ->orderBy('name', 'ASC');
            $customersGet = $customers->get();
            $data = $customers->paginate($paginate);
            $totalIncoming = 0;
            foreach ($customersGet as $customerGet) {
                $totalIncoming += $customerGet->paket->price * ((100 - $customerGet->discount) / 100);
            }
            if (count($data) == 0) {
                return view('customers.index', compact('data', 'totalIncoming'), [
                    'message' => 'No customer found. Try to search again !',
                ]);
            } else {
                return view('customers.index', compact('data', 'totalIncoming'))
                    ->with('i', (request()->input('page', 1) - 1) * $paginate);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        $pppservices = PppType::orderBy('name', 'ASC')->pluck('name', 'id');
        // $interfaces = $this->ipStaticService->getInterfaces($mikrotiks);
        return view('customers.create', [
            'mikrotiks' => $mikrotiks,
            'pppservices' => $pppservices,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerStoreRequest $request): RedirectResponse
    {
        $internet_service = $request->internet_service;
        $mikrotikId = $request->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotikId)->first();
        //  $date = Carbon::now()->format('d');
        if ($request->discount) {
            $discount = $request->discount;
        } else {
            $discount = 0;
        }
        $periodeId = BillingPeriode::latest('created_at')->value('id');
        if (!isset($periodeId)) {
            $billingPeriode = BillingPeriode::create([
                'name' => Carbon::now()->format('F Y'),
            ]);
            $newBillingPeriode = $billingPeriode->fresh();
            $periodeId = $newBillingPeriode->id;
            $periodeName = $newBillingPeriode->name;
        } else {
            $periodeName = BillingPeriode::latest('created_at')->value('name');
        }
        $month = Carbon::parse($periodeName)->format('m');
        $year = Carbon::parse($periodeName)->format('Y');
        $name = explode(' ', $request->name);
        $first_name = $name[0];
        if ($request->address) {
            $string = str_replace(' ', '-', $request->address);
            $address = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
            $username = str($first_name . '-' . $address . '-' . Str::random(4))->slug();
        } else {
            $username = str($first_name . '-' . $year . '-' . $month . '-' . Str::random(4))->slug();
        }
        if ($internet_service == 'ppp') {
            $passwordPpp = str(Str::random(6));
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'slug' => str($first_name . '-' . Str::random(6))->slug(),
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'contact' => $request->contact,
                'internet_service' => $request->internet_service,
                'primary_server' => $request->mikrotik_id,
                'discount' => $discount,
                'mikrotik_id' => $request->input('mikrotik_id'),
                'paket_id' => $request->input('paket_id'),
                'username' => $username,
                'password_ppp' => $passwordPpp,
                'ppp_type_id' => $request->ppp_type_id
            ]);
        } else if ($internet_service == 'ip_static') {
            $ip_static = $request->input('ip_static');
            $mac_address = $request->input('mac_address');
            $interface = $request->input('interface');
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'slug' => str($first_name . '-' . Str::random(6))->slug(),
                'username' => $username,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'contact' => $request->contact,
                'internet_service' => $request->internet_service,
                'primary_server' => $request->mikrotik_id,
                'discount' => $discount,
                'mikrotik_id' => $request->input('mikrotik_id'),
                'paket_id' => $request->input('paket_id'),
                'simpleque_name' => $username,
                'ip_static' => $ip_static,
                'mac_address' => $mac_address,
                'interface' => $interface
            ]);
        }
        $getCustomer = $customer->fresh();
        $websystem = Websystem::where('slug', 'websystem')->first();
        $longCode = '%0' . $websystem->long_customer_code . 'd';
        $customerId = $websystem->customer_code . sprintf('%02d', $customer->mikrotik_id) . '-' . sprintf($longCode, $customer->id);
        $getCustomer->update([
            'customer_id' => $customerId
        ]);
        CustomerOnt::create([
            'customer_id' => $getCustomer->id,
        ]);
        $billing = Billing::create([
            'billing_periode_id' => $periodeId,
            'slug' => str($getCustomer->id . '-' . Str::random(4))->slug(),
            'billing_number' => str($getCustomer->id . '-' . Str::random(4)  . '-' . $month . '-' . $year)->slug(),
            'customer_id' => $getCustomer->id,
            'customer_name' => $getCustomer->name,
            'paket_name' => $getCustomer->paket->name,
            'paket_price' => $getCustomer->paket->price * ((100 - $getCustomer->discount) / 100),
            'created_name' => auth()->user()->name,
            'status' => 'BL'
        ]);
        return redirect()->route('customers.index')
            ->with('success', 'Customer added successfully. Please activated first to create user ppp on server ' . $router->name . '!');
    }

    public function activation(Customer $customer): RedirectResponse
    {
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
            // return dd($profile);
            //  if ($profile['rate-limit'] ) {
            //      return redirect()->back()->withErrors(['msg' => 'Please create limitasi PPP Profile ' . $profile['name'] . ' on server ' . $router->name . '!']);
            //  }
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
        return redirect()->back()
            ->with('success', 'Customer activated.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $billingLastPayment = Billing::where('customer_id', $customer->id)
            ->where('status', 'LS')
            ->latest('payment_time')
            ->first();
        $unpaid = Billing::where('customer_id', $customer->id)->where('status', '!=', 'LS')->get();
        $notYetPaid = collect($unpaid)->sum('paket_price');
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        $ont = CustomerOnt::where('customer_id', $customer->id)->first();
        try {
            $customerSecret = $this->secretService->getSecretById($router, $customer->secret_id);
            if ($customerSecret == 'error') {
                return redirect()->route('customers.index')->withErrors(['msg' => 'Cannot find user with name ' . $customer->username . ' on router ' . $customer->mikrotik->name . '!']);
            } else {
                $secret = SecretResource::collection($customerSecret)->first();
                $customerSecretActive = $this->secretService->getOnePppSecretActive($router, $customer->username);
                $secretActive = SecretResource::collection($customerSecretActive)->first();
            }
            $serverDisconnect = 'false';
            $message = 'Server ' . $router->name . ' online';
        } catch (\Exception $e) {
            $secret = '';
            $secretActive = '';
            $serverDisconnect = 'true';
            $message = 'Server ' . $router->name . ' offline, data displayed is local data. You cannot change this information.';
        }
        $countryCode = '62';
        if (WhatsappGateway::where('slug', 'gateway')->value('disabled') == false) {
            $countryCode = WhatsappGateway::where('slug', 'gateway')->value('country_code');
        }
        $customer_number = $customer->contact;
        if ($customer_number) {
            $customer_number = preg_replace('/^' . $countryCode . '?/', '0', $customer_number);
        }
        return view('customers.show', [
            'customer' => $customer,
            'customer_contact' => $customer_number,
            'pppsecret' => $secret,
            'mikrotik' => $router,
            'secretActive' => $secretActive,
            'ont' => $ont,
            'billingLastPayment' => $billingLastPayment,
            'notYetPaid' => $notYetPaid,
            'serverDisconnect' => $serverDisconnect,
            'info_msg' => $message,
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

        $countryCode = '62';
        if (WhatsappGateway::where('slug', 'gateway')->value('disabled') == false) {
            $countryCode = WhatsappGateway::where('slug', 'gateway')->value('country_code');
        }
        $customer_number = $customer->contact;
        if ($customer_number) {
            $customer_number = preg_replace('/^' . $countryCode . '?/', '0', $customer_number);
        }
        $pakets = Paket::where('mikrotik_id', $customer->mikrotik_id)->where('disabled', 'false')->orderBy('price', 'ASC')->get();
        return view('customers.edit', compact('customer', 'pakets', 'serverDisconnect', 'customer_number'));
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


        $countryCode = '62';
        if (WhatsappGateway::where('slug', 'gateway')->value('disabled') == false) {
            $countryCode = WhatsappGateway::where('slug', 'gateway')->value('country_code');
        }
        $number = $request->input('contact');
        if ($number) {
            if (substr($number, 0, 2) == '62') {
                $number = preg_replace('/^' . $countryCode . '?/', $countryCode, $number);
            } else if (substr($number, 0, 1) == '0') {
                $number = preg_replace('/^0?/', $countryCode, $number);
            }
        }
        $customer->update([
            'name' => $request->name,
            'paket_id' => $request->paket_id,
            'email' => $request->email,
            'address' => $request->address,
            'contact' => $number,
            'discount' => $discount,
        ]);
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
        // return dd($request->interface);
        //  $this->validate($request, [
        //      'username' => ['required', 'string', 'max:45', Rule::unique('customers')->ignore($customer->slug, 'slug')],
        //  ]);

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
        $merkRouters = MerkRouter::where('disabled', 'false')->orderBy('name', 'ASC')->get();
        $merkRouterId = MerkRouter::where('disabled', 'false')->where('name', $customerRouter->merk_ont)->value('id');
        $typeRouters = TypeRouter::where('disabled', 'false')->where('merk_router_id', $merkRouterId)->orderBy('name', 'ASC')->get();
        return view('customers.edit_router', compact('merkRouters', 'customer', 'customerRouter', 'typeRouters'));
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
        return view('customers.delete', compact('customer'));
    }
    public function destroy(Customer $customer)
    {
        if ($customer->activation == 'true') {
            $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
            $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
            if ($customer->internet_service == 'ppp') {
                try {
                    $this->testConnectionService->checkOnline($router);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
                }
                $username = $customer->username;
                //dispatch(new DeletePppSecretJob($router, $username))->onQueue('default');
                $this->secretService->deleteSecretByName($router, $username);
                $this->secretService->deleteActiveSecret($router, $username);
            } else if ($customer->internet_service == 'ip_static') {
                // return dd($customer->internet_service);
                try {
                    $this->testConnectionService->checkOnline($router);
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
                }
                $simpleque_id = $customer->simpleque_id;
                //  $address_list_id = $customer->address_list_id;
                $ip_address = $customer->ip_static;
                $arp_id = $customer->arp_id;
                if ($autoisolir) {
                    $address_list_name = $autoisolir->address_list_isolir;
                } else {
                    $address_list_name = 'none';
                }

                // $interface = $customer->interface;
                //return dd($simple_queue_id);
                dispatch(new DeleteIpStaticJob($router, $simpleque_id, $arp_id, $ip_address, $address_list_name))->onQueue('default');
                //$this->ipStaticService->deleteIpFromSimpleQueue($router, $simple_queue_id);
                //$this->ipStaticService->deleteIpFromIsolirAddressList($router, $address_list_id);
            }
        }
        //$customer->delete();
        $customer->forceDelete();
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully');
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
            'disabled' => 'false'
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
        // dispatch(new EnablePppSecretJob($router, $username))->onQueue('default');
        // $customer->update([
        //     'disabled' => 'false',
        // ]);
        // return redirect()->back()->with('success', 'Enable customer successfully');
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
}
