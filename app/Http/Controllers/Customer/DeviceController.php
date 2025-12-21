<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
use App\Services\Customers\CustomerViewService;
use Illuminate\Support\Str;
use App\Services\Routerboard\SystemService;
use Exception;

class DeviceController extends Controller
{
    private SystemService $systemService;
    private CustomerViewService $customerViewService;

    public function __construct(
        SystemService $systemService,
        CustomerViewService $customerViewService,
    ) {
        $this->systemService = $systemService;
        $this->customerViewService = $customerViewService;
    }

    private function getCustomerData()
    {
        $customerLogin = auth()->guard('customer')->user();
        $customer = Customer::find($customerLogin->id);

        return $customer;
    }

    /**
     * Show device information page.
     */
    public function manageDevice()
    {
        $traceId = (string) Str::uuid();

        $customer = $this->getCustomerData();

        // Logging untuk monitoring
        LogHelper::logInfo(null, [
            'customer_id' => $customer->id ?? null
        ], 200, 'Customer akses halaman device information', $traceId);

        $data = $this->customerViewService->getCustomerViewData($customer, $traceId);
        // $data = null;

        return view('customers.pages.devices.info', $data);
    }

    public function mikrotikTrafficInterface()
    {
        $traceId = (string) Str::uuid();
        try {
            $customer = $this->getCustomerData();
            $mikrotik = Mikrotik::where('id', $customer->mikrotik_id)->first();
            $response = $this->systemService->monitoringTraffic($mikrotik, $traceId);
            LogHelper::logInfo(null, [
                'customer' => $customer->name,
                'mikrotik' => $mikrotik->id,
                'response' => $response
            ], 200, 'Successfully to retrieve mikrotik traffic interface');
            return $response;
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 503, 'Failed to retrieve mikrotik traffic interface', $traceId);
            return response()->json([
                'error' => 'Failed to retrieve Mikrotik traffic interface',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
