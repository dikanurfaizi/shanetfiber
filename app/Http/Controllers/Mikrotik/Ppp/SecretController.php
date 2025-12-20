<?php

namespace App\Http\Controllers\Mikrotik\Ppp;

use Carbon\Carbon;
use App\Models\Packages\Package;
use App\Models\PppType;
use App\Models\Customers\Customer;
use App\Models\Mikrotik;
use App\Models\Websystem;
use App\Models\Customers\CustomerOnt;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\Secrets\EnablePppSecretJob;
use App\Jobs\Secrets\DisablePppSecretJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Services\Routerboard\Ppp\SecretService;
use App\Helpers\LogHelper;
use Exception;

class SecretController extends Controller
{
    private SecretService $secretService;

    public function __construct(SecretService $secretService)
    {
        $this->secretService = $secretService;

        $this->middleware('permission:secret-list|secret-create|secret-edit|secret-delete|secret-disable', ['only' => ['index', 'show']]);
        $this->middleware('permission:secret-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:secret-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:secret-delete', ['only' => ['delete']]);
        $this->middleware('permission:secret-disable', ['only' => ['enable', 'disable']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, 'Starting to fetch PPP secrets', $traceId);

            $secretsActive = $this->secretService->getAllPppSecrets($mikrotik, $traceId);
            $secrets = $this->secretService->getAllPppSecrets($mikrotik, $traceId);

            $pppsecrets = SecretResource::collection($secrets);
            $pppSecretsActive = SecretResource::collection($secretsActive);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'total_secrets' => count($pppsecrets),
                'active_secrets' => count($pppSecretsActive)
            ], 200, 'Successfully fetched PPP secrets', $traceId);

            return view('mikrotiks.ppp.secrets.index', [
                'mikrotik' => $mikrotik,
                'pppsecrets' => $pppsecrets,
                'pppSecretsActive' => $pppSecretsActive,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to fetch PPP secrets', $traceId);

            return redirect()->back()->withErrors(['msg' => 'Failed to fetch secrets. Please try again.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id
            ], 200, 'Loading secret creation form', $traceId);

            $profiles = $this->secretService->getPppProfile($mikrotik, $traceId);
            $pppProfiles = ProfileResource::collection($profiles);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'profiles_count' => count($pppProfiles),
                'ppp_services_count' => PppType::count()
            ], 200, 'Secret creation form loaded successfully', $traceId);

