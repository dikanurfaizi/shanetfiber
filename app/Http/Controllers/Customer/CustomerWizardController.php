<?php

namespace App\Http\Controllers\Customer;

use App\Helpers\FormatReceiver;
use App\Helpers\ProrateCalculator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Mikrotik;
use App\Models\PppType;
use App\Models\Packages\Package;
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
                'user_id.exists' => 'Admin yang dipilih tidak ditemukan.',

                'name.required' => 'Nama customer wajib diisi.',
                'name.min' => 'Nama customer minimal 3 karakter.',

                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',

                'contact.required' => 'Nomor WhatsApp wajib diisi.',
                'contact.regex' => 'Nomor harus menggunakan format Indonesia yang valid.',
                'contact.unique' => 'Nomor WhatsApp sudah terdaftar.',

                'password.min' => 'Password minimal 8 karakter.',
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
        $mikrotiks = Mikrotik::pluck('name', 'id');
        $pppservices = PppType::pluck('name', 'id');

        return view('wizard.customer_step2', compact('mikrotiks', 'pppservices'));
    }

    public function step2Post(Request $request)
    {
        $validated = $request->validate([
            'mikrotik_id'        => 'required|exists:mikrotiks,id',
            'internet_service'   => 'required|in:ppp,ip_static',
            'ppp_type_id'        => 'required_if:internet_service,ppp|nullable|exists:ppp_types,id',
            'interface'          => 'required_if:internet_service,ip_static|nullable|string',
            'ip_static'          => 'required_if:internet_service,ip_static|nullable|ip',
            'mac_address'        => 'required_if:internet_service,ip_static|nullable|string',
        ]);

        // Tambahkan mikrotik_name
        $validated['mikrotik_name'] = Mikrotik::find($validated['mikrotik_id'])->name;

        // Jika PPP, tambahkan ppp_type_name
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

        // 💡 PERBAIKAN: Ambil paket tanpa mapWithKeys dulu agar data 'price' murni tidak hilang saat di Blade loop
        $packages = Package::where('disabled', 'false')
            ->select('id', 'name', 'price')
            ->get();

        $members = Membership::where('is_active', true)->get();
        $last = BillingPeriode::orderByDesc('id')->first();

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

        $package = Package::find($validated['package_id']);
        $validated['package_name'] = $package->name;
        $packagePrice = $package->price;

        $duration = $package->packageDurations()->find($validated['package_duration_id']);
        $validated['duration_label'] = $duration?->duration?->name_en ?? '-';
        $months = $duration?->duration?->duration_months ?? 1;

        // ========== MEMBERSHIP DISCOUNT HANDLING ==========
        $discountNominal = 0;
        $discountPercent = 0;
        if ($validated['member_id']) {
            $member = Membership::find($validated['member_id']);
            $validated['member_label'] = $member->name;
            if ($member->discount_type === 'percent') {
                $discountPercent = $member->discount_value ?? 0;
                $discountNominal = max(($discountPercent / 100) * $packagePrice, 0);
            } else {
                $discountNominal = max($member->discount_value ?? 0, 0);
                $discountPercent = ($packagePrice > 0) ? round(($discountNominal / $packagePrice) * 100, 2) : 0;
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

        // ========== 💡 PANGGIL HELPER BARU ==========
        $calculation = ProrateCalculator::calculate($packagePrice, $discountNominal, $validated['is_prorate'], $months);

        // Satukan hasil hitungan helper ke dalam array session wizard
        $validated = array_merge($validated, [
            'package_price'          => $packagePrice,
            'price_before_discount'  => $calculation['price_before_discount'], // 💡 Nilai prorate dasar/mentah sebelum diskon
            'final_bill_price'       => $calculation['price_final'],           // 💡 Nilai bersih setelah dikurangi diskon
            'calculation_type_label' => $calculation['calculation_type_label'],
            'active_days'            => $calculation['active_days'],
            'total_days'             => $calculation['total_days'],
            'current_day'            => $calculation['current_day'],
            'current_month'          => $calculation['current_month'],
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
        // Buang addons yang kosong
        $raw = $request->addons ?? [];

        $filtered = collect($raw)
            ->filter(
                fn($item) =>
                !empty($item['id']) && !empty($item['qty'])
            )
            ->values() // reset index biar rapi 0,1,2
            ->toArray();

        // Replace addons yang akan divalidate
        $request->merge(['addons' => $filtered]);

        // Lanjut validasi
        $validated = $request->validate([
            'addons'              => 'nullable|array',
            'addons.*.id'         => 'required|exists:addons,id',
            'addons.*.qty'        => 'required|integer|min:1',
            'addons.*.unit_price' => 'required|numeric|min:0',
        ]);

        session(['wizard.step4' => $validated['addons'] ?? []]);

        return redirect()->route('wizard.step5');
    }

    public function step5()
    {
        if (!session('wizard.step1') || !session('wizard.step2') || !session('wizard.step3')) {
            return redirect()->route('wizard.step1');
        }

        $step1 = session('wizard.step1');
        $step2 = session('wizard.step2');
        $step3 = session('wizard.step3');
        $addons = session('wizard.step4') ?? [];

        // Untuk menampilkan nama addon, ambil id-ids
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

        // Pastikan baris ini ikut divalidasi ringan di dalam function process() Controller Anda:
        $validator = Validator::make($payload, [
            'name'       => 'required|string|min:3',
            'email'      => 'required|email',
            'contact'    => 'required|regex:/^62[0-9]{8,14}$/',
            'user_id'    => 'required|exists:users,id',
            'is_prorate' => 'required|boolean', // 💡 Diubah menjadi boolean agar lebih fleksibel dan aman
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
        // Validasi input minimal dari AJAX
        $packagePrice   = (float) $request->input('package_price', 0);
        $discountAmount = (float) $request->input('discount_amount', 0);
        $isProrate      = $request->input('is_prorate');
        $durationMonths = (int) $request->input('duration_months', 1);

        // Panggil Helper tunggal Back-End (DRY Principle)
        $calculation = ProrateCalculator::calculate(
            $packagePrice,
            $discountAmount,
            $isProrate,
            $durationMonths
        );

        // Di dalam CustomerWizardController::calculateProrate Anda:
        $exactProratePriceRaw = ($packagePrice * $durationMonths) * ($calculation['active_days'] / $calculation['total_days']);

        return response()->json([
            'success'                 => true,
            'package_price_raw'       => $packagePrice * $durationMonths, // Harga katalog asli
            'exact_prorate_raw'       => $exactProratePriceRaw,           // 12/31 x 1.500.000 (Belum dibulatkan)
            'price_before_discount'   => $calculation['price_before_discount'], // Hasil pembulatan kelipatan 500 (581.000)
            'discount_amount'         => $discountAmount,                 // Potongan diskon (100.000)
            'price_final'             => $calculation['price_final'],     // Hasil bersih akhir (481.000)
            'calculation_type_label'  => $calculation['calculation_type_label'],
            'active_days'             => $calculation['active_days'],
            'total_days'              => $calculation['total_days'],
            'current_day'             => $calculation['current_day'],
            'current_month'           => $calculation['current_month'],
        ]);
    }
}
