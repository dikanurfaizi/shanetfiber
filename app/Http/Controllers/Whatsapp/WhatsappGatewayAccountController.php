<?php

namespace App\Http\Controllers\WhatsApp;

use App\Events\SingleNotification\WhatsAppTestRequest;
use App\Helpers\ExecutionTimer;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\WhatsApp\WhatsAppGateway;
use App\Models\WhatsApp\WhatsAppGatewayAccount;
use App\Models\WhatsApp\WhatsAppNumber;
use App\Services\WhatsApp\CloudAPI\Single\TestMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WhatsAppGatewayAccountController extends Controller
{
    public function __construct() {}

    /**
     * Show create form.
     */
    public function create(WhatsAppGateway $whatsappgateway)
    {
        $view = $whatsappgateway->type === 'cloud'
            ? 'whatsapp_gateways.accounts.create_cloud'
            : 'whatsapp_gateways.accounts.create_onprem';

        return view($view, compact('whatsappgateway'));
    }

    /**
     * Store new account.
     */
    public function store(Request $request, WhatsAppGateway $whatsappgateway)
    {
        if ($whatsappgateway->type === 'cloud') {
            return $this->storeCloud($request, $whatsappgateway);
        }

        return $this->storeOnPrem($request, $whatsappgateway);
    }

    protected function storeCloud(Request $request, WhatsAppGateway $whatsappgateway)
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

        // VALIDASI CROSS-WABA
        foreach ($validated['phone_numbers'] as $num) {
            $pnid = $num['meta_phone_number_id'];

            $exists = WhatsAppNumber::query()
                ->where('meta_phone_number_id', $pnid)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'error' => "Phone number ID {$pnid} is already used by another WABA account.",
                ])->withInput();
            }
        }

        DB::beginTransaction();

        try {
            // 1. Create Gateway Account
            $account = $whatsappgateway->accounts()->create([
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
                ->route('whatsappgateway.show', $whatsappgateway->slug)
                ->with('success', 'Cloud API account successfully added.');
        } catch (\Throwable $e) {
            DB::rollBack();

            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, 'Cloud account creation failed');

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    protected function storeOnPrem(Request $request, WhatsAppGateway $whatsappgateway)
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
            'device_id' => [
                'required',
                'string',
                'max:255',
                'unique:whatsapp_numbers,device_id'
            ],
        ]);

        $formatted = new FormatReceiver();
        $formattedNumber = $formatted->formatReceiver($validated['number'], null, false);
        $jid = $formatted->formatReceiver($validated['number'], null, true);

        try {
            DB::beginTransaction();

            // 1) Insert account
            $account = $whatsappgateway->accounts()->create([
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

            // 2) Insert number
            $slug = Str::slug($validated['number']);
            $account->numbers()->create([
                'number' => $validated['number'],
                'jid' => $jid,
                'slug' => $slug,
                'name' => 'gowa_phone_number',
                'country_code' => $validated['country_code'] ?? '62',
                'device_id' => $validated['device_id'],
                'is_default' => true,
                'is_enabled' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('whatsappgateway.show', $whatsappgateway->slug)
                ->with('success', 'Gowa account successfully added.');
        } catch (\Throwable $e) {
            DB::rollBack();

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
    public function edit(WhatsAppGateway $whatsappgateway, WhatsAppGatewayAccount $account)
    {
        if ($whatsappgateway->id !== $account->whatsapp_gateway_id) {
            abort(404);
        }

        $view = $whatsappgateway->type === 'cloud'
            ? 'whatsapp_gateways.accounts.edit_cloud'
            : 'whatsapp_gateways.accounts.edit_onprem';

        return view($view, [
            'whatsappgateway' => $whatsappgateway,
            'account' => $account
        ]);
    }

    /**
     * Update data.
     */
    public function update(Request $request, WhatsAppGateway $whatsappgateway, WhatsAppGatewayAccount $account)
    {
        if ($whatsappgateway->id !== $account->whatsapp_gateway_id) {
            abort(404);
        }

        if ($whatsappgateway->type === 'cloud') {
            return $this->updateCloud($request, $account, $whatsappgateway);
        }

        return $this->updateOnPrem($request, $account, $whatsappgateway);
    }

    protected function updateCloud(Request $request, WhatsAppGatewayAccount $account, WhatsAppGateway $whatsappgateway)
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
            'phone_numbers.*.id' => 'nullable|integer', // Tambahkan validasi ID jika nomor sudah ada
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

            $exists = WhatsAppNumber::query()
                ->where('meta_phone_number_id', $pnid)
                ->whereHas('account', function ($q) use ($validated, $account) {
                    $q->where('meta_waba_id', '!=', $validated['meta_waba_id']);
                    $q->where('id', '!=', $account->id);
                })
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'error' => "Phone Number ID {$pnid} already belongs to another WABA account.",
                ])->withInput();
            }
        }

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
                    : $account->meta_user_access_token,
            ]);

            $processedIds = [];

            foreach ($validated['phone_numbers'] as $idx => $num) {
                $numberId = $num['id'] ?? null;
                $pnid = $num['meta_phone_number_id'];

                // slug unik dengan menggabungkan account_id & meta_phone_number_id
                $slug = Str::slug("cloudapi-{$account->id}-{$pnid}");

                $dataToSave = [
                    'meta_phone_number_id' => $pnid,
                    'slug'                 => $slug,
                    'name'                 => 'meta_number_id',
                    'number'               => $num['number'] ?? null,
                    'country_code'         => $num['country_code'] ?? '62',
                    'is_default'           => ($validated['default_phone_number'] == $idx),
                    'is_enabled'           => true,
                ];

                // Cek berdasarkan ID jika ada
                if ($numberId) {
                    $model = $account->numbers()->where('id', $numberId)->first();
                    if ($model) {
                        $model->update($dataToSave);
                    } else {
                        $model = $account->numbers()->create($dataToSave);
                    }
                } else {
                    // Jika tidak ada ID (nomor baru), cek by meta_phone_number_id atau create baru
                    $model = $account->numbers()->updateOrCreate(
                        ['meta_phone_number_id' => $pnid],
                        $dataToSave
                    );
                }

                $processedIds[] = $model->id;
            }

            // Hapus nomor yang dicopot/dihapus dari form
            $account->numbers()
                ->whereNotIn('id', $processedIds)
                ->delete();

            DB::commit();

            return redirect()->route('whatsappgateway.show', $whatsappgateway->slug)
                ->with('success', 'Cloud API account updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    protected function updateOnPrem(Request $request, WhatsAppGatewayAccount $account, WhatsAppGateway $whatsAppGateway)
    {
        $existingNumber = $account->numbers()->first();

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

            // number
            'number' => 'required|string',
            'country_code' => 'nullable|string|max:5',
            'device_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('whatsapp_numbers', 'device_id')->ignore($existingNumber?->id),
            ],
        ]);

        $formatted = new FormatReceiver();
        $formattedNumber = $formatted->formatReceiver($validated['number'], null, false);
        $jid = $formatted->formatReceiver($validated['number'], null, true);

        DB::beginTransaction();

        try {
            $authEnabled = $request->has('auth_enabled');

            $updateAccountData = [
                'purpose' => $validated['purpose'] ?? null,
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'base_url' => $validated['base_url'],
                'webhook_secret' => $validated['webhook_secret'] ?? null,
                'auth_enabled' => $authEnabled,
                'basic_auth_username' => $authEnabled ? ($validated['basic_auth_username'] ?? null) : null,
                'cert_file' => $validated['cert_file'] ?? null,
            ];

            if ($authEnabled && $request->filled('basic_auth_password')) {
                $updateAccountData['basic_auth_password'] = encrypt($request->basic_auth_password);
            } elseif (!$authEnabled) {
                $updateAccountData['basic_auth_password'] = null;
            }

            $account->update($updateAccountData);

            // ✅ PERBAIKAN: Cari berdasarkan ID number jika ada, atau cukup berdasarkan whatsapp_gateway_account_id
            $account->numbers()->updateOrCreate(
                ['whatsapp_gateway_account_id' => $account->id],
                [
                    'number' => $formattedNumber,
                    'country_code' => $validated['country_code'] ?? '62',
                    'device_id' => $validated['device_id'],
                    'jid' => $jid,
                    'is_enabled' => true,
                ]
            );

            DB::commit();

            return redirect()->route('whatsappgateway.show', $whatsAppGateway->slug)
                ->with('success', 'Gowa account updated successfully');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete data.
     */
    public function destroy(WhatsAppGateway $whatsappgateway, WhatsAppGatewayAccount $account)
    {
        if ($account->whatsapp_gateway_id !== $whatsappgateway->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $account->numbers()->delete();
            $account->delete();

            DB::commit();

            return back()->with('success', 'Akun WhatsApp Gateway berhasil dihapus.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Test account connection.
     */
    public function test(Request $request, WhatsAppGateway $whatsappgateway)
    {
        $traceId = (string) Str::uuid();
        $timer   = new ExecutionTimer();

        $request->validate([
            'receive_number' => 'required|string',
        ]);

        $receiveNumber = $request->input('receive_number');

        if ($whatsappgateway->type === 'onprem') {
            try {
                LogHelper::logInfo(null, [
                    'gateway_id'     => $whatsappgateway->id,
                    'receive_number' => $receiveNumber,
                ], 200, '[TestGateway] Start dispatching test message', $traceId, $timer->lap());

                event(new WhatsAppTestRequest($receiveNumber, $traceId));

                LogHelper::logInfo(null, [
                    'gateway_id'     => $whatsappgateway->id,
                    'receive_number' => $receiveNumber,
                ], 200, '[TestGateway] Test message queued successfully.', $traceId, $timer->lap());

                return back()->with('success', 'Pesan uji berhasil dikirim ke antrean.');
            } catch (\Throwable $e) {
                $exceptionCode = $e->getCode();
                $statusCode = (is_numeric($exceptionCode) && $exceptionCode >= 400 && $exceptionCode <= 599)
                    ? (int) $exceptionCode
                    : 500;

                LogHelper::logError(null, [
                    'gateway_id'      => $whatsappgateway->id,
                    'receive_number'  => $receiveNumber,
                    'error_message'   => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file'            => $e->getFile(),
                    'line'            => $e->getLine(),
                ], $statusCode, '[TestGateway] Failed to dispatch test message', $traceId, $timer->lap());

                return back()->with('error', 'Gagal mengirim pesan uji: ' . $e->getMessage());
            }
        }

        // Cabang Cloud API
        $account = $whatsappgateway->accounts()->where('is_active', true)->first()
            ?? $whatsappgateway->accounts()->first();

        if (!$account) {
            return back()->with('error', 'Tidak ada akun terdaftar pada gateway Cloud API ini.');
        }

        return app(TestMessage::class)->test($account, $receiveNumber);
    }

    /**
     * Test Cloud API connection.
     */
    protected function testCloud(WhatsAppGateway $whatsappgateway, WhatsAppGatewayAccount $account)
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