            return view('mikrotiks.ppp.secrets.create', [
                'mikrotik' => $mikrotik,
                'pppprofiles' => $pppProfiles,
                'pppservices' => PppType::All(),
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to load secret creation form', $traceId);

            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(null, [
            'mikrotik_id' => $mikrotik->id,
            'request_data' => $request->except('password')
        ], 200, 'Starting to create new PPP secret', $traceId);

        $request->validate([
            'name' => 'required',
            'password' => 'required',
            'service' => 'required',
            'profile' => 'required',
        ], [
            'name.required' => 'Name field is required.',
            'password.required' => 'Password field is required.',
            'service.required' => 'Service field is required.',
            'profile.required' => 'Profile field is required.',
        ]);

        try {
            $systems = Websystem::all();
            $comment = $systems[0]->comment_unpayment;
            $username = $request->name;
            $password = $request->password;
            $service = $request->service;
            $profile = $request->profile;

            $response = $this->secretService->createSecret($mikrotik, $username, $password, $service, $profile, $comment, $traceId);

            if (isset($response['after']['message'])) {
                LogHelper::logWarning(null, [
                    'mikrotik_id' => $mikrotik->id,
                    'username' => $username,
                    'service' => $service,
                    'profile' => $profile,
                    'api_response' => $response
                ], 400, 'PPP secret creation failed with API error', $traceId);

                return redirect()->back()->with('error', $response['after']['message']);
            }

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username,
                'service' => $service,
                'profile' => $profile
            ], 201, 'PPP secret created successfully', $traceId);

            return redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])
                ->with('success', 'Secret created successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $request->name,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to create PPP secret', $traceId);

            return redirect()->back()->withErrors(['msg' => 'Failed to create secret. Please try again.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $traceId = (string) Str::uuid();
        // Implementation for show method
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            $id = request('id');

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $id
            ], 200, 'Loading secret edit form', $traceId);

            $secrets = $this->secretService->getSecretById($mikrotik, $id, $traceId);
            $pppsecrets = SecretResource::collection($secrets);

            $profiles = $this->secretService->getPppProfile($mikrotik, $traceId);
            $pppProfiles = ProfileResource::collection($profiles);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $id,
                'secret_data' => $pppsecrets->first(),
                'profiles_count' => count($pppProfiles)
            ], 200, 'Secret edit form loaded successfully', $traceId);

            return view('mikrotiks.ppp.secrets.edit', [
                'mikrotik' => $mikrotik,
                'pppsecrets' => $pppsecrets,
                'pppprofiles' => $pppProfiles,
                'pppservices' => PppType::All(),
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => request('id'),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to load secret edit form', $traceId);

            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        LogHelper::logInfo(null, [
            'mikrotik_id' => $mikrotik->id,
            'secret_id' => request('id'),
            'request_data' => $request->except('password')
        ], 200, 'Starting to update PPP secret', $traceId);

        $request->validate([
            'name' => 'required',
            'password' => 'required',
            'service' => 'required',
            'profile' => 'required',
        ], [
            'name.required' => 'Name field is required.',
            'password.required' => 'Password field is required.',
            'service.required' => 'Service field is required.',
            'profile.required' => 'Profile field is required.',
        ]);

        try {
            $username = $request->name;
            $password = $request->password;
            $service = $request->service;
            $profile = $request->profile;
            $id = request('id');

            $response = $this->secretService->updateSecret($mikrotik, $username, $password, $service, $profile, $id, $traceId);

            if (isset($response['after']['message'])) {
                LogHelper::logWarning(null, [
                    'mikrotik_id' => $mikrotik->id,
                    'secret_id' => $id,
                    'username' => $username,
                    'api_response' => $response
                ], 400, 'PPP secret update failed with API error', $traceId);

                return redirect()->back()->with('error', $response['after']['message']);
            }

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $id,
                'username' => $username,
                'service' => $service,
                'profile' => $profile
            ], 200, 'PPP secret updated successfully', $traceId);

            return redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])
                ->with('success', 'Secret updated successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => request('id'),
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to update PPP secret', $traceId);

            return redirect()->back()->withErrors(['msg' => 'Failed to update secret. Please try again.']);
        }
    }

    public function getCountPppSecrets(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id
            ], 200, 'Counting total PPP secrets', $traceId);

            $allPppSecrets = $this->secretService->getAllPppSecrets($mikrotik, $traceId);
            $allPppSecretsCount = count($allPppSecrets);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'total_secrets' => $allPppSecretsCount
            ], 200, 'PPP secrets count retrieved', $traceId);

            return response()->json([
                'allPppSecretsCount' => $allPppSecretsCount,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to count PPP secrets', $traceId);

            return response()->json([
                'error' => 'Failed to retrieve secret count',
                'allPppSecretsCount' => 0
            ], 500);
        }
    }

    public function getCountPppSecretsActive(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id
            ], 200, 'Counting active PPP secrets', $traceId);

            $allPppActive = $this->secretService->getPppActive($mikrotik, $traceId) ?? [];
            $allPppActiveCount = is_countable($allPppActive) ? count($allPppActive) : 0;

            return response()->json([
                'status'            => 'success',
                'allPppActiveCount' => $allPppActiveCount,
            ], 200);
        } catch (\Throwable $e) {
            // Log lengkap biar tau error teknisnya
            LogHelper::logError(null, [
                'mikrotik_id'   => $mikrotik->id,
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
            ], 500, 'Failed to count active PPP secrets', $traceId);

            // Tapi response ke frontend tetap aman
            return response()->json([
                'status'            => 'error',
                'message'           => 'Router tidak dapat dihubungi (timeout/connection refused)',
                'allPppActiveCount' => 0,
            ], 200); // <- ubah ke 200 supaya frontend tetap bisa update UI
        }
    }

    //===================================PPP Secret Action ==============================================

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $secretId = request('id');

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $secretId
            ], 200, 'Starting to delete PPP secret', $traceId);

            $this->secretService->deleteOneSecret($mikrotik, $secretId, $traceId);

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $secretId
            ], 200, 'PPP secret deleted successfully', $traceId);

            return back()->with('success', 'Secret deleted successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'secret_id' => $secretId,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to delete PPP secret', $traceId);

            return back()->withErrors(['msg' => 'Failed to delete secret. Please try again.']);
        }
    }

    public function disable(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $username = request()->input('username');

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username
            ], 200, 'Starting to disable PPP secret', $traceId);

            $customer = Customer::where('username', $username)->first();
            dispatch(new DisablePppSecretJob($mikrotik, $username, $customer->id, $traceId))->onQueue('ppp');

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username,
                'customer_id' => $customer?->id
            ], 200, 'PPP secret disable job queued successfully', $traceId);

            return back()->with('success', 'Secret disable request queued.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to disable PPP secret', $traceId);

            return back()->withErrors(['msg' => 'Failed to disable secret. Please try again.']);
        }
    }

    public function enable(Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();
        $username = request()->input('username');

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username
            ], 200, 'Starting to enable PPP secret', $traceId);

            $customer = Customer::where('username', $username)->first();
            dispatch(new EnablePppSecretJob($mikrotik, $username, $customer->id, $traceId))->onQueue('ppp');

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username,
                'customer_id' => $customer?->id
            ], 200, 'PPP secret enable job queued successfully', $traceId);

            return back()->with('success', 'Secret enable request queued.');
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'username' => $username,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to enable PPP secret', $traceId);

            return back()->withErrors(['msg' => 'Failed to enable secret. Please try again.']);
        }
    }

    public function clone_secret(Request $request, Mikrotik $mikrotik)
    {
        $traceId = (string) Str::uuid();

        try {
            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotik->id,
                'mikrotik_name' => $mikrotik->name
            ], 200, 'Starting to clone secrets to customers', $traceId);

            $mikrotikId = $mikrotik->id;
            $allSecrets = $this->secretService->getAllPppSecrets($mikrotik, $traceId);
            $pppsecrets = SecretResource::collection($allSecrets);

            $pppsecretsIDs = $pppsecrets->pluck('.id')->toArray();
            $customersSecretIDs = Customer::where('mikrotik_id', $mikrotikId)->pluck('secret_id')->toArray();

            $secretIDNonCustomers = array_diff($pppsecretsIDs, $customersSecretIDs);
            $secretNonCustomer = $pppsecrets->whereIn('.id', $secretIDNonCustomers)->toArray();

            $activationDate = Carbon::now();
            $importedCount = 0;

            foreach ($secretNonCustomer as $secret) {
                $pppTypeId = PppType::where('name', $secret['service'])->value('id');
                if (!$pppTypeId) {
                    LogHelper::logWarning(null, [
                        'service_name' => $secret['service'],
                        'secret_name' => $secret['name']
                    ], 404, 'PPP service not found during cloning', $traceId);
                    continue;
                }

                $packageId = Package::where('mikrotik_id', $mikrotikId)
                    ->where('profile', $secret['profile'])
                    ->value('id');

                if (!$packageId) {
                    LogHelper::logWarning(null, [
                        'profile_name' => $secret['profile'],
                        'secret_name' => $secret['name']
                    ], 404, 'Package not found for profile during cloning', $traceId);
                    continue;
                }

                $customer = Customer::create([
                    'slug' => str($secret['name'] . '-' . Str::random(4))->slug(),
                    'name' => $secret['name'],
                    'password' => Hash::make($secret['password']),
                    'mikrotik_id' => $mikrotikId,
                    'package_id' => $packageId,
                    'secret_id' => $secret['.id'],
                    'username' => $secret['name'],
                    'password_ppp' => $secret['password'],
                    'ppp_type_id' => $pppTypeId,
                    'internet_service' => 'ppp',
                    'disabled' => $secret['disabled'],
                    'activation_date' => $activationDate,
                    'activation' => 'true',
                ]);

                CustomerOnt::create([
                    'customer_id' => $customer->id,
                ]);

                $importedCount++;

                LogHelper::logInfo(null, [
                    'customer_id' => $customer->id,
                    'username' => $secret['name'],
                    'secret_id' => $secret['.id']
                ], 201, 'Customer created from secret', $traceId);
            }

            LogHelper::logInfo(null, [
                'mikrotik_id' => $mikrotikId,
                'total_secrets' => count($secretNonCustomer),
                'imported_customers' => $importedCount
            ], 200, 'Secrets cloned to customers successfully', $traceId);

            return redirect()->route('customers.index')
                ->with('success', "Successfully imported {$importedCount} customers from secrets.");
        } catch (Exception $e) {
            LogHelper::logError(null, [
                'mikrotik_id' => $mikrotik->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ], 500, 'Failed to clone secrets to customers', $traceId);

            return redirect()->back()
                ->withErrors(['msg' => 'Failed to import customers: ' . $e->getMessage()]);
        }
    }
}
