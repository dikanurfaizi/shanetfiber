<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer;
use App\Models\Mikrotik;

use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Customers\CustomerService;
use App\Services\Customers\CustomerDataService;
use App\Services\Customers\CustomerStatusService;
use App\Services\Customers\CustomerFormService;
use App\Services\Customers\CustomerPppService;
use App\Services\Customers\CustomerRouterService;
use App\Services\Customers\CustomerViewService;
use App\Services\Customers\CustomerUpdateService;
use App\Services\Customers\CustomerActivationService;

use Illuminate\Http\Request;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\CustomerPppUpdateRequest;
use App\Http\Requests\CustomerRouterUpdateRequest;
use App\Helpers\UserAuth;
use App\Helpers\LogHelper;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConnectException;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    private SecretService $secretService;
    private CustomerService $customerService;
    private CustomerDataService $customerDataService;
    private CustomerStatusService $customerStatusService;
    private CustomerFormService $customerFormService;
    private CustomerPppService $customerPppService;
    private CustomerRouterService $customerRouterService;
    private CustomerViewService $customerViewService;
    private CustomerUpdateService $customerUpdateService;
    private CustomerActivationService $customerActivationService;

    public function __construct(
        SecretService $secretService,
        CustomerService $customerService,
        CustomerDataService $customerDataService,
        CustomerStatusService $customerStatusService,
        CustomerFormService $customerFormService,
        CustomerPppService $customerPppService,
        CustomerRouterService $customerRouterService,
        CustomerViewService $customerViewService,
        CustomerUpdateService $customerUpdateService,
        CustomerActivationService $customerActivationService
    ) {
        $this->secretService = $secretService;
        $this->customerService = $customerService;
        $this->customerDataService = $customerDataService;
        $this->customerStatusService = $customerStatusService;
        $this->customerFormService = $customerFormService;
        $this->customerPppService = $customerPppService;
        $this->customerRouterService = $customerRouterService;
        $this->customerViewService = $customerViewService;
        $this->customerUpdateService = $customerUpdateService;
        $this->customerActivationService = $customerActivationService;

        $this->middleware('permission:customer-list|customer-create|customer-edit|customer-delete|secret-disable|secret-edit', ['only' => ['index', 'show']]);
        $this->middleware('permission:customer-create', ['only' => ['create', 'store', 'activation']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update', 'activation', 'editRouter', 'updateRouter']]);
        $this->middleware('permission:customer-delete', ['only' => ['destroy', 'delete']]);
        $this->middleware('permission:secret-disable', ['only' => ['disable', 'enable']]);
        $this->middleware('permission:secret-edit', ['only' => ['editPpp', 'updatePpp', 'activation_all_customer']]);
    }

    // ... [previous methods remain the same until index()]

    public function index(Request $request)
    {
        $traceId = (string) Str::uuid();
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
            'search_paket',
            'status',
            'search_admin'
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
        $data = $this->customerDataService->getPaginatedCustomers($searchParams, $perPage, $traceId);

        return view('customers.index', [
            'data' => $data,
            'totalIncoming' => $this->calculateTotalIncoming($data, $traceId),
            'address' => $this->customerDataService->getCustomerAddresses($traceId),
            'allServers' => $this->customerDataService->getActiveServers($traceId),
            'allAdmin' => $this->customerDataService->getActiveAdmins($traceId),
            'periodeName' => $this->customerDataService->getCurrentBillingPeriod($traceId),
            'perPage' => $perPage
        ])->with('i', (request()->input('page', 1) - 1) * $perPage);
    }

    private function handleStatusFilter(Request $request, array $searchParams, int $perPage, string $traceId)
    {
        $routers = $this->customerDataService->getActiveServers($searchParams['server_search'] ?? null);

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
        return $customers->sum(
            fn($customer) =>
            $customer->paket->price * ((100 - $customer->discount) / 100)
        );
    }

    private function getUserFriendlyError(\Exception $e): string
    {
        return match (true) {
            $e instanceof \RuntimeException => $e->getMessage(),
            $e instanceof ClientException => "Server configuration error",
            $e instanceof ConnectException => "Cannot connect to server",
            default => "Operation failed. Please try again"
        };
    }

    public function create()
    {
        $traceId = (string) Str::uuid();
        return view(
            'customers.create',
            $this->customerFormService->getCreateFormData($traceId)
        );
    }

    public function store(CustomerStoreRequest $request)
    {
        $traceId = (string) Str::uuid();
        try {
            $customer = $this->customerService->createCustomer($request->validated(), $traceId);

            LogHelper::logInfo(null, [
                'request' => $request->only([
                    'name',
                    'email',
                    'discount',
                    'paket_id',
                    'contact',
                    'address',
                    'member_id',
                    'user_id',
                    'billing_option',
                    'periode_billing',
                    'activation_date'
                ]),
                'customer' => $customer
            ], 200, 'Successfully created customer', $traceId);

            return redirect()->route('customers.index')
                ->with('success', 'Customer added successfully. Please activate first to create user ppp on server ' .
                    ($customer->mikrotik->name ?? 'router') . '!');
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'request' => $request,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed to create customer', $traceId);

            return redirect()->back()
                ->withInput()
                ->withErrors(['msg' => 'Failed to create customer: ' . $e->getMessage()]);
        }
    }

    public function show(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logInfo(null, [
            'customer' => $customer
        ], 200, 'Successfuly fetch data customer', $traceId);
        return view('customers.show', $this->customerViewService->getCustomerViewData($customer, $traceId));
    }

    public function edit(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        LogHelper::logInfo(null, [
            'customer' => $customer
        ], 200, 'Successfuly fetch data customer', $traceId);
        return view(
            'customers.edit',
            $this->customerFormService->getEditFormData($customer, $traceId)
        );
    }

    public function editPpp(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        try {
            LogHelper::logInfo(null, [
                'customer' => $customer
            ], 200, 'Successfuly fetch data customer', $traceId);
            return view(
                'customers.editppp',
                array_merge(
                    ['customer' => $customer],
                    $this->customerPppService->getPppEditData($customer, $traceId)
                )
            );
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Failed show data for edit PPP/IP Static', $traceId);

            return redirect()->back()
                ->withErrors(['msg' => 'Cannot connect to server']);
        }
    }

    public function editRouter(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        try {
            LogHelper::logInfo(null, [
                'customer' => $customer
            ], 200, 'Successfuly fetch data customer', $traceId);
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
            ], 500, 'Failed show data for edit router', $traceId);

            return redirect()->back()
                ->withErrors(['msg' => 'Cannot connect to server']);
        }
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        $traceId = (string) Str::uuid();
        try {
            $action = $request->input('action', 'update_data');
            $this->customerUpdateService->updateCustomer(
                $customer,
                $request->validated(),
                $traceId,
                $action
            );

            LogHelper::logInfo(null, [
                'request' => $request->only([
                    'name',
                    'email',
                    'discount',
                    'paket_id',
                    'contact',
                    'address',
                    'member_id',
                    'user_id',
                    'billing_option',
                    'periode_billing',
                    'activation_date'
                ]),
                'customer' => $customer
            ], 200, 'Successfuly Update Customer Data', $traceId);

            return redirect()->back()
                ->with('success', 'Customer ' . $customer->name . ' updated!');
        } catch (\RuntimeException $e) {

            LogHelper::logError(null, [
                'request' => $request->only([
                    'name',
                    'email',
                    'discount',
                    'paket_id',
                    'contact',
                    'address',
                    'member_id',
                    'user_id',
                    'billing_option',
                    'periode_billing',
                    'activation_date'
                ]),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Customer update failed', $traceId);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'request' => $request->only([
                    'name',
                    'email',
                    'discount',
                    'paket_id',
                    'contact',
                    'address',
                    'member_id',
                    'user_id',
                    'billing_option',
                    'periode_billing',
                    'activation_date'
                ]),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Customer update failed', $traceId);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update customer. Please try again.');
        }
    }

    public function updatePpp(CustomerPppUpdateRequest $request, Customer $customer)
    {
        $traceId = (string) Str::uuid();

        try {
            $this->customerPppService->updatePppData($customer, $request->validated(), $traceId);

            LogHelper::logInfo(null, [
                'request' => $request->only(
                    $customer->internet_service === 'ppp'
                        ? ['username', 'ppp_type_id'] // exclude password
                        : ['ip_static', 'mac_address', 'interface']
                ),
                'customer' => $customer,
            ], 200, 'Successfully updated PPP/IP Static', $traceId);

            return redirect()->back()
                ->with('success', $customer->internet_service === 'ppp'
                    ? 'PPP updated successfully'
                    : 'IP Static updated successfully');
        } catch (\RuntimeException $e) {

            LogHelper::logError(null, [
                'request' => $request->only(
                    $customer->internet_service === 'ppp'
                        ? ['username', 'ppp_type_id']
                        : ['ip_static', 'mac_address', 'interface']
                ),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to update PPP/IP Static (RuntimeException)', $traceId);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {

            LogHelper::logError(null, [
                'request' => $request->only(
                    $customer->internet_service === 'ppp'
                        ? ['username', 'ppp_type_id']
                        : ['ip_static', 'mac_address', 'interface']
                ),
                'customer' => $customer,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to update PPP/IP Static (Exception)', $traceId);

            return redirect()->back()
                ->withInput()
                ->with('error', 'System error occurred. Please try again later.');
        }
    }

    public function updateRouter(CustomerRouterUpdateRequest $request, Customer $customer)
    {
        $traceId = (string) Str::uuid();
        try {
            $this->customerRouterService->updateRouterData($customer, $request->validated(), $traceId);

            LogHelper::logInfo(null, [
                'request' => $request->only([
                    'merk_ont',
                    'port',
                ]),
                'customer' => $customer
            ], 200, 'Successfuly Update Router Customer', $traceId);

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
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to update router (RuntimeException)', $traceId);

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
                'trace' => $e->getTraceAsString()
            ], 500, 'Failed to update router (Exception)', $traceId);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update router. Please try again.');
        }
    }

    public function activation(Customer $customer): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerActivationService->activateCustomer($customer, $traceId);

        if (isset($result['error'])) {
            LogHelper::logError(null, [
                'customer' => $customer,
                'error' => Str::limit($result['error'], 250)
            ], 400, 'Failed to activate customer', $traceId);

            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        LogHelper::logInfo(null, [
            'customer' => $customer
        ], 200, 'Successfully activated customer', $traceId);

        return redirect()->back()->with('success', $result['success']);
    }


    public function activation_all_customer(Request $request): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $mikrotikId = $request->servers_activation;

        $result = $this->customerActivationService->activateAllCustomers($mikrotikId, $traceId);

        if (isset($result['error'])) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotikId,
                'error' => $result['error'],
            ], 400, 'Failed to activate all customers', $traceId);

            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        LogHelper::logInfo(null, [
            'mikrotik_id' => $mikrotikId,
            'activated_count' => $result['count'] ?? null,
        ], 200, 'Successfully activated all customers', $traceId);

        return redirect()->back()->with('success', $result['success']);
    }

    public function disable(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerStatusService->disableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function forceDisable(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerStatusService->forceDisableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function enable(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerStatusService->enableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    public function forceEnable(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerStatusService->forceEnableCustomer($customer, $traceId);

        return $this->handleStatusResponse($result);
    }

    private function handleStatusResponse(array $result): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function delete(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerService->checkCustomerDeletionEligibility($customer, $traceId);

        if (!$result['eligible']) {
            return redirect()->route('billings.index', $result['redirect_data'])
                ->with('warning', $result['message']);
        }

        return view('customers.delete', ['customer' => $result['customer']]);
    }

    public function exportSecret(Customer $customer): RedirectResponse
    {
        $traceId = (string) Str::uuid();
        $result = $this->customerActivationService->exportCustomerSecret($customer, $traceId);

        if (isset($result['error'])) {
            return redirect()->back()->withErrors(['msg' => $result['error']]);
        }

        return redirect()->back()->with('success', $result['success']);
    }

    public function export()
    {
        $traceId = (string) Str::uuid();
        $timestamp = now()->format('Ymd_His'); // Format: YYYYMMDD_HHMMSS
        $fileName = "customers_{$timestamp}.xlsx"; // Contoh: customers_20250325_143530.xlsx

        return Excel::download(new CustomersExport, $fileName);
    }

    public function trashed(Request $request)
    {
        $traceId = (string) Str::uuid();
        $perPage = $request->input('perPage', 25);
        $searchParams = $request->only([
            'search_name',
            'search_address',
            'server_search',
            'search_paket',
            'status',
            'search_admin'
        ]);

        $data = $this->customerService->getTrashedCustomers($searchParams, $perPage, $traceId);

        return view('customers.trashed', [
            'data' => $data['customers'],
            'address' => $data['addresses'],
            'allServers' => $data['servers'],
            'allAdmin' => $data['admins'],
            'periodeName' => $data['periodeName'],
            'perPage' => $data['perPage']
        ])->with('i', ($request->input('page', 1) - 1) * $perPage);
    }

    public function restore($slug)
    {
        $traceId = (string) Str::uuid();
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
        $traceId = (string) Str::uuid();
        $result = $this->customerService->forceDeleteCustomer($slug, $traceId);

        if (!$result['success']) {
            LogHelper::logError(
                null,
                ['slug' => $slug, 'error' => $result['error'] ?? null],
                $result['status'],
                $result['message']
            );

            return redirect()->back()
                ->withErrors(['msg' => $result['message']]);
        }

        LogHelper::logInfo(
            null,
            ['slug' => $slug],
            $result['status'],
            $result['message']
        );

        return redirect()->route('customers.trashed')
            ->with('success', $result['message']);
    }

    public function getPppSecretsActiveByName(Request $request, Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
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
}
