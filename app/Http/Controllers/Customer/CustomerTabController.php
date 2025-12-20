<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Billings\BillingPeriode;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerAddon;
use App\Models\Customers\CustomerSubscription;
use App\Models\Membership\CustomerMembership;
use App\Models\Membership\Membership;
use App\Models\Packages\Addon;
use App\Models\Packages\Package;
use App\Models\User;
use App\Services\Billings\BillingItemService;
use App\Services\Customers\CustomerBasicInfoService;
use App\Services\Customers\CustomerBillingInfoService;
use Illuminate\Support\Str;
use App\Services\Customers\CustomerMembershipService;
use App\Services\Customers\CustomerOntService;
use App\Services\Customers\CustomerServerService;
use App\Services\Packages\PackageGetService;
use Carbon\Carbon;

class CustomerTabController extends Controller
{
    public function __construct(private PackageGetService $packageGetService)
    {
        //
    }

    public function info(Customer $customer, CustomerBasicInfoService $service)
    {
        $traceId = (string) Str::uuid();

        $data = $service->getCustomerBasicInfo($customer, $traceId);

        // =========================
        // Explicit edit mode flag
        // =========================
        $data['isEdit'] = request()->boolean('edit') && auth()->user()->can('customer-edit');

        // =========================
        // Users list (for edit mode)
        // =========================
        $data['users'] = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('customers.tabs.info', $data);
    }

    public function billing(
        Customer $customer,
        CustomerBillingInfoService $customerBillingInfoService,
        CustomerMembershipService $customerMembershipService,
        BillingItemService $billingItemService,
    ) {
        $traceId = (string) Str::uuid();

        $billingInfo    = $customerBillingInfoService->getCustomerBillingInfo($customer, $traceId);
        $membershipInfo = $customerMembershipService->getCustomerMembershipInfo($customer);
        $billingItems   = $billingItemService->getLatestBillingItems($customer);

        // ====== DATA UNTUK EDIT FORM ======
        $packages = Package::where('disabled', 'false')->get();
        $members  = Membership::where('is_active', 1)->get();

        $activeSubscription = CustomerSubscription::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        $currentDuration = $activeSubscription?->packageDuration;

        $activeMembership = CustomerMembership::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->latest('created_at')
            ->first();

        $billingOption = 'monthly';
        $billingPeriod = null;

        if ($activeMembership) {
            if (!empty($activeMembership->end_date)) {
                // ONE TIME
                $billingOption = 'one_time';

                // Pastikan start_date & end_date di bulan-tahun yang sama
                if (
                    $activeMembership->start_date &&
                    $activeMembership->end_date &&
                    $activeMembership->start_date->format('F Y') ===
                    $activeMembership->end_date->format('F Y')
                ) {
                    $billingPeriod = $activeMembership->start_date->format('F Y');
                }
            } else {
                // MONTHLY
                $billingOption = 'monthly';
                $billingPeriod = null;
            }
        }

        $last = BillingPeriode::orderByDesc('id')->first();

        if (!$last) {
            return collect();
        }

        // Parse dari name, contoh: "December 2025"
        $current = Carbon::createFromFormat('F Y', $last->name);

        // Generate 3 bulan
        $periodes = collect();

        for ($i = 0; $i < 3; $i++) {
            $periodes->push([
                'name' => $current->copy()->addMonths($i)->format('F Y'),
            ]);
        }

        $availablePeriodes = $periodes;

        $addons = Addon::where('is_active', true)->get();

        $activeCustomerAddons = CustomerAddon::where('customer_id', $customer->id)
            ->where('is_active', 1)
            ->get()
            ->keyBy('addon_id');

        $discountEstimate = null;

        if ($activeSubscription?->package_id && $activeMembership?->membership_id) {
            $discountEstimate = $this->packageGetService->getDiscount(
                $activeSubscription->package_id,
                $activeMembership?->membership_id
            );
        }

        return view('customers.tabs.billing', [
            ...$billingInfo,
            ...$membershipInfo,
            ...$billingItems,

            // REQUIRED
            'customer' => $customer,
            'packages' => $packages,
            'members'  => $members,
            'availablePeriodes' => $availablePeriodes,

            // CURRENT STATE
            'current_package_id'     => $activeSubscription?->package_id,
            'current_duration_id'    => $activeSubscription?->package_duration_id,
            'current_duration'       => $currentDuration,
            'current_membership_id'  => $activeMembership?->membership_id,
            'billing_option'         => $billingOption,
            'billing_period'         => $billingPeriod,
            'addons'                 => $addons,
            'activeCustomerAddons'   => $activeCustomerAddons,
            'discountEstimate'       => $discountEstimate,

            // MODE
            'isEdit' => request()->boolean('edit')
                && auth()->user()->can('billing-edit', $customer),
        ]);
    }

    public function server(Customer $customer, CustomerServerService $service)
    {
        $traceId = (string) Str::uuid();

        // Pastikan customer punya package dan subscription ready
        $customer->loadMissing([
            'activeSubscription.package',  // Ini penting
        ]);

        $data = $service->getServerData($customer, $traceId);

        // Tambahkan package profile ke hasil final
        $data['package_profile'] = $customer->activeSubscription->package->profile ?? null;
        $data['package_name']    = $customer->activeSubscription->package->name ?? null;

        // dd($data);

        return view('customers.tabs.server', $data);
    }

    public function ont(Customer $customer, CustomerOntService $service)
    {
        $ont = $service->getOntForCustomer($customer);

        return view('customers.tabs.ont', compact('customer', 'ont'));
    }

    public function traffic(Customer $customer, CustomerServerService $service)
    {
        $traceId = (string) Str::uuid();

        // SecretActive sudah kamu ambil di CustomerServerService
        $data = $service->getServerData($customer, $traceId);
        $secretActive = $data['secretActive'] ?? null;
        $mikrotik = $customer->mikrotik;

        return view('customers.tabs.traffic', compact('customer', 'mikrotik', 'secretActive'));
    }
}
