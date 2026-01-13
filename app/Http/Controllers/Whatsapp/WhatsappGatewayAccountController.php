<?php

namespace App\Http\Controllers\Whatsapp;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Whatsapp\WhatsappGateway;
use App\Models\Whatsapp\WhatsappGatewayAccount;
use App\Models\Whatsapp\WhatsappNumber;
use App\Services\Whatsapp\GowaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsappGatewayAccountController extends Controller
{
    public function __construct() {}

    /**
     * Show create form.
     */
    public function create(WhatsappGateway $whatsappgateway)
    {
        $view = $whatsappgateway->type === 'cloud'
            ? 'whatsapp_gateways.accounts.create_cloud'
            : 'whatsapp_gateways.accounts.create_onprem';

        return view($view, compact('whatsappgateway'));
    }

    /**
     * Store new account.
     */
    public function store(Request $request, WhatsappGateway $whatsappgateway)
    {
        if ($whatsappgateway->type === 'cloud') {
            return $this->storeCloud($request, $whatsappgateway);
        }

        return $this->storeOnPrem($request, $whatsappgateway);
    }

    protected function storeCloud(Request $request, WhatsappGateway $gateway)
    {
        $validated = $request->validate([
            // General
            'purpose' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            // Cloud API Required Fields
            'meta_waba_id' => 'required|string',
            'meta_graph_version' => 'required|string',
            'meta_graph_url' => 'required|url',
            'meta_user_access_token' => 'required|string',
            'meta_webhook_verify_token' => 'nullable|string',

            // Phone numbers array
            'phone_numbers' => 'nullable|array|min:1',
            'phone_numbers.*.meta_phone_number_id' => 'required|string',
            'phone_numbers.*.number' => 'nullable|string',
            'phone_numbers.*.country_code' => 'nullable|string|max:5',

            // Default number index
            'default_phone_number' => 'required|integer|min:0',
        ]);

        // VALIDASI INTERNAL DUPLICATE
        $inputNumbers = array_column($validated['phone_numbers'], 'meta_phone_number_id');
        if (count($inputNumbers) !== count(array_unique($inputNumbers))) {
            return back()->withErrors([
                'error' => 'Duplicate phone_number_id found in the form. Each phone number must be unique.',
            ])->withInput();
        }

        // VALIDASI CROSS-WABA (tidak boleh dipakai WABA lain)
        foreach ($validated['phone_numbers'] as $num) {
            $pnid = $num['meta_phone_number_id'];

            $exists = WhatsappNumber::query()
                ->where('meta_phone_number_id', $pnid)
                ->exists(); // strict global uniqueness

            if ($exists) {
                return back()->withErrors([
                    'error' => "Phone number ID {$pnid} is already used by another WABA account.",
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // 1. Create Gateway Account
            $account = $gateway->accounts()->create([
                'purpose' => $validated['purpose'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),

                'meta_waba_id' => $validated['meta_waba_id'],
                'meta_graph_version' => $validated['meta_graph_version'],
                'meta_graph_url' => $validated['meta_graph_url'],
                'meta_user_access_token' => encrypt($validated['meta_user_access_token']),
                'meta_webhook_verify_token' => $validated['meta_webhook_verify_token'] ?? null,
            ]);

            // 2. Create multiple phone numbers
            $existingIds = [];

            foreach ($validated['phone_numbers'] as $idx => $num) {

                $source = $num['number'] ?? $num['meta_phone_number_id'];
                $clean = preg_replace('/\D/', '', $source);
                $last4 = substr($clean, -4);

                $slug = Str::slug("cloudapi-{$account->id}-{$last4}");

                $model = $account->numbers()->updateOrCreate(
                    [
                        'meta_phone_number_id' => $num['meta_phone_number_id']
                    ],
                    [
                        'slug' => $slug,
                        'name' => 'meta_number_id',
                        'number' => $num['number'] ?? null,
                        'country_code' => $num['country_code'] ?? '62',
                        'is_default' => ($validated['default_phone_number'] == $idx),
                        'is_enabled' => true,
                    ]
                );

                $existingIds[] = $model->id;
            }

            DB::commit();

            return redirect()
                ->route('whatsappgateway.show', $gateway->slug)
                ->with('success', 'Cloud API account successfully added.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Optional: log error
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Cloud account creation failed');

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    protected function storeOnPrem(Request $request, WhatsappGateway $gateway)
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'base_url' => 'required|url',
            'webhook_secret' => 'nullable|string',

            'auth_enabled' => 'nullable|boolean',
            'basic_auth_username' => 'required_if:auth_enabled,1',
            'basic_auth_password' => 'required_if:auth_enabled,1',

            // number
            'number' => 'required|string',
            'country_code' => 'nullable|string|max:5',
        ]);

        try {
            DB::beginTransaction();

            // -----------------------------------------
            // 1) Insert account
            // -----------------------------------------
            $account = $gateway->accounts()->create([
                'purpose' => $validated['purpose'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),

                'base_url' => $validated['base_url'],
                'webhook_secret' => $validated['webhook_secret'] ?? null,

                'auth_enabled' => $request->boolean('auth_enabled'),
                'basic_auth_username' => $validated['basic_auth_username'] ?? null,
                'basic_auth_password' => isset($validated['basic_auth_password'])
                    ? encrypt($validated['basic_auth_password'])
                    : null,

                'cert_file' => $validated['cert_file'] ?? null,
            ]);

            // -----------------------------------------
            // 2) Insert number (default = true always for Gowa)
            // -----------------------------------------
            $slug = Str::slug($validated['number']);
            $account->numbers()->create([
                'number' => $validated['number'],
                'slug' => $slug,
                'name' => 'gowa_phone_number',
                'country_code' => $validated['country_code'] ?? '62',
                'is_default' => true,
                'is_enabled' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('whatsappgateway.show', $gateway->slug)
                ->with('success', 'Gowa account successfully added.');
        } catch (\Throwable $e) {

            DB::rollBack();

            // Optional: log error
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Gowa account creation failed');

            return back()
                ->withInput()
                ->with('error', 'Failed to save Gowa account. Please try again.');
        }
    }

    /**
     * Edit form.
     */
    public function edit(WhatsappGateway $whatsappgateway, WhatsappGatewayAccount $account)
    {
        if ($whatsappgateway->id !== $account->whatsapp_gateway_id) {
            abort(404);
        }

        $view = $whatsappgateway->type === 'cloud'
            ? 'whatsapp_gateways.accounts.edit_cloud'
            : 'whatsapp_gateways.accounts.edit_onprem';

        return view($view, [
            'whatsappgateway' => $whatsappgateway,
            'account' => $account,
        ]);
    }

    /**
     * Update data.
     */
    public function update(Request $request, WhatsappGateway $whatsappgateway, WhatsappGatewayAccount $account)
    {
        if ($whatsappgateway->id !== $account->whatsapp_gateway_id) {
            abort(404);
        }

        if ($whatsappgateway->type === 'cloud') {
            return $this->updateCloud($request, $account, $whatsappgateway);
        }

        return $this->updateOnPrem($request, $account, $whatsappgateway);
    }

    protected function updateCloud(Request $request, WhatsappGatewayAccount $account, WhatsappGateway $gateway)
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'meta_waba_id' => 'required|string',
            'meta_graph_version' => 'required|string',
            'meta_graph_url' => 'required|url',
            'meta_user_access_token' => 'nullable|string',
            'meta_webhook_verify_token' => 'nullable|string',

            'phone_numbers' => 'required|array|min:1',
            'phone_numbers.*.meta_phone_number_id' => 'required|string',
            'phone_numbers.*.number' => 'nullable|string',
            'phone_numbers.*.country_code' => 'nullable|string|max:5',
            'default_phone_number' => 'required|integer',
        ]);

        // VALIDASI INTERNAL DUPLICATE
        $inputNumbers = array_column($validated['phone_numbers'], 'meta_phone_number_id');
        if (count($inputNumbers) !== count(array_unique($inputNumbers))) {
            return back()->withErrors([
                'error' => 'Duplicate phone_number_id found in the form. Each phone number must be unique.',
            ])->withInput();
        }

        // VALIDASI CROSS-WABA DUPLICATE
        foreach ($validated['phone_numbers'] as $num) {
            $pnid = $num['meta_phone_number_id'];

            $exists = WhatsappNumber::query()
                ->where('meta_phone_number_id', $pnid)
                ->whereHas('account', function ($q) use ($validated, $account) {
                    $q->where('meta_waba_id', '!=', $validated['meta_waba_id']); // avoid different WABA
                    $q->where('id', '!=', $account->id); // avoid self
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'error' => "Phone Number ID {$pnid} already belongs to another WABA account.",
                ])->withInput();
            }
        }

        // PROSES UPDATE (same as previous answer)
        DB::beginTransaction();
        try {
            $account->update([
                'purpose' => $validated['purpose'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'meta_waba_id' => $validated['meta_waba_id'],
                'meta_graph_version' => $validated['meta_graph_version'],
                'meta_graph_url' => $validated['meta_graph_url'],
                'meta_webhook_verify_token' => $validated['meta_webhook_verify_token'] ?? null,
                'meta_user_access_token' => $validated['meta_user_access_token']
                    ? encrypt($validated['meta_user_access_token'])
                    : null,
            ]);

            $existingIds = [];

            foreach ($validated['phone_numbers'] as $idx => $num) {

                $source = $num['number'] ?? $num['meta_phone_number_id'];
                $clean = preg_replace('/\D/', '', $source);
                $last4 = substr($clean, -4);

                $slug = Str::slug("cloudapi-{$account->id}-{$last4}");

                $model = $account->numbers()->updateOrCreate(
                    [
                        'meta_phone_number_id' => $num['meta_phone_number_id']
                    ],
                    [
                        'slug' => $slug,
                        'name' => 'meta_number_id',
                        'number' => $num['number'] ?? null,
                        'country_code' => $num['country_code'] ?? '62',
                        'is_default' => ($validated['default_phone_number'] == $idx),
                        'is_enabled' => true,
                    ]
                );

                $existingIds[] = $model->id;
            }

            $account->numbers()
                ->whereNotIn('id', $existingIds)
                ->delete();

            DB::commit();

            return redirect()->route('whatsappgateway.show', $gateway->slug)
                ->with('success', 'Cloud API account updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    protected function updateOnPrem(Request $request, WhatsappGatewayAccount $account, WhatsappGateway $gateway)
    {
        $validated = $request->validate([
            'purpose' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',

            'base_url' => 'required|url',
            'webhook_secret' => 'nullable|string|max:255',

            'auth_enabled' => 'nullable|boolean',

            'basic_auth_username' => 'nullable|string',
            'basic_auth_password' => 'nullable|string',
            'cert_file' => 'nullable|string',
        ]);

        $validated['auth_enabled'] = $request->has('auth_enabled');

        if (! $validated['auth_enabled']) {
            $validated['basic_auth_username'] = null;
            $validated['basic_auth_password'] = null;
        }

        if ($validated['auth_enabled'] && $request->filled('basic_auth_password')) {
            $validated['basic_auth_password'] = encrypt($request->basic_auth_password);
        }

        $validated['is_active'] = $request->has('is_active');

        $account->update($validated);

        return redirect()->route('whatsappgateway.show', $gateway->slug)
            ->with('success', 'Gowa account updated successfully');
    }

    /**
     * Delete data.
     */
    public function destroy(WhatsappGateway $whatsappgateway, WhatsappGatewayAccount $account)
    {
        // 1. Pastikan account memang milik gateway -> keamanan tambahan
        if ($account->whatsapp_gateway_id !== $whatsappgateway->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // 2. Hapus phone numbers dulu (jika tidak ada cascade)
            $account->numbers()->delete();

            // 3. Hapus account
            $account->delete();

            DB::commit();

            return back()->with('success', 'Akun Cloud API berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Test account connection.
     */
    public function test(Request $request, WhatsappGateway $whatsappgateway)
    {
        $request->validate([
            'receive_number' => 'required|numeric',
        ]);

        if ($whatsappgateway->type === 'onprem') {

            return app(GowaService::class)
                ->test($whatsappgateway, $request->receive_number);
        } else {

            // return app(\App\Services\Whatsapp\CloudAPIService::class)
            //     ->test($whatsappgateway, $request->receive_number);
        }
    }

    /**
     * Test Cloud API connection.
     */
    protected function testCloud(WhatsappGateway $gateway, WhatsappGatewayAccount $account)
    {
        try {
            $url = "{$account->meta_graph_url}/{$account->meta_graph_version}/{$account->meta_phone_number_id}?access_token={$account->meta_user_access_token}";

            $response = Http::get($url);

            if ($response->successful() && isset($response['id'])) {
                return back()->with('success', 'Cloud API connection verified successfully.');
            }

            return back()->with('error', 'Cloud API test failed: invalid response from Meta.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cloud API test failed: ' . $e->getMessage());
        }
    }
}
