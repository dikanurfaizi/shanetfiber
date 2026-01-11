<?php

namespace App\Services\Customers;

use App\Models\Customers\Customer;
use App\Models\Customers\DeletedCustomer;
use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Jobs\Secrets\DeletePppSecretJob;
use App\Jobs\Secrets\DeleteIpStaticJob;
use App\Services\Routerboard\TestConnectionService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Services\Customers\CustomerRouterService;
use App\Services\Billings\BillingUnpaidService;
use App\Helpers\LogHelper;
use App\Models\Customers\CustomerSubscription;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerDeletionService
{
    protected $testConnectionService;
    protected $secretService;
    protected $ipStaticService;
    private BillingUnpaidService $billingUnpaidService;
    private CustomerRouterService $customerRouterService;

    public function __construct(
        TestConnectionService $testConnectionService,
        SecretService $secretService,
        IpStaticService $ipStaticService,
        BillingUnpaidService $billingUnpaidService,
        CustomerRouterService $customerRouterService,
    ) {
        $this->testConnectionService = $testConnectionService;
        $this->secretService = $secretService;
        $this->ipStaticService = $ipStaticService;
        $this->billingUnpaidService = $billingUnpaidService;
        $this->customerRouterService = $customerRouterService;
    }

    public function moveToDeleted(Customer $customer, string $traceId): DeletedCustomer
    {
        try {
            $subscription = CustomerSubscription::where('customer_id', $customer->id)->orderBy('created_at')->first();
            $package = Package::where('id', $subscription->package_id)->first();
            $deletedCustomer = DeletedCustomer::create([
                'id' => $customer->id,
                'name' => $customer->name,
                'slug' => $customer->slug,
                'password' => $customer->password,
                'email' => $customer->email,
                'package_id' => $package->id,
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

    public function checkCustomerDeletionEligibility(Customer $customer, string $traceId): array
    {
        try {
            $hasUnpaidBills = $this->billingUnpaidService->hasUnpaidBills($customer->id, $traceId);

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
                    $this->customerRouterService->deactivateCustomerServices($customer, $traceId);

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
}
