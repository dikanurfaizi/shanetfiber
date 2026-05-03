<?php

namespace App\Http\Controllers\Customer;

use App\Exports\CustomersExport;
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerPppUpdateRequest;
use App\Http\Requests\CustomerRouterUpdateRequest;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Services\Customers\CustomerActivationService;
use App\Services\Customers\CustomerDeletionService;
use App\Services\Customers\CustomerFormService;
use App\Services\Customers\CustomerPppService;
use App\Services\Customers\CustomerRouterService;
use App\Services\Customers\CustomerBillingInfoService;
use App\Services\Customers\CustomerListingService;
use App\Services\Customers\CustomerStatusService;
use App\Services\Customers\CustomerUpdateService;
use App\Services\Customers\CustomerQueryService;
use App\Services\Customers\CustomerRestoreService;
use App\Services\Routerboard\Ppp\SecretService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConnectException;
use App\Events\CustomerActivated;
use App\Exports\CustomerMasterExport;
use App\Helpers\ExecutionTimer;
use App\Services\Customers\CustomerSoftDeleteService;
use App\Services\Customers\CustomerSubscriptionGuardService;
use DomainException;

class CustomerController extends Controller
{
    private SecretService $secretService;
    private CustomerStatusService $customerStatusService;
    private CustomerFormService $customerFormService;
    private CustomerPppService $customerPppService;
    private CustomerRouterService $customerRouterService;
    private CustomerUpdateService $customerUpdateService;
    private CustomerActivationService $customerActivationService;
    private CustomerDeletionService $customerDeletionService;
    private CustomerListingService $customerListingService;
    private CustomerBillingInfoService $customerBillingInfoService;
    private CustomerQueryService $customerQueryService;
    private CustomerRestoreService $customerRestoreService;
    private CustomerSoftDeleteService $customerSoftDeleteService;
    private CustomerSubscriptionGuardService $subscriptionGuard;

