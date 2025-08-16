<?php

namespace App\Services\Customers;

use App\Models\Customer;
use App\Models\Billing\Billing;
use App\Models\Billing\BillingPeriode;
use App\Models\Paket\Paket;
use App\Models\DeletedCustomer;
use App\Models\Receipt;
use App\Models\Websystem;
use App\Models\CustomerOnt;
use App\Models\Mikrotik;
use App\Models\User;
use App\Models\AutoIsolir;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Services\Customers\CustomerBillingService;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Jobs\Secrets\DeleteIpStaticJob;

class CustomerService
{
    private CustomerBillingService $customerBillingService;
    private TestConnectionService $testConnectionService;
    private SecretService $secretService;

    public function __construct(
        CustomerBillingService $customerBillingService,
        TestConnectionService $testConnectionService,
        SecretService $secretService
    ) {
        $this->customerBillingService = $customerBillingService;
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
    }

    public function generateCredential(string $name, string $traceId): array
    {
        try {
            $today = Carbon::today();
            $countToday = Customer::whereDate('created_at', $today)->count();
            $activationNumber = str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);
            $datePrefix = $today->format('Ymd') . $activationNumber;
            $nameSlug = strtolower(Str::slug($name));

            $credentials = [
                'username' => $datePrefix . '@' . $nameSlug,
                'password' => $datePrefix,
            ];

            LogHelper::logInfo(
                null,
                ['name' => $name, 'credentials' => $credentials],
                200,
                'Successfully generated credentials',
                $traceId
            );

            return $credentials;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'name' => $name
                ],
                500,
                'Failed to generate credentials',
                $traceId
            );
            throw $e;
        }
    }

    public function generateCustomerId(Customer $customer, int $mikrotikId, string $traceId): void
    {
        try {
            $websystem = Websystem::where('slug', 'websystem')->first();
            $longCode = sprintf('%%0%dd', $websystem->long_customer_code);
            $customerId = sprintf(
                '%s%02d-%s',
                $websystem->customer_code,
                $mikrotikId,
                sprintf($longCode, $customer->id)
            );

            $customer->update(['customer_id' => $customerId]);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'generated_id' => $customerId
                ],
                200,
                'Successfully generated customer ID',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'mikrotik_id' => $mikrotikId
                ],
                500,
                'Failed to generate customer ID',
                $traceId
            );
            throw $e;
        }
    }

    public function createBilling(Customer $customer, int $periodeId, string $periodeName, string $traceId): void
    {
        try {
            $month = Carbon::parse($periodeName)->format('m');
            $year = Carbon::parse($periodeName)->format('Y');
            $paket_price = $customer->paket->price;
            $discount = optional($customer->member)->discount ?? 0;
            $total_price = $paket_price;

            if ($customer->billing_option == 2 && $customer->billing_period === $periodeName) {
                $total_price -= $discount;
            } elseif ($customer->billing_option == 1) {
                $total_price -= $discount;
            }

            $billing = Billing::create([
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

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'billing_id' => $billing->id,
                    'amount' => $total_price
                ],
                200,
                'Successfully created billing',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'periode_id' => $periodeId
                ],
                500,
                'Failed to create billing',
                $traceId
            );
            throw $e;
        }
    }

    public function createReceipt(Customer $customer, Billing $billing, string $traceId): Receipt
    {
        try {
            $getLastReceipt = Receipt::latest('created_at')->value('number');
            $numberReceipt = $getLastReceipt ? (int) explode('-', $getLastReceipt)[1] + 1 : 1;

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

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'receipt_id' => $receipt->id,
                    'amount' => $receipt->paket_price
                ],
                200,
                'Successfully created receipt',
                $traceId
            );

            return $receipt;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'billing_id' => $billing->id
                ],
                500,
                'Failed to create receipt',
                $traceId
            );
            throw $e;
        }
    }

    public function moveToDeleted(Customer $customer, string $traceId): DeletedCustomer
    {
        try {
            $deletedCustomer = DeletedCustomer::create([
                'id' => $customer->id,
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
                'contact' => $customer->contact,
                'disabled' => $customer->disabled,
                'activation' => $customer->activation,
                'created_at' => $customer->created_at,
                'updated_at' => $customer->updated_at,
                'deleted_at' => now(),
                'primary_server' => $customer->primary_server,
                'discount' => $customer->discount,
                'customer_id' => $customer->customer_id,
                'activation_date' => $customer->activation_date,
                'caller_id' => $customer->caller_id,
                'user_id' => $customer->user_id,
                'member_id' => $customer->member_id,
                'billing_option' => $customer->billing_option,
                'billing_period' => $customer->billing_period,
            ]);

            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                'Successfully moved customer to deleted table',
                $traceId
            );

            return $deletedCustomer;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to move customer to deleted table',
                $traceId
            );
            throw $e;
        }
    }

    public function hasUnpaidBills(int $customerId, string $traceId): bool
    {
        try {
            $exists = Billing::where('customer_id', $customerId)
                ->whereNotIn('status', ['NP', 'LS'])
                ->exists();

            if ($exists) {
                LogHelper::logInfo(
                    null,
                    ['customer_id' => $customerId],
                    200,
                    'Customer has unpaid bills',
                    $traceId
                );
            }

            return $exists;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customerId
                ],
                500,
                'Failed to check unpaid bills',
                $traceId
            );
            throw $e;
        }
    }


    public function createCustomer(array $data, string $traceId): Customer
    {
        try {
            if (empty($data['password']) || trim($data['password']) === '' || $data['password'] == null) {
                $data['password'] = '12345678';
            }
            $customerData = $this->prepareCustomerData($data, $traceId);
            $customer = Customer::create($customerData);

            $this->generateCustomerId($customer, $data['mikrotik_id'], $traceId);
            $this->createCustomerOnt($customer, $traceId);
            $this->createInitialBilling($customer, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                    'service_type' => $customer->internet_service
                ],
                201,
                'Customer created successfully',
                $traceId
            );

            return $customer;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_data' => [
                        'name' => $data['name'] ?? null,
                        'email' => $data['email'] ?? null
                    ]
                ],
                500,
                'Failed to create customer',
                $traceId
            );
            throw $e;
        }
    }

    private function prepareCustomerData(array $data, string $traceId): array
    {
        $formatter = new FormatReceiver();
        $creds = $this->generateCredential(explode(' ', $data['name'])[0], $traceId);

        return $this->addServiceSpecificData([
            'name' => $data['name'],
            'email' => $data['email'],
            'slug' => Str::slug(explode(' ', $data['name'])[0] . '-' . Str::random(6)),
            'password' => Hash::make($data['password']),
            'address' => $data['address'],
            'contact' => $formatter->formatReceiver($data['contact'], null, false),
            'internet_service' => $data['internet_service'],
            'primary_server' => $data['mikrotik_id'],
            'discount' => $data['discount'] ?? 0,
            'mikrotik_id' => $data['mikrotik_id'],
            'paket_id' => Paket::findOrFail($data['paket_id'])->id,
            'username' => $creds['username'],
            'user_id' => $data['user_id'],
            'member_id' => $data['member_id'],
            'billing_option' => $data['billing_option'] ?? '0',
            'billing_period' => $this->getBillingPeriod($data['billing_option'] ?? '0', $traceId)
        ], $data, $creds, $traceId);
    }

    private function addServiceSpecificData(array $customerData, array $requestData, array $creds, string $traceId): array
    {
        if ($requestData['internet_service'] === 'ppp') {
            $customerData['password_ppp'] = $creds['password'];
            $customerData['ppp_type_id'] = $requestData['ppp_type_id'];
        } elseif ($requestData['internet_service'] === 'ip_static') {
            $customerData += [
                'simpleque_name' => $creds['username'],
                'ip_static' => $requestData['ip_static'],
                'mac_address' => $requestData['mac_address'],
                'interface' => $requestData['interface']
            ];
        }

        return $customerData;
    }

    private function getBillingPeriod(string $billingOption, string $traceId): ?string
    {
        return $billingOption !== '0'
            ? BillingPeriode::latest('created_at')->first()->name
            : null;
    }

    private function createCustomerOnt(Customer $customer, string $traceId): void
    {
        try {
            CustomerOnt::create(['customer_id' => $customer->id]);
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to create customer ONT',
                $traceId
            );
            throw $e;
        }
    }

    public function initialBilling(Customer $customer, string $traceId): void
    {
        $this->createInitialBilling($customer, $traceId);
    }

    private function createInitialBilling(Customer $customer, string $traceId): void
    {
        try {
            $periode = BillingPeriode::latest('created_at')->firstOrCreate(
                ['name' => now()->format('F Y')],
                ['name' => now()->format('F Y')]
            );

            $this->customerBillingService->createInitialBilling($customer, $periode, $traceId);
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to create initial billing',
                $traceId
            );
            throw $e;
        }
    }

    public function checkCustomerDeletionEligibility(Customer $customer, string $traceId): array
    {
        try {
            $hasUnpaidBills = $this->hasUnpaidBills($customer->id, $traceId);

            if ($hasUnpaidBills) {
                LogHelper::logWarning(
                    null,
                    ['customer_id' => $customer->id],
                    400,
                    'Customer deletion blocked due to unpaid bills',
                    $traceId
                );

                return [
                    'eligible' => false,
                    'redirect' => true,
                    'message' => 'Customer memiliki tagihan aktif. Harap konfirmasi status billing sebelum melanjutkan penghapusan.',
                    'redirect_data' => [
                        'search_name' => $customer->name,
                        'source' => 'customer-delete',
                        'customer_name' => $customer->name,
                    ]
                ];
            }

            return [
                'eligible' => true,
                'customer' => $customer
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id
                ],
                500,
                'Failed to check deletion eligibility',
                $traceId
            );
            throw $e;
        }
    }

    public function getTrashedCustomers(array $searchParams, int $perPage = 25, string $traceId): array
    {
        try {
            // Log the start of operation with all search parameters
            $activeFilters = array_filter($searchParams, fn($value) => !is_null($value));
            LogHelper::logInfo(
                null,
                [
                    'active_filters' => $activeFilters,
                    'per_page' => $perPage
                ],
                200,
                'Starting trashed customers retrieval with filters',
                $traceId
            );

            $query = Customer::onlyTrashed()->with(['user', 'paket', 'member']);

            // Apply all filters in one go
            $query->when(true, function ($query) use ($searchParams) {
                if (!empty($searchParams['search_name'])) {
                    $query->where('name', 'like', "%{$searchParams['search_name']}%");
                }

                if (!empty($searchParams['search_address'])) {
                    $searchParams['search_address'] === 'NULL'
                        ? $query->whereNull('address')
                        : $query->where('address', $searchParams['search_address']);
                }

                if (!empty($searchParams['search_admin'])) {
                    $searchParams['search_admin'] === 'NULL'
                        ? $query->whereNull('user_id')
                        : $query->where('user_id', $searchParams['search_admin']);
                }

                if (!empty($searchParams['server_search'])) {
                    $query->where('mikrotik_id', $searchParams['server_search']);
                }

                if (!empty($searchParams['search_paket'])) {
                    $query->where('paket_id', $searchParams['search_paket']);
                }
            })->orderBy('name');

            // Execute queries
            $customers = $query->paginate($perPage);
            $addresses = Customer::onlyTrashed()->select('address')->distinct()->pluck('address', 'address');
            $servers = Mikrotik::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');
            $admins = User::where('disabled', 'false')->orderBy('name')->pluck('name', 'id');
            $periodeName = BillingPeriode::latest('created_at')->value('name');

            // Log successful results
            LogHelper::logInfo(
                null,
                [
                    'results' => [
                        'customers' => $customers->total(),
                        'address_options' => count($addresses),
                        'server_options' => count($servers),
                        'admin_options' => count($admins)
                    ]
                ],
                200,
                'Successfully retrieved trashed customers',
                $traceId
            );

            return [
                'customers' => $customers,
                'addresses' => $addresses,
                'servers' => $servers,
                'admins' => $admins,
                'periodeName' => $periodeName,
                'perPage' => $perPage
            ];
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'search_params' => $searchParams
                ],
                500,
                'Failed to retrieve trashed customers',
                $traceId
            );

            return [
                'customers' => collect([]),
                'addresses' => collect([]),
                'servers' => collect([]),
                'admins' => collect([]),
                'periodeName' => '',
                'perPage' => $perPage
            ];
        }
    }

    public function forceDeleteCustomer(string $slug, string $traceId): array
    {
        try {
            $customer = Customer::onlyTrashed()->where('slug', $slug)->firstOrFail();

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'slug' => $slug,
                    'activation_status' => $customer->activation
                ],
                200,
                'Retrieved trashed customer for deletion',
                $traceId
            );

            if ($customer->activation === 'true') {
                try {
                    $this->deactivateCustomerServices($customer, $traceId);

                    LogHelper::logInfo(
                        null,
                        ['customer_id' => $customer->id],
                        200,
                        'Successfully deactivated customer services before deletion',
                        $traceId
                    );
                } catch (\Exception $e) {
                    LogHelper::logError(
                        null,
                        [
                            'exception' => [
                                'message' => $e->getMessage(),
                                'code' => $e->getCode(),
                                'file' => $e->getFile(),
                                'line' => $e->getLine()
                            ],
                            'customer_id' => $customer->id
                        ],
                        500,
                        'Failed to deactivate customer services',
                        $traceId
                    );

                    return [
                        'success' => false,
                        'message' => 'An error occurred while handling the router. Please try again.',
                        'error' => $e->getMessage(),
                        'status' => 500
                    ];
                }
            }

            try {
                $deletedCustomer = $this->moveToDeleted($customer, $traceId);
                $customer->forceDelete();

                LogHelper::logInfo(
                    null,
                    [
                        'customer_id' => $customer->id,
                        'deleted_customer_id' => $deletedCustomer->id ?? null
                    ],
                    200,
                    'Successfully force deleted customer',
                    $traceId
                );

                return [
                    'success' => true,
                    'message' => 'Customer permanently deleted.',
                    'status' => 200
                ];
            } catch (ModelNotFoundException $e) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ],
                        'slug' => $slug
                    ],
                    404,
                    'Customer not found for force deletion',
                    $traceId
                );

                return [
                    'success' => false,
                    'message' => 'Customer not found.',
                    'error' => $e->getMessage(),
                    'status' => 404
                ];
            } catch (\Exception $e) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ],
                        'customer_id' => $customer->id ?? null,
                        'slug' => $slug
                    ],
                    500,
                    'Failed to force delete customer',
                    $traceId
                );

                return [
                    'success' => false,
                    'message' => 'An error occurred while deleting the customer. Please try again.',
                    'error' => $e->getMessage(),
                    'status' => 500
                ];
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'slug' => $slug
                ],
                500,
                'Failed to retrieve customer for deletion',
                $traceId
            );

            return [
                'success' => false,
                'message' => 'An error occurred while processing the request.',
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    private function deactivateCustomerServices(Customer $customer, string $traceId): void
    {
        try {
            $router = Mikrotik::find($customer->mikrotik_id);
            $autoisolir = AutoIsolir::where('mikrotik_id', $customer->mikrotik_id)->first();

            if (!$router) {
                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => 'Router not found',
                            'code' => 404,
                            'file' => __FILE__,
                            'line' => __LINE__
                        ],
                        'customer_id' => $customer->id,
                        'mikrotik_id' => $customer->mikrotik_id
                    ],
                    404,
                    'Router not found for customer',
                    $traceId
                );
                throw new \RuntimeException('Router not found for customer ID: ' . $customer->id);
            }

            $this->handleInternetService($customer, $router, $autoisolir, $traceId);

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'router_id' => $router->id,
                    'autoisolir_id' => $autoisolir->id ?? null
                ],
                200,
                'Successfully deactivated customer services',
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'mikrotik_id' => $customer->mikrotik_id ?? null
                ],
                500,
                'Failed to deactivate customer services',
                $traceId
            );
            throw $e;
        }
    }

    private function handleInternetService(Customer $customer, $router, $autoisolir, string $traceId)
    {
        if (!$router) {
            LogHelper::logError(
                null,
                ['customer_id' => $customer->id],
                404,
                'Router not found for customer',
                $traceId
            );
            throw new \Exception('Router not found for customer ID: ' . $customer->id);
        }

        try {
            $this->testConnectionService->checkOnline($router, $traceId);
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "Connection to router succeeded",
                $traceId
            );

            if ($customer->internet_service === 'ppp') {
                $this->handlePppService($router, $customer->username, $traceId);
            } elseif ($customer->internet_service === 'ip_static') {
                $this->handleIpStaticService($router, $customer, $autoisolir, $traceId);
            }
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to handle internet service',
                $traceId
            );
            throw $e;
        }
    }

    private function handlePppService($router, $username, string $traceId)
    {
        try {
            $this->secretService->deleteSecretByName($router, $username, $traceId);
            $this->secretService->deleteActiveSecret($router, $username, $traceId);
            LogHelper::logInfo(
                null,
                ['username' => $username],
                200,
                "PPP service deleted successfully",
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'username' => $username,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to delete PPP service',
                $traceId
            );
            throw $e;
        }
    }

    private function handleIpStaticService($router, Customer $customer, $autoisolir, string $traceId)
    {
        try {
            $simpleque_id = $customer->simpleque_id;
            $ip_address = $customer->ip_static;
            $arp_id = $customer->arp_id;
            $address_list_name = $autoisolir->address_list_isolir ?? 'none';

            dispatch(new DeleteIpStaticJob($router, $simpleque_id, $arp_id, $ip_address, $address_list_name, $traceId))->onQueue('default');
            LogHelper::logInfo(
                null,
                ['customer_id' => $customer->id],
                200,
                "IP static service deletion queued",
                $traceId
            );
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id,
                    'router_id' => $router->id ?? null
                ],
                500,
                'Failed to queue IP static service deletion',
                $traceId
            );
            throw $e;
        }
    }
}
