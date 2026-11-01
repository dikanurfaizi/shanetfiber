<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Helpers\LogHelper;
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
use App\Services\Customers\CustomerMembershipService;
use App\Services\Customers\CustomerOntService;
use App\Services\Customers\CustomerServerService;
use App\Services\Packages\PackageGetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CustomerTabController extends Controller
{
    public function __construct(private PackageGetService $packageGetService)
    {
        //
    }

    public function info(Customer $customer, CustomerBasicInfoService $service)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);

        // 1. Ambil data customer secara manual termasuk yang terhapus
        $customerModel = Customer::withTrashed()
            ->where('id', $customer->id)
            ->firstOrFail();

        // 2. Masukkan object $customerModel ke service
        $data = $service->getCustomerBasicInfo($customerModel, $traceId);

        // 💡 Cek permission via Gate Facade dengan menyertakan model Customer agar terikat ke CustomerPolicy
        $data['isEdit'] = request()->boolean('edit') && Gate::allows('customer-edit', $customerModel);

        $data['users'] = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        LogHelper::logInfo(null, [
            'customer_id'   => $customer->id,
            'is_deleted'    => $customerModel->trashed(),
            'edit_mode'     => $data['isEdit'],
            'load_time_ms'  => round((microtime(true) - $startTime) * 1000, 2)
        ], 200, "[Customer Tab] Info tab loaded successfully", $traceId);

        return view('customers.tabs.info', $data);
    }

    public function billing(
        Customer $customer,
        CustomerBillingInfoService $customerBillingInfoService,
        CustomerMembershipService $customerMembershipService,
        BillingItemService $billingItemService,
    ) {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);

        $billingInfo    = $customerBillingInfoService->getCustomerBillingInfo($customer, $traceId);
        $membershipInfo = $customerMembershipService->getCustomerMembershipInfo($customer);
        $billingItems   = $billingItemService->getLatestBillingItems($customer);

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
                $billingOption = 'one_time';

                if (
                    $activeMembership->start_date &&
                    $activeMembership->end_date &&
                    $activeMembership->start_date->format('F Y') ===
                    $activeMembership->end_date->format('F Y')
                ) {
                    $billingPeriod = $activeMembership->start_date->format('F Y');
                }
            } else {
                $billingOption = 'monthly';
                $billingPeriod = null;
            }
        }

        $last = BillingPeriode::orderByDesc('id')->first();

        if (!$last) {
            LogHelper::logWarning(null, [
                'customer_id' => $customer->id
            ], 404, "[Customer Tab] Billing tab rendering stopped: No billing period found in database.", $traceId);

            return collect();
        }

        $current = Carbon::createFromFormat('F Y', $last->name);
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

        LogHelper::logInfo(null, [
            'customer_id'           => $customer->id,
            'active_package_id'     => $activeSubscription?->package_id,
            'active_membership_id'  => $activeMembership?->membership_id,
            'billing_option'        => $billingOption,
            'addons_count'          => $activeCustomerAddons->count(),
            'load_time_ms'          => round((microtime(true) - $startTime) * 1000, 2)
        ], 200, "[Customer Tab] Billing tab data compiled", $traceId);

        // 💡 SOLUSI IDE: Cek permission via Gate Facade agar bebas dari error static analyzer
        $canEditBilling = request()->boolean('edit') && Gate::allows('billing-edit', $customer);

        return view('customers.tabs.billing', [
            ...$billingInfo,
            ...$membershipInfo,
            ...$billingItems,

            'customer' => $customer,
            'packages' => $packages,
            'members'  => $members,
            'availablePeriodes' => $availablePeriodes,

            'current_package_id'    => $activeSubscription?->package_id,
            'current_duration_id'   => $activeSubscription?->package_duration_id,
            'current_duration'      => $currentDuration,
            'current_membership_id' => $activeMembership?->membership_id,
            'billing_option'        => $billingOption,
            'billing_period'        => $billingPeriod,
            'addons'                => $addons,
            'activeCustomerAddons'  => $activeCustomerAddons,
            'discountEstimate'      => $discountEstimate,

            'isEdit' => $canEditBilling,

            'periodes' => $last->name,
        ]);
    }

    public function server(Customer $customer, CustomerServerService $service)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);

        $customer->loadMissing([
            'activeSubscription.package',
        ]);

        $data = $service->getServerData($customer, $traceId);

        // 💡 Safe null-coalescing untuk menghindari IDE undefined property warning
        $package = $customer->activeSubscription?->package;
        $data['package_profile'] = $package?->profile;
        $data['package_name']    = $package?->name;

        LogHelper::logInfo(null, [
            'customer_id'     => $customer->id,
            'mikrotik_id'     => $customer->mikrotik_id ?? 'null',
            'package_profile' => $data['package_profile'],
            'load_time_ms'    => round((microtime(true) - $startTime) * 1000, 2)
        ], 200, "[Customer Tab] Server/Mikrotik tab loaded successfully", $traceId);

        return view('customers.tabs.server', $data);
    }

    public function ont(Customer $customer, CustomerOntService $service)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);

        $ont = $service->getOntForCustomer($customer);

        LogHelper::logInfo(null, [
            'customer_id'   => $customer->id,
            'ont_resolved'  => isset($ont),
            'load_time_ms'  => round((microtime(true) - $startTime) * 1000, 2)
        ], 200, "[Customer Tab] ONT status tab loaded", $traceId);

        return view('customers.tabs.ont', compact('customer', 'ont'));
    }

    public function traffic(Customer $customer, CustomerServerService $service)
    {
        $traceId = (string) Str::uuid();
        $startTime = microtime(true);

        $data = $service->getServerData($customer, $traceId);
        $secretActive = $data['secretActive'] ?? null;
        $mikrotik = $customer->mikrotik;

        LogHelper::logInfo(null, [
            'customer_id'     => $customer->id,
            'mikrotik_name'   => $mikrotik->name ?? 'unknown',
            'has_live_secret' => isset($secretActive),
            'load_time_ms'    => round((microtime(true) - $startTime) * 1000, 2)
        ], 200, "[Customer Tab] Real-time Traffic tab loaded", $traceId);

        return view('customers.tabs.traffic', compact('customer', 'mikrotik', 'secretActive'));
    }
}
