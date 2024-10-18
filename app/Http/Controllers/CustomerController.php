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
use App\Jobs\ActivationCustomerJob;
use App\Models\Receipt;
use App\Models\Websystem;
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


    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService, SystemService $systemService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->systemService = $systemService;
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
            // return dd($dataCustomers);
            //Get current page form url e.g. &page=6
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            //Create a new Laravel collection from the array data
            $collection = new Collection($dataCustomers);
            //Slice the collection to get the items to display in current page
            $currentPageSearchResults = $collection->slice(($currentPage - 1) * $paginate, $paginate)->all();
            //Create our paginator and pass it to the view
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
        $date = Carbon::now()->format('d');
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
        //return dd($username);
        //Informasi ppp
        //  $username = str($first_name . '-' . $year . '-' . $month . '-' . Str::random(4))->slug();
        $passwordPpp = str(Str::random(6));

        $mikrotikId = $request->mikrotik_id;
        $router = Mikrotik::where('id', $mikrotikId)->first();

        $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
        if (isset($autoIsolir)) {
            if ($autoIsolir->activation_date == 'true') {
                $comment_unpayment = $autoIsolir->comment_unpayment . '_' . $date . '_' . $month . '_' . $year;
            } else {
                $comment_unpayment = $autoIsolir->comment_unpayment;
            }
            // $firstProfile = $autoIsolir->profile_id;
        } else {
            // $firstProfile = Paket::where('mikrotik_id', $mikrotikId)->where('id', $request->paket_id)->value('profile');
            $comment_unpayment = 'create_from_customer_management';
        }
        $firstProfile = Paket::where('mikrotik_id', $mikrotikId)->where('id', $request->paket_id)->value('profile');
        $serviceName = PppType::where('id', $request->ppp_type_id)->value('name');

        try {

            $createSecret = $this->secretService->createSecret($router, $username, $passwordPpp, $serviceName, $firstProfile, $comment_unpayment);
            if (isset($createSecret['after']['message'])) {
                $checkCreateSecret = 'false';
                $secretId = 0;
            } else {
                $checkCreateSecret = 'true';
                $secretId = $createSecret['after']['ret'];
            }
        } catch (\Exception $e) {
            $checkCreateSecret = 'false';
            $secretId = 0;
        }
        $request->request->remove('password_confirmation');
        $request->request->add(['slug' => str($first_name . '-' . Str::random(6))->slug()]);
        $request->request->add(['username' => $username]);
        $request->request->add(['password_ppp' => $passwordPpp]);
        $request->request->add(['password' => Hash::make($request->password)]);
        $request->request->add(['secret_id' => $secretId]);
        $request->request->add(['primary_server' => $request->mikrotik_id]);
        $request->request->add(['discount' => $discount]);
        $customer = Customer::create($request->all());

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
        if ($checkCreateSecret == 'true') {
            return redirect()->route('customers.index')
                ->with('success', 'Customer added successfully.');
        } else {
            return redirect()->route('customers.index')
                ->with('success', 'Customer added successfully but cant create user secret on server ' . $router->name . '.');
        }
    }

    public function activation(Customer $customer): RedirectResponse
    {
        $activationDate = Carbon::now();

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
        $mikrotikId = $customer->mikrotik_id;
        $profile = Paket::where('mikrotik_id', $mikrotikId)->where('id', $customer->paket_id)->value('profile');
        $router = Mikrotik::where('id', $mikrotikId)->first();
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

        if ($customer->secret_id == 0) {
            //Password ppp
            $password = $customer->password_ppp;
            $serviceName = PppType::where('id', $customer->ppp_type_id)->value('name');

            try {
                $createSecret = $this->secretService->createSecret($router, $username, $password, $serviceName, $profile, $comment_payment);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
            }


            if (isset($createSecret['after']['message'])) {
                return redirect()->back()->withErrors(['msg' => $createSecret['after']['message'], 'Maybe, profile ' . $profile . ' on your mikrotik not found.']);
            } else {
                $customer->update([
                    'activation_date' => $activationDate,
                    'activation' => 'true',
                    'disabled' => 'false',
                    'secret_id' => $createSecret['after']['ret'],
                ]);
                $this->secretService->enableSecret($router, $username);
                return redirect()->back()
                    ->with('success', 'User secret created on server ' . $router->name . ', Customer activated.');
            }
        } else {
            try {
                dispatch(new ActivationCustomerJob($router, $comment_payment, $customer->secret_id, $username))->onQueue('default');
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Server ' . $router->name . ' offline, please check your router.']);
            }

            $customer->update([
                'activation_date' => $activationDate,
                'activation' => 'true',
                'disabled' => 'false',
            ]);
            return redirect()->back()
                ->with('success', 'Customer activated.');
        }
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

        return view('customers.show', [
            'customer' => $customer,
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
        $pakets = Paket::where('mikrotik_id', $customer->mikrotik_id)->where('disabled', 'false')->orderBy('price', 'ASC')->get();
        return view('customers.edit', compact('customer', 'pakets', 'serverDisconnect'));
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {

        $discount = $request->discount;

        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();
        if ($request->paket_id != $customer->paket_id) {

            if ($router->disabled == 'false') {

                try {
                    $this->testConnectionService->checkOnline($router);
                    $serverDisconnect = 'false';
                } catch (\Exception $e) {
                    $serverDisconnect = 'true';
                }
                if ($serverDisconnect == 'false') {
                    $customerSecretId = $customer->secret_id;
                    $userSecret = $this->secretService->getSecretById($router, $customerSecretId);

                    $profileName = Paket::where('id', $request->paket_id)->value('profile');
                    $username = $customer->username;
                    if ($userSecret != 'error') {

                        if ($autoIsolir != NULL && $autoIsolir->disabled == 'false') {
                            $secretProfiles = SecretResource::collection($userSecret)->value('profile');
                            if ($autoIsolir->profile_id != $secretProfiles) {
                                $response = $this->secretService->updateProfileSecret($router, $profileName, $customerSecretId);
                            } else {
                                $response = '';
                            }
                        } else {
                            $response = $this->secretService->updateProfileSecret($router, $profileName, $customerSecretId);
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
                        dispatch(new AddPaymentBeforeDueDateJob($router, $comment_payment, $customerSecretId))->onQueue('default');
                    } catch (\Exception $e) {
                        return redirect()->back()
                            ->with('error', 'Server offline, please try again!.');
                    }
                }
                $customer->update([
                    'activation_date' => Carbon::parse($request->activation_date)
                ]);
            }
        }
        if ($request->password) {
            $customer->update(['password' => Hash::make($request->password)]);
        }
        $customer->update([
            'name' => $request->name,
            'paket_id' => $request->paket_id,
            'email' => $request->email,
            'address' => $request->address,
            'contact' => $request->contact,
            'discount' => $discount,
        ]);
        return redirect()->back()
            ->with('success', 'Customer ' . $customer->name . ' updated!');
    }


    public function editPpp(Customer $customer)
    {
        $callerID = request('caller_id');
        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
        try {
            $this->testConnectionService->checkOnline($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Server offline, canot edit this data.']);
        }
        $services = PppType::orderBy('name', 'ASC')->pluck('name', 'id');
        return view('customers.editppp', compact('customer', 'services', 'callerID'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePpp(CustomerPppUpdateRequest $request, Customer $customer)
    {


        $this->validate($request, [
            'username' => ['required', 'string', 'max:45', Rule::unique('customers')->ignore($customer->slug, 'slug')],
        ]);

        $customerSecretId = $customer->secret_id;
        $username = $request->username;
        $password = $request->password_ppp;
        $serviceName = PppType::where('id', $request->ppp_type_id)->value('name');
        $callerID = $request->caller_id;
        if ($request->save_with_last_caller_id) {
            $callerID = $request->last_caller_id;
        }

        $router = Mikrotik::where('id', $customer->mikrotik_id)->first();
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
            try {
                $this->testConnectionService->checkOnline($router);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.']);
            }
            $username = $customer->username;
            dispatch(new DeletePppSecretJob($router, $username))->onQueue('default');
        }

        //Backup Customer deleted
        DeletedCustomer::create([
            'name' => $customer->name,
            'slug' => $customer->slug,
            'password' => $customer->password,
            'email' => $customer->email,
            'paket_id' => $customer->paket->name,
            'mikrotik_id' => $customer->mikrotik->name,
            'secret_id' => $customer->secret_id,
            'username' => $customer->username,
            'password_ppp' => $customer->password_ppp,
            'ppp_type_id' => $customer->ppp_type->name,
            'address' => $customer->address,
            'contact' => $customer->contact
        ]);

        $customer->delete();
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
