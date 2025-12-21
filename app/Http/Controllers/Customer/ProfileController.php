<?php

namespace App\Http\Controllers\Customer;

use App\Helpers\ExecutionTimer;
use App\Models\Customers\Customer;
use App\Models\ActivityLog;
use App\Models\PasswordHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Customers\PaymentDashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\FormatReceiver;
use Illuminate\Support\Facades\Hash;
use App\Helpers\LogHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Rules\NotInPasswordHistory;

class ProfileController extends Controller
{
    private function formatContact($contact, $action = null)
    {
        $formatter = new FormatReceiver();
        return $formatter->formatReceiver($contact, $action, false);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PaymentDashboardService $dashboardService)
    {
        $customer = auth()->guard('customer')->user();
        $result = $dashboardService->getDashboardData($customer);

        return view($result['view'], $result['data']);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $customerId = (auth()->guard('customer')->user()->id);
        $customer = Customer::where('id', $customerId)->first();
        $customer->contact_formatted = $this->formatContact($customer->contact, 'display');
        // return dd($customer);
        return view('customers.pages.profile', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $customerLogin = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->ignore($customerLogin->id)
            ],
            'contact' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Alamat email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain',
            'contact.required' => 'Nomor kontak wajib diisi',
            'address.max' => 'Alamat terlalu panjang (maksimal 500 karakter)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = Customer::find($customerLogin->id);
            $contact = $this->formatContact($request->contact, null);
            $customer->update([
                'name' => $request->name,
                'email' => $request->email,
                'contact' => $contact,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updatePassword(Request $request)
    {
        $customer = Customer::find(Auth::guard('customer')->id());
        $traceId = Str::uuid();
        $timer = new ExecutionTimer();

        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($customer) {
                    if (!Hash::check($value, $customer->password)) {
                        $fail(__('Password saat ini tidak valid.'));
                    }
                }
            ],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
                new NotInPasswordHistory($customer, 5)
            ],
        ], [
            'new_password.regex' => 'Password harus mengandung: 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 simbol'
        ]);

        if ($validator->fails()) {
            $context = [
                'success' => false,
                'message' => 'Terdapat kesalahan validasi',
                'errors' => $validator->errors()->toArray(),
            ];

            LogHelper::logInfo(null, $context, 400, 'Validation Password Failed', $traceId, $timer->lap());

            return response()->json([
                'success' => false,
                'message' => 'Terdapat kesalahan validasi',
                'errors' => $validator->errors()->toArray(),
                'trace_id' => $traceId
            ], 422);
        }

        try {
            DB::transaction(function () use ($customer, $request, $traceId, $timer) {
                // 1. Simpan password lama ke history
                PasswordHistory::create([
                    'customer_id' => $customer->id,
                    'password' => $customer->password,
                    'created_at' => now()
                ]);

                // 2. Update password baru
                $customer->update([
                    'password' => Hash::make($request->new_password),
                    'password_changed_at' => now()
                ]);

                // 3. Ambil ID history terbaru maksimal 5
                $latestFive = PasswordHistory::where('customer_id', $customer->id)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->pluck('id');

                // 4. Hapus yang tidak termasuk 5 terbaru
                PasswordHistory::where('customer_id', $customer->id)
                    ->whereNotIn('id', $latestFive)
                    ->delete();

                // 5. Catat aktivitas
                ActivityLog::create([
                    'customer_id' => $customer->id,
                    'activity_type' => 'password_change',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'details' => json_encode([
                        'action' => 'password_changed',
                        'changed_at' => now()->toDateTimeString()
                    ])
                ]);
            });

            // 5. Logout semua session
            Auth::guard('customer')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            LogHelper::logInfo(
                null,
                [
                    'success' => true,
                    'message' => 'Password berhasil diperbarui! Silakan login kembali.',
                    'requires_logout' => true,
                    'show_toast' => true
                ],
                200,
                '[G9] Finished Update Password',
                $traceId,
                $timer->lap()
            );

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diperbarui! Silakan login kembali.',
                'redirect' => route('customerLogin'), // Redirect ke halaman login
                'requires_logout' => true,
                'show_toast' => true
            ]);
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'customer_id' => $customer->id,
                'ip' => $request->ip(),
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ],
            ], 500, "Password Change Failed", $traceId, $timer->lap());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui password. Silakan coba lagi.',
                'error_code' => 'SYSTEM_ERROR',
                'trace_id' => $traceId
            ], 500);
        }
    }

    protected function generatePasswordSuggestion()
    {
        // Contoh generate password yang memenuhi kriteria
        $lower = str_shuffle('abcdefghjkmnpqrstuvwxyz');
        $upper = str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ');
        $number = rand(1000, 9999);
        $special = str_shuffle('@$!%*#?&');

        return substr($lower, 0, 4) . substr($upper, 0, 3) . $number . $special[0];
    }
}
