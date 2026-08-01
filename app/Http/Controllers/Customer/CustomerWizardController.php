<?php

namespace App\Http\Controllers\Customer;

use App\Helpers\FormatReceiver;
use App\Helpers\SubscriptionPricingCalculator;
use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Validator;
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
        $traceId = (string) Str::uuid();
        $users = User::where('disabled', 'false')->get();
        return view('wizard.customer_step1', compact('users'));
    }

    public function step1Post(Request $request, FormatReceiver $formatter)
    {
        $request->merge([
            'contact' => $formatter->normalize($request->input('contact'))
        ]);

        $validated = $request->validate(
            [
                'user_id' => [
                    'required',
                    'exists:users,id'
                ],

                'name' => [
                    'required',
                    'string',
                    'min:3'
                ],

                'email' => [
                    'required',
                    'email'
                ],

                'contact' => [
                    'required',
                    'regex:/^62[0-9]{8,14}$/',
                    'unique:customers,contact'
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:255'
                ],

                'password' => [
                    'nullable',
                    'min:8',
                    'confirmed'
                ]
            ],

            [
                'user_id.required' => 'Admin harus dipilih.',
                'user_id.exists'   => 'Admin yang dipilih tidak ditemukan.',

                'name.required' => 'Nama customer wajib diisi.',
                'name.min'      => 'Nama customer minimal 3 karakter.',

                'email.required' => 'Email wajib diisi.',
                'email.email'    => 'Format email tidak valid.',

                'contact.required' => 'Nomor WhatsApp wajib diisi.',
                'contact.regex'    => 'Nomor harus menggunakan format Indonesia yang valid.',
                'contact.unique'   => 'Nomor WhatsApp sudah terdaftar.',

                'password.min'       => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak sama.',
            ]
        );

        $user = User::select('name')->find($validated['user_id']);
        $validated['user_name'] = $user->name;

        session(['wizard.step1' => $validated]);

        return redirect()->route('wizard.step2');
    }

    public function step2()
    {
        $mikrotiks   = Mikrotik::pluck('name', 'id');
        $pppservices = PppType::pluck('name', 'id');

        return view('wizard.customer_step2', compact('mikrotiks', 'pppservices'));
    }

    public function step2Post(Request $request)
    {
        $validated = $request->validate([
            'mikrotik_id'      => 'required|exists:mikrotiks,id',
            'internet_service' => 'required|in:ppp,ip_static',
            'ppp_type_id'      => 'required_if:internet_service,ppp|nullable|exists:ppp_types,id',
            'interface'        => 'required_if:internet_service,ip_static|nullable|string',
            'ip_static'        => 'required_if:internet_service,ip_static|nullable|ip',
            'mac_address'      => 'required_if:internet_service,ip_static|nullable|string',
        ]);

        $validated['mikrotik_name'] = Mikrotik::find($validated['mikrotik_id'])->name;

        if ($validated['internet_service'] === 'ppp') {
            $validated['ppp_type_name'] = PppType::find($validated['ppp_type_id'])->name ?? null;
        }

        session(['wizard.step2' => $validated]);

        return redirect()->route('wizard.step3');
    }

    public function step3()
    {
        $step2 = session('wizard.step2');
        if (!$step2) {
            return redirect()->route('wizard.step2');
        }

        $packages = Package::where('disabled', 'false')
            ->select('id', 'name', 'price')
            ->get();

        $members = Membership::where('is_active', true)->get();
        $last    = BillingPeriode::orderByDesc('id')->first();

        if (!$last) {
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

        return view('wizard.customer_step3', compact('packages', 'members', 'availablePeriodes'));
    }

    public function step3Post(Request $request)
    {
        $validated = $request->validate([
            'package_id'           => 'required|exists:packages,id',
            'package_duration_id'  => 'required|exists:package_durations,id',
            'member_id'            => 'nullable|exists:memberships,id',
            'billing_option'       => 'required|in:monthly,one_time',
            'billing_periode_name' => 'required_if:billing_option,one_time',
            'is_prorate'           => 'required|in:0,1',
        ]);

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

        return redirect()->route('wizard.step4');
    }

    public function step4()
    {
        if (!session('wizard.step3')) {
            return redirect()->route('wizard.step3');
        }

        $addons = Addon::where('is_active', 1)->get();

        return view('wizard.customer_step4', compact('addons'));
    }

    public function step4Post(Request $request)
    {
        $raw = $request->addons ?? [];

        $filtered = collect($raw)
            ->filter(
                fn($item) =>
                !empty($item['id']) && !empty($item['qty'])
            )
            ->values()
            ->toArray();

        $request->merge(['addons' => $filtered]);

        $validated = $request->validate([
            'addons'               => 'nullable|array',
            'addons.*.id'          => 'required|exists:addons,id',
            'addons.*.qty'         => 'required|integer|min:1',
            'addons.*.unit_price'  => 'required|numeric|min:0',
        ]);

        session(['wizard.step4' => $validated['addons'] ?? []]);

        return redirect()->route('wizard.step5');
    }

    public function step5()
    {
        if (!session('wizard.step1') || !session('wizard.step2') || !session('wizard.step3')) {
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

        return view('wizard.customer_step5', compact(
            'step1',
            'step2',
            'step3',
            'addons',
            'addonItems'
        ));
    }

    public function process()
    {
        if (
            !session()->has('wizard.step1') ||
            !session()->has('wizard.step2') ||
            !session()->has('wizard.step3')
        ) {
            return redirect()
                ->route('wizard.step1')
                ->withErrors('Data wizard tidak lengkap. Silakan ulangi proses.');
        }

        $payload = array_merge(
            session('wizard.step1', []),
            session('wizard.step2', []),
            session('wizard.step3', []),
            ['addons' => session('wizard.step4', [])]
        );

        $validator = Validator::make($payload, [
            'name'       => 'required|string|min:3',
            'email'      => 'required|email',
            'contact'    => 'required|regex:/^62[0-9]{8,14}$/',
            'user_id'    => 'required|exists:users,id',
            'is_prorate' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('wizard.step1')
                ->withErrors($validator);
        }

        $traceId = (string) Str::uuid();

        $customer = $this->customerCreateService
            ->createCustomer($payload, $traceId);

        session()->forget('wizard');

        return redirect()
            ->route('customers.show', ['customer' => $customer->slug])
            ->with('success', 'Customer created successfully!');
    }

    /**
     * Endpoint API untuk kalkulasi prorate secara dinamis (AJAX)
     */
    public function calculateProrate(Request $request): JsonResponse
    {
        $packageDurationId = $request->input('package_duration_id');
        $discountAmount    = (float) $request->input('discount_amount', 0);
        $isProrate         = $request->input('is_prorate');

        $packageDuration = PackageDuration::with(['duration', 'package'])->find($packageDurationId);

        if (!$packageDuration) {
            return response()->json(['success' => false, 'message' => 'Package duration not found'], 404);
        }

        $pricing = SubscriptionPricingCalculator::calculateSubscriptionPrice(
            packageDuration: $packageDuration,
            isProrateInput: $isProrate,
            discountNominal: $discountAmount
        );

        return response()->json(array_merge(['success' => true], $pricing));
    }
}
