<?php

namespace App\Http\Controllers\Customer;

use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Helpers\SubscriptionPricingCalculator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerWizardProcessRequest;
use App\Http\Requests\CustomerWizardStep1Request;
use App\Http\Requests\CustomerWizardStep2Request;
use App\Http\Requests\CustomerWizardStep3Request;
use App\Http\Requests\CustomerWizardStep4Request;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Mikrotik;
use App\Models\PppType;
use App\Models\Packages\Package;
use App\Models\Packages\PackageDuration;
use App\Models\Packages\Addon;
use App\Models\Membership\Membership;
use App\Models\Billings\BillingPeriode;
use App\Services\Customers\CustomerCreateService;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class CustomerWizardController extends Controller
{
    private CustomerCreateService $customerCreateService;

    public function __construct(CustomerCreateService $customerCreateService)
    {
        $this->customerCreateService = $customerCreateService;
    }

    public function step1()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $users = User::where('disabled', 'false')->get();

        LogHelper::logInfo(
            null,
            ['step' => 1, 'available_users_count' => $users->count()],
            200,
            'Render Customer Wizard Step 1',
            $traceId,
            $timer->lap()
        );

        return view('wizard.customer_step1', compact('users'));
    }

    public function step1Post(CustomerWizardStep1Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $validated = $request->validated();

        $user = User::select('name')->find($validated['user_id']);
        $validated['user_name'] = $user->name;

        session(['wizard.step1' => $validated]);

        LogHelper::logInfo(
            null,
            [
                'step'      => 1,
                'user_id'   => $validated['user_id'],
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'contact'   => $validated['contact'],
                'has_coords' => !empty($validated['latitude']) && !empty($validated['longitude']),
            ],
            200,
            'Saved Customer Wizard Step 1 to Session',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('wizard.step2');
    }

    public function step2()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $mikrotiks = Mikrotik::where('disabled', 'false')->pluck('name', 'id');
        $pppservices = PppType::pluck('name', 'id');

        LogHelper::logInfo(
            null,
            ['step' => 2, 'mikrotik_count' => $mikrotiks->count()],
            200,
            'Render Customer Wizard Step 2',
            $traceId,
            $timer->lap()
        );

        return view('wizard.customer_step2', compact('mikrotiks', 'pppservices'));
    }

    public function step2Post(CustomerWizardStep2Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $validated = $request->validated();
        $validated['mikrotik_name'] = Mikrotik::find($validated['mikrotik_id'])->name;

        if ($validated['internet_service'] === 'ppp') {
            $validated['ppp_type_name'] = PppType::find($validated['ppp_type_id'])->name ?? null;
        }

        session(['wizard.step2' => $validated]);

        LogHelper::logInfo(
            null,
            [
                'step'             => 2,
                'mikrotik_id'      => $validated['mikrotik_id'],
                'internet_service' => $validated['internet_service'],
                'ppp_type_id'      => $validated['ppp_type_id'] ?? null,
                'ip_static'        => $validated['ip_static'] ?? null,
            ],
            200,
            'Saved Customer Wizard Step 2 to Session',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('wizard.step3');
    }

    public function step3()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $step2 = session('wizard.step2');
        if (!$step2) {
            LogHelper::logInfo(
                null,
                ['step' => 3, 'redirect' => 'step2'],
                302,
                'Step 2 session missing, redirecting to Step 2',
                $traceId,
                $timer->lap()
            );
            return redirect()->route('wizard.step2');
        }

        $packages = Package::where('disabled', 'false')
            ->select('id', 'name', 'price')
            ->get();

        $members = Membership::where('is_active', true)->get();
        $last    = BillingPeriode::orderByDesc('id')->first();

        if (!$last) {
            LogHelper::logInfo(
                null,
                ['step' => 3],
                200,
                'No Billing Periode found',
                $traceId,
                $timer->lap()
            );
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

        LogHelper::logInfo(
            null,
            ['step' => 3, 'packages_count' => $packages->count()],
            200,
            'Render Customer Wizard Step 3',
            $traceId,
            $timer->lap()
        );

        return view('wizard.customer_step3', compact('packages', 'members', 'availablePeriodes'));
    }

    public function step3Post(CustomerWizardStep3Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $validated = $request->validated();

        $package = Package::findOrFail($validated['package_id']);
        $validated['package_name'] = $package->name;

        /** @var PackageDuration $packageDuration */
        $packageDuration = PackageDuration::with(['duration', 'package'])
            ->findOrFail($validated['package_duration_id']);

        $validated['duration_label'] = $packageDuration->duration?->name_en ?? '-';

        // ========== MEMBERSHIP DISCOUNT HANDLING ==========
        $discountNominal = 0;
        $discountPercent = 0;
        if (!empty($validated['member_id'])) {
            $member = Membership::find($validated['member_id']);
            $validated['member_label'] = $member->name;
            if ($member->discount_type === 'percent') {
                $discountPercent = $member->discount_value ?? 0;
                $discountNominal = max(($discountPercent / 100) * $package->price, 0);
            } else {
                $discountNominal = max($member->discount_value ?? 0, 0);
                $discountPercent = ($package->price > 0) ? round(($discountNominal / $package->price) * 100, 2) : 0;
            }
        } else {
            $validated['member_label'] = 'Non Member';
        }

        $validated['discount_nominal'] = $discountNominal;
        $validated['discount_percent'] = $discountPercent;

        // ========== Proteksi Aturan Bisnis FE ==========
        if ($validated['is_prorate'] == '1' && Carbon::now()->day <= 15) {
            LogHelper::logInfo(
                null,
                ['step' => 3, 'reason' => 'prorate_date_restriction'],
                422,
                'Prorate option validation failed',
                $traceId,
                $timer->lap()
            );

            throw ValidationException::withMessages([
                'is_prorate' => __('customer.wizard.message.prorate_not_available')
            ]);
        }

        // ========== 💡 INTEGRASI SUBSCRIPTION PRICING CALCULATOR ==========
        $pricing = SubscriptionPricingCalculator::calculateSubscriptionPrice(
            packageDuration: $packageDuration,
            isProrateInput: $validated['is_prorate'],
            discountNominal: $discountNominal
        );

        $validated = array_merge($validated, [
            'package_price'          => $package->price,
            'price_before_discount'  => $pricing['subscription_price'],
            'final_bill_price'       => $pricing['total_amount_to_pay'],
            'calculation_type_label' => $pricing['calculation_type_label'],
            'active_days'            => $pricing['active_days'],
            'total_days'             => $pricing['total_days'],
            'current_day'            => $pricing['current_day'],
            'current_month'          => $pricing['current_month'],
            'is_prorated'            => $pricing['is_prorated'],
        ]);

        session(['wizard.step3' => $validated]);

        LogHelper::logInfo(
            null,
            [
                'step'                => 3,
                'package_id'          => $validated['package_id'],
                'package_duration_id' => $validated['package_duration_id'],
                'billing_option'      => $validated['billing_option'],
                'is_prorate'          => $validated['is_prorate'],
                'final_bill_price'    => $validated['final_bill_price'],
            ],
            200,
            'Saved Customer Wizard Step 3 to Session',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('wizard.step4');
    }

    public function step4()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        if (!session('wizard.step3')) {
            LogHelper::logInfo(
                null,
                ['step' => 4, 'redirect' => 'step3'],
                302,
                'Step 3 session missing, redirecting to Step 3',
                $traceId,
                $timer->lap()
            );
            return redirect()->route('wizard.step3');
        }

        $addons = Addon::where('is_active', 1)->get();

        LogHelper::logInfo(
            null,
            ['step' => 4, 'addons_count' => $addons->count()],
            200,
            'Render Customer Wizard Step 4',
            $traceId,
            $timer->lap()
        );

        return view('wizard.customer_step4', compact('addons'));
    }

    public function step4Post(CustomerWizardStep4Request $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $validated = $request->validated();
        $selectedAddons = $validated['addons'] ?? [];

        session(['wizard.step4' => $selectedAddons]);

        LogHelper::logInfo(
            null,
            [
                'step'                 => 4,
                'selected_addon_count' => count($selectedAddons),
            ],
            200,
            'Saved Customer Wizard Step 4 to Session',
            $traceId,
            $timer->lap()
        );

        return redirect()->route('wizard.step5');
    }

    public function step5()
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        if (!session('wizard.step1') || !session('wizard.step2') || !session('wizard.step3')) {
            LogHelper::logInfo(
                null,
                ['step' => 5, 'redirect' => 'step1'],
                302,
                'Incomplete wizard session, redirecting to Step 1',
                $traceId,
                $timer->lap()
            );
            return redirect()->route('wizard.step1');
        }

        $step1  = session('wizard.step1');
        $step2  = session('wizard.step2');
        $step3  = session('wizard.step3');
        $addons = session('wizard.step4') ?? [];

        $addonItems = [];
        if (!empty($addons)) {
            $addonItems = Addon::whereIn('id', collect($addons)->pluck('id'))->get();
        }

        LogHelper::logInfo(
            null,
            [
                'step'                  => 5,
                'customer_name'         => $step1['name'] ?? null,
                'package_name'          => $step3['package_name'] ?? null,
                'grand_total_estimated' => $step3['final_bill_price'] ?? 0,
            ],
            200,
            'Render Customer Wizard Step 5 (Review Summary)',
            $traceId,
            $timer->lap()
        );

        return view('wizard.customer_step5', compact(
            'step1',
            'step2',
            'step3',
            'addons',
            'addonItems'
        ));
    }

    public function process(CustomerWizardProcessRequest $request)
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        // Cek kelengkapan session wizard
        if (
            !session()->has('wizard.step1') ||
            !session()->has('wizard.step2') ||
            !session()->has('wizard.step3')
        ) {
            LogHelper::logInfo(
                null,
                ['reason' => 'incomplete_session_state'],
                400,
                'Wizard process failed: Incomplete Session Data',
                $traceId,
                $timer->lap()
            );

            return redirect()
                ->route('wizard.step1')
                ->withErrors(__('customer.wizard.message.incomplete_data'));
        }

        // $request->validationData() mengambil payload gabungan seluruh step yang sudah ter-validate
        $payload = $request->validationData();

        LogHelper::logInfo(
            null,
            [
                'customer_name'    => $payload['name'] ?? null,
                'email'            => $payload['email'] ?? null,
                'contact'          => $payload['contact'] ?? null,
                'user_id'          => $payload['user_id'] ?? null,
                'mikrotik_id'      => $payload['mikrotik_id'] ?? null,
                'package_id'       => $payload['package_id'] ?? null,
                'addons_count'     => count($payload['addons'] ?? []),
                'has_coordinates'  => !empty($payload['latitude']) && !empty($payload['longitude']),
            ],
            200,
            'Processing Final Customer Creation',
            $traceId,
            $timer->lap()
        );

        $customer = $this->customerCreateService
            ->createCustomer($payload, $traceId);

        session()->forget('wizard');

        LogHelper::logInfo(
            null,
            [
                'customer_id'   => $customer->id,
                'customer_code' => $customer->customer_id,
                'slug'          => $customer->slug,
            ],
            201,
            'Customer Registration Successfully Completed via Wizard',
            $traceId,
            $timer->lap()
        );

        return redirect()
            ->route('customers.show', ['customer' => $customer->slug])
            ->with('success', __('customer.wizard.message.create_success'));
    }

    /**
     * Endpoint API untuk kalkulasi prorate secara dinamis (AJAX)
     */
    public function calculateProrate(Request $request): JsonResponse
    {
        $timer = new ExecutionTimer();
        $traceId = (string) Str::uuid();

        $packageDurationId = $request->input('package_duration_id');
        $discountAmount    = (float) $request->input('discount_amount', 0);
        $isProrate         = $request->input('is_prorate');

        $packageDuration = PackageDuration::with(['duration', 'package'])->find($packageDurationId);

        if (!$packageDuration) {
            LogHelper::logInfo(
                null,
                ['package_duration_id' => $packageDurationId],
                404,
                'AJAX Prorate Calculation Failed: Package Duration Not Found',
                $traceId,
                $timer->lap()
            );

            return response()->json(['success' => false, 'message' => 'Package duration not found'], 404);
        }

        $pricing = SubscriptionPricingCalculator::calculateSubscriptionPrice(
            packageDuration: $packageDuration,
            isProrateInput: $isProrate,
            discountNominal: $discountAmount
        );

        LogHelper::logInfo(
            null,
            [
                'package_duration_id' => $packageDurationId,
                'is_prorate'          => $isProrate,
                'calculated_total'    => $pricing['total_amount_to_pay'] ?? 0,
            ],
            200,
            'Calculated Dynamic Prorate Price via AJAX',
            $traceId,
            $timer->lap()
        );

        return response()->json(array_merge(['success' => true], $pricing));
    }
}