    public function __construct(
        SecretService $secretService,
        CustomerStatusService $customerStatusService,
        CustomerFormService $customerFormService,
        CustomerPppService $customerPppService,
        CustomerRouterService $customerRouterService,
        CustomerUpdateService $customerUpdateService,
        CustomerActivationService $customerActivationService,
        CustomerDeletionService $customerDeletionService,
        CustomerListingService $customerListingService,
        CustomerBillingInfoService $customerBillingInfoService,
        CustomerQueryService $customerQueryService,
        CustomerRestoreService $customerRestoreService,
        CustomerSoftDeleteService $customerSoftDeleteService,
        CustomerSubscriptionGuardService $subscriptionGuard
    ) {
        $this->secretService = $secretService;
        $this->customerStatusService = $customerStatusService;
        $this->customerFormService = $customerFormService;
        $this->customerPppService = $customerPppService;
        $this->customerRouterService = $customerRouterService;
        $this->customerUpdateService = $customerUpdateService;
        $this->customerActivationService = $customerActivationService;
        $this->customerDeletionService = $customerDeletionService;
        $this->customerListingService = $customerListingService;
        $this->customerBillingInfoService = $customerBillingInfoService;
        $this->customerQueryService = $customerQueryService;
        $this->customerRestoreService = $customerRestoreService;
        $this->customerSoftDeleteService = $customerSoftDeleteService;
        $this->subscriptionGuard = $subscriptionGuard;

        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete|secret-disable|secret-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store', 'activation']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update', 'activation', 'editRouter', 'updateRouter']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy', 'delete']]);
        $this->middleware('permission:secret-disable', ['only' => ['disable', 'enable']]);
        $this->middleware('permission:secret-edit', ['only' => ['editPpp', 'updatePpp', 'activation_all_customer']]);
        $this->middleware('permission:customer-trashed', ['only' => ['trashed', 'restore', 'forceDelete']]);
    }

    private function startTrace(): array
    {
        return [
            'traceId' => (string) Str::uuid(),
            'timer'   => new ExecutionTimer(),
        ];
    }

    public function index(Request $request)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $perPage = $request->input('perPage', 25);
        $searchParams = $this->prepareSearchParams($request, $traceId);

        // Handle status filter case
        if ($this->shouldFilterByStatus($searchParams, $traceId)) {
            return $this->handleStatusFilter($request, $searchParams, $perPage, $traceId);
        }

        // Default case - regular listing
        return $this->showCustomerList($searchParams, $perPage, $traceId);
    }

    private function prepareSearchParams(Request $request, string $traceId): array
    {
        $auth = UserAuth::getUserDetails();
        $user = $auth['user'];

        $searchParams = $request->only([
            'search_name',
            'search_address',
            'server_search',
            'search_package',
            'status',
            'search_admin',
        ]);

        // Auto-set admin filter if user has permission
        if (empty($searchParams['search_admin']) && $user && $user->can('view-customers-widget-related')) {
            $searchParams['search_admin'] = $user->id;
        }

        return $searchParams;
    }

    private function shouldFilterByStatus(array $searchParams, string $traceId): bool
    {
        return in_array($searchParams['status'] ?? null, ['isolir', 'offline', 'online']);
    }

    private function showCustomerList(array $searchParams, int $perPage, string $traceId)
    {
        $data = $this->customerListingService->getPaginatedCustomers($searchParams, $perPage, $traceId);

        return view('customers.index', [
            'data' => $data,
            // 'totalIncoming' => $this->calculateTotalIncoming($data, $traceId),
            'address' => $this->customerListingService->getCustomerAddresses($traceId),
            'allServers' => $this->customerListingService->getActiveServers($traceId),
            'allAdmin' => $this->customerListingService->getActiveAdmins($traceId),
            'periodeName' => $this->customerBillingInfoService->getCurrentBillingPeriod($traceId),
            'perPage' => $perPage,
        ])->with('i', (request()->input('page', 1) - 1) * $perPage);
    }

    private function handleStatusFilter(Request $request, array $searchParams, int $perPage, string $traceId)
    {
        $routers = $this->customerListingService->getActiveServers($searchParams['server_search'] ?? null);

        if ($routers->isEmpty()) {
            return redirect()->route('customers.index')
                ->withErrors(['msg' => 'No active servers available']);
        }

        try {
            $result = $this->customerStatusService->processStatusFilter(
                $routers,
                $searchParams,
                $perPage,
                $request->url(),
                $traceId
            );

            return view('customers.offline', $result);
        } catch (\Exception $e) {
            return redirect()->route('customers.index')
                ->withErrors(['msg' => $this->getUserFriendlyError($e)]);
        }
    }

    private function calculateTotalIncoming($customers, $traceId): float
    {
        return $customers->sum(fn($customer) => $customer->estimated_monthly_incoming);
    }

    private function getUserFriendlyError(\Exception $e): string
    {
        return match (true) {
            $e instanceof \RuntimeException => $e->getMessage(),
            $e instanceof ClientException => 'Server configuration error',
            $e instanceof ConnectException => 'Cannot connect to server',
            default => 'Operation failed. Please try again'
        };
    }

    public function create()
    {
        return redirect()->route('wizard.step1');
    }

    public function show(Customer $customer)
    {
        $customers = Customer::withTrashed()->where('id', $customer->id)
            ->firstOrFail();
        return view('customers.show', ['customer' => $customers]);
    }

    public function edit(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        LogHelper::logInfo(null, [
            'customer' => $customer,
        ], 200, 'Successfuly fetch data customer', $traceId, $timer->lap());

        return view(
            'customers.edit',
            $this->customerFormService->getEditFormData($customer, $traceId)
        );
    }

    public function editPpp(Customer $customer)
    {
        $callerID = request('caller_id');
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        try {
            LogHelper::logInfo(null, [
                'customer' => $customer,
            ], 200, 'Successfuly fetch data customer', $traceId, $timer->lap());

            return view(
                'customers.editppp',
                array_merge(
                    ['customer' => $customer, 'callerID' => $callerID],
                    $this->customerPppService->getPppEditData($customer, $traceId)
                )
            );
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed show data for edit PPP/IP Static', $traceId, $timer->lap());

            return redirect()->back()
                ->withErrors(['msg' => 'Cannot connect to server']);
        }
    }

    public function editRouter(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        try {
            LogHelper::logInfo(null, [
                'customer' => $customer,
            ], 200, 'Successfuly fetch data customer', $traceId, $timer->lap());

            return view(
                'customers.edit_router',
                array_merge(
                    ['customer' => $customer],
                    $this->customerRouterService->getRouterEditData($customer, $traceId)
                )
            );
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed show data for edit router', $traceId, $timer->lap());

            return redirect()->back()
                ->withErrors(['msg' => 'Cannot connect to server']);
        }
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            $action = $request->input('action', 'update_data');

            $result = $this->customerUpdateService->updateCustomer(
                $customer,
                $request->validated(),
                $traceId,
                $action
            );

            return redirect()->back()
                ->withInput()
                ->with($result['success'] ? 'success' : 'error', $result['message']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update customer. Please try again.');
        }
    }

    public function updatePpp(CustomerPppUpdateRequest $request, Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            // ✅ Tangkap return dari service
            $result = $this->customerPppService->updatePppData($customer, $request->validated(), $traceId);

            LogHelper::logInfo(
                null,
                [
                    'request' => $request->only(
                        $customer->internet_service === 'ppp'
                            ? ['username', 'ppp_type_id'] // exclude password
                            : ['ip_static', 'mac_address', 'interface']
                    ),
                    'customer' => $customer,
                    'service_result' => $result,
                ],
                200,
                'Successfully updated PPP/IP Static',
                $traceId,
                $timer->lap()
            );

            // ✅ Pakai pesan dari service jika ada
            return redirect()->back()
                ->with('success', $result['message'] ?? (
                    $customer->internet_service === 'ppp'
                    ? 'PPP updated successfully'
                    : 'IP Static updated successfully'
                ));
        } catch (\RuntimeException $e) {

            LogHelper::logError(
                null,
                [
                    'request' => $request->only(
                        $customer->internet_service === 'ppp'
                            ? ['username', 'ppp_type_id']
                            : ['ip_static', 'mac_address', 'interface']
                    ),
                    'customer' => $customer,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                500,
                'Failed to update PPP/IP Static (RuntimeException)',
                $traceId,
                $timer->lap()
            );

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {

            LogHelper::logError(
                null,
                [
                    'request' => $request->only(
                        $customer->internet_service === 'ppp'
                            ? ['username', 'ppp_type_id']
                            : ['ip_static', 'mac_address', 'interface']
                    ),
                    'customer' => $customer,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                500,
                'Failed to update PPP/IP Static (Exception)',
                $traceId,
                $timer->lap()
            );

            return redirect()->back()
                ->withInput()
                ->with('error', 'System error occurred. Please try again later.');
        }
    }

    public function updateRouter(CustomerRouterUpdateRequest $request, Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            $this->customerRouterService->updateRouterData($customer, $request->validated(), $traceId);

            LogHelper::logInfo(null, [
                'request' => $request->only([
                    'merk_ont',
                    'port',
                ]),
                'customer' => $customer,
            ], 200, 'Successfuly Update Router Customer', $traceId, $timer->lap());

            return redirect()->back()
                ->with('success', 'Router updated successfully');
        } catch (\RuntimeException $e) {

            LogHelper::logError(null, [
                'request' => $request->only(
                    'merk_ont',
                    'port',
                ),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to update router (RuntimeException)', $traceId, $timer->lap());

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'request' => $request->only(
                    'merk_ont',
                    'port',
                ),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to update router (Exception)', $traceId, $timer->lap());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update router. Please try again.');
        }
    }

    public function activation(Customer $customer): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        try {
            $this->subscriptionGuard->assertReadyForActivation($customer);
        } catch (DomainException $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        }

        $result = $this->customerActivationService->activateCustomer($customer, $traceId);

        if (isset($result['error'])) {
            LogHelper::logError(null, [
                'customer' => $customer,
                'error' => Str::limit($result['error'], 250),
            ], 400, 'Failed to activate customer', $traceId, $timer->lap());

            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        if (isset($result['success'])) {
            $membershipId = $result['membership']->id ?? null;
            event(
                new CustomerActivated(
                    $membershipId,
                    $result['subscription'],
                    $result['billing'],
                    $result['receipt'],
                    null,
                    $result['traceId']
                )
            );
        }

        LogHelper::logInfo(null, [
            'customerId' => $result['customer']->id,
            'billingId'  => $result['billing']->id,
            'billingPaymentId' => $result['billingPayment']->id,
            'receiptId'  => $result['receipt']->id,
        ], 200, 'Successfully activated customer', $traceId, $timer->lap());

        return redirect()->back()->with('success', 'Successfully activated customer');
    }

    public function activation_all_customer(Request $request): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $mikrotikId = $request->servers_activation;

        $result = $this->customerActivationService->activateAllCustomers($mikrotikId, $traceId);

        if (isset($result['error'])) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotikId,
                'error' => $result['error'],
            ], 400, 'Failed to activate all customers', $traceId, $timer->lap());

            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        LogHelper::logInfo(null, [
            'mikrotik_id' => $mikrotikId,
            'activated_count' => $result['count'] ?? null,
        ], 200, 'Successfully activated all customers', $traceId, $timer->lap());

        return redirect()->back()->with('success', $result['success']);
    }

    public function disable(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerStatusService->disableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function forceDisable(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerStatusService->forceDisableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function enable(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerStatusService->enableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function forceEnable(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerStatusService->forceEnableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    private function handleStatusResponse(array $result): RedirectResponse
    {
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function delete(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerDeletionService->checkCustomerDeletionEligibility($customer, $traceId);

        if (! $result['eligible']) {
            return redirect()->route('billings.index', $result['redirect_data'])
                ->with('warning', $result['message']);
        }

        return view('customers.delete', ['customer' => $result['customer']]);
    }

    public function exportSecret(Customer $customer): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $result = $this->customerActivationService->exportCustomerSecret($customer, $traceId);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function export()
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        $timestamp = now()->format('Ymd_His'); // Format: YYYYMMDD_HHMMSS
        $fileName = "Customers_{$timestamp}.xlsx"; // Contoh: customers_20250325_143530.xlsx

        return Excel::download(new CustomerMasterExport, $fileName);
    }

    public function getPppSecretsActiveByName(Request $request, Mikrotik $mikrotik)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        try {
            $pppActive = $this->secretService->getPppSecretActiveByName($mikrotik, $traceId);
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

    public function destroy(Customer $customer)
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();
        try {
            $this->customerSoftDeleteService->deleteCustomer($customer, $traceId);

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
}
