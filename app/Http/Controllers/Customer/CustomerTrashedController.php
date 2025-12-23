<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Helpers\LogHelper;
use App\Services\Customers\CustomerDeletionService;
use App\Services\Customers\CustomerQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerTrashedController extends Controller
{
    public function __construct(
        private CustomerQueryService $customerQueryService,
        private CustomerDeletionService $customerDeletionService
    ) {
        $this->middleware('permission:view-deleted-customer', ['only' => ['index', 'show']]);
        $this->middleware('permission:force-deleted-customer', ['only' => ['forceDelete']]);
    }

    /**
     * Display list of trashed customers.
     *
     * Responsibility:
     * - Read-only view
     * - No mutation
     * - No restore logic
     */
    public function __invoke(Request $request)
    {
        $traceId = (string) Str::uuid();
        $perPage = $request->input('perPage', 25);

        $customers = Customer::onlyTrashed()
            ->with(['subscriptions'])
            ->orderByDesc('deleted_at')
            ->paginate(20);

        $searchParams = $request->only([
            'search_name',
            'search_address',
            'server_search',
            'search_package',
            'status',
            'search_admin',
        ]);

        $data = $this->customerQueryService->getTrashedCustomers($searchParams, $perPage, $traceId);

        return view('customers.trashed', [
            'data' => $data['customers'],
            'address' => $data['addresses'],
            'allServers' => $data['servers'],
            'allAdmin' => $data['admins'],
            'periodeName' => $data['periodeName'],
            'perPage' => $data['perPage'],
            'allPackages' => $data['allPackages']
        ])->with('i', ($request->input('page', 1) - 1) * $perPage);
    }

    /**
     * Permanently delete customer.
     *
     * WARNING:
     * - This action is irreversible
     * - Must be explicitly authorized
     * - Must be transactional
     */
    public function forceDelete(Customer $customer)
    {
        $traceId = (string) Str::uuid();
        $slug = $customer->slug;
        $result = $this->customerDeletionService->forceDeleteCustomer($slug, $traceId);

        if (! $result['success']) {
            LogHelper::logError(
                null,
                ['slug' => $customer->slug, 'error' => $result['error'] ?? null],
                $result['status'],
                $result['message'],
                $traceId
            );

            return redirect()->back()
                ->withErrors(['msg' => $result['message']]);
        }

        LogHelper::logInfo(
            null,
            ['slug' => $slug],
            $result['status'],
            $result['message'],
            $traceId
        );

        return redirect()->route('customers.trashed')
            ->with('success', $result['message']);
    }
}
