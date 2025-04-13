<?php

namespace App\Http\Controllers\Mikrotik\Ppp;

use Carbon\Carbon;

use App\Models\Paket;
use App\Models\PppType;
use App\Models\Customer;
use App\Models\Mikrotik;
use App\Models\Websystem;
use App\Models\CustomerOnt;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\EnablePppSecretJob;
use App\Jobs\DisablePppSecretJob;
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
        try {
            //$router = $mikrotik;
            $secretsActive = $this->secretService->getAllPppSecrets($mikrotik);
            $secrets = $this->secretService->getAllPppSecrets($mikrotik);
            $pppsecrets = SecretResource::collection($secrets);
            $pppSecretsActive = SecretResource::collection($secretsActive);

            // LogHelper::logInfo(null, [
            //     'mikrotik' => $mikrotik->toArray(),
            //     'pppsecrets' => $pppsecrets,
            //     'pppSecretsActive' => $pppSecretsActive
            // ], 200, 'Fetched secrets successfully');

            return view('mikrotiks.ppp.secrets.index', [
                'mikrotik' => $mikrotik,
                'pppsecrets' => $pppsecrets,
                'pppSecretsActive' => $pppSecretsActive,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Error fetching secrets');
            return redirect()->back()->withErrors(['msg' => 'Failed to fetch secrets.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Mikrotik $mikrotik)
    {
        try {
            //$router = $mikrotik;
            $profiles = $this->secretService->getPppProfile($mikrotik);
            $pppProfiles = ProfileResource::collection($profiles);
            // $pppServices = PppType::pluck('name', 'id');
            //return dd($pppServices);

            // LogHelper::logInfo(null, [
            //     'mikrotik' => $mikrotik->toArray(),
            //     'pppprofiles' => $pppProfiles,
            //     'pppservices' => PppType::All()->toArray()
            // ], 200, 'Secret created successfully');
            return view('mikrotiks.ppp.secrets.create', [
                'mikrotik' => $mikrotik,
                'pppprofiles' => $pppProfiles,
                'pppservices' => PppType::All(),
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to create secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Mikrotik $mikrotik)
    {
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

            //$router = $mikrotik;

            $response = $this->secretService->createSecret($mikrotik, $username, $password, $service, $profile,  $comment);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray()], 200, 'Secret created successfully');

            return isset($response['after']['message'])
                ? redirect()->back()->with('error', $response['after']['message'])
                : redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])->with('success', 'Secret created successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to create secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mikrotik $mikrotik)
    {
        try {
            //  $router = $mikrotik;

            // $username = request()->input('username');
            //  $secrets = $this->pppService->getPppSecretByName($router, $username);
            // $id = request()->input('id');
            $id = request('id');

            $secrets = $this->secretService->getSecretById($mikrotik, $id);
            $pppsecrets = SecretResource::collection($secrets);

            $profiles = $this->secretService->getPppProfile($mikrotik);
            $pppProfiles = ProfileResource::collection($profiles);
            // LogHelper::logInfo(null, [
            //     'mikrotik' => $mikrotik->toArray(),
            //     'pppsecrets' => $pppsecrets,
            //     'pppprofiles' => $pppProfiles,
            //     'pppservices' => PppType::All()->toArray(),
            // ], 200, 'Secret successfully edited');

            return view('mikrotiks.ppp.secrets.edit', [
                'mikrotik' => $mikrotik,
                'pppsecrets' => $pppsecrets,
                'pppprofiles' => $pppProfiles,
                'pppservices' => PppType::All(),
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to edit secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mikrotik $mikrotik)
    {
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
            $response = $this->secretService->updateSecret($mikrotik, $username, $password, $service, $profile, $id);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray(),], 200, 'Secret updated seccessfully');

            return isset($response['after']['message'])
                ? redirect()->back()->with('error', $response['after']['message'])
                : redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])->with('success', 'Secret updated successfully.');
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function getCountPppSecrets(Mikrotik $mikrotik)
    {
        try {
            $allPppSecrets = $this->secretService->getAllPppSecrets($mikrotik);
            $allPppSecretsCount = count($allPppSecrets);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray(), 'allPppSecretsCount' => $allPppSecretsCount], 200, 'Secret get count ppp secrets');
            return response()->json([
                'allPppSecretsCount' => $allPppSecretsCount,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to get count ppp secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function getCountPppSecretsActive(Mikrotik $mikrotik)
    {
        try {
            $allPppActive = $this->secretService->getPppActive($mikrotik);
            $allPppActiveCount = count($allPppActive);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray(), 'allPppSecretsCount' => $allPppActiveCount], 200, 'Secret get count ppp secrets active');
            return response()->json([
                'allPppActiveCount' => $allPppActiveCount,
            ]);
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to get count ppp secret active');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
    //===================================PPP Secret Action ==============================================
    /**
     * Remove the specified resource from storage.
     */
    public function delete(Mikrotik $mikrotik)
    {
        try {
            $secretId = request('id');
            $this->secretService->deleteOneSecret($mikrotik, $secretId);
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray()], 200, 'Successfully delete one secret');
            return back();
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed delete one secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function disable(Mikrotik $mikrotik)
    {
        try {
            $username = request()->input('username');
            dispatch(new DisablePppSecretJob($mikrotik, $username))->onQueue('default');
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray()], 200, 'Successfully disable ppp secret');
            return back();
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to disable ppp secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function enable(Mikrotik $mikrotik)
    {
        try {
            $username = request()->input('username');
            dispatch(new EnablePppSecretJob($mikrotik, $username))->onQueue('default');
            // LogHelper::logInfo(null, ['mikrotik' => $mikrotik->toArray()], 200, 'Successfully enable ppp secret');
            return back();
        } catch (Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to enable ppp secret');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    // public function clone_secret(Request $request, Mikrotik $mikrotik)
    // {
    //     try {
    //         $mikrotikId = $mikrotik->id;
    //         $allSecrets = $this->secretService->getAllPppSecrets($mikrotik);
    //         $pppsecrets = SecretResource::collection($allSecrets);
    //         $pppsecretsIDs = $pppsecrets->pluck(['.id'])->toArray();
    //         $scretIDs = collect($pppsecretsIDs);
    //         $activationDate = Carbon::now();
    //         $customers = Customer::where('mikrotik_id', $mikrotikId)->pluck('secret_id')->toArray();
    //         $customersSecretIDs = collect($customers);
    //         $secretIDNonCustomers = $scretIDs->diff($customersSecretIDs);
    //         $secretNonCustomer =  $pppsecrets->whereIn(['.id'], $secretIDNonCustomers)
    //             ->toArray();
    //         foreach ($secretNonCustomer as $secret) {
    //             $pppTypeId = PppType::where('name', $secret['service'])->value('id');
    //             if (!isset($pppTypeId)) {
    //                 return redirect()->back()->withErrors(['msg' => 'There isnot PPP services are the same, please check ppp_types table on your database.']);
    //             }
    //             $paketId = Paket::where('mikrotik_id', $mikrotikId)->where('profile', $secret['profile'])->value('id');
    //             if (!isset($paketId)) {
    //                 return redirect()->back()->withErrors(['msg' => 'Please create one package according to your Mikrotik profile!', 'Go to on sidebar menu then select [Home] -> [Package] -> [Create New Package]', 'Or you can import profile on your mikrotik, select menu [PPP] -> [PPP Profiles] -> [Import Profiles]']);
    //             }
    //             $customer = Customer::create([
    //                 'slug' => str($secret['name'] . '-' . Str::random(4))->slug(),
    //                 'name' => $secret['name'],
    //                 'password' => Hash::make($secret['password']),
    //                 'mikrotik_id' => $mikrotikId,
    //                 'paket_id' => $paketId,
    //                 'secret_id' => $secret['.id'],
    //                 'username' => $secret['name'],
    //                 'password_ppp' => $secret['password'],
    //                 'ppp_type_id' => $pppTypeId,
    //                 'internet_service' => 'ppp',
    //                 'disabled' => $secret['disabled'],
    //                 'activation_date' => $activationDate,
    //                 'activation' => 'true',
    //             ]);
    //             $getCustomer = $customer->fresh();
    //             CustomerOnt::create([
    //                 'customer_id' => $getCustomer->id,
    //             ]);
    //         }
    //         return redirect()->route('customers.index')
    //             ->with('success', 'Import customers successfully.');
    //     } catch (Exception $e) {
    //     }
    // }
    public function clone_secret(Request $request, Mikrotik $mikrotik)
    {
        try {
            $mikrotikId = $mikrotik->id;
            $allSecrets = $this->secretService->getAllPppSecrets($mikrotik);
            $pppsecrets = SecretResource::collection($allSecrets);

            // Ambil daftar secret ID
            $pppsecretsIDs = $pppsecrets->pluck('.id')->toArray();
            $customersSecretIDs = Customer::where('mikrotik_id', $mikrotikId)->pluck('secret_id')->toArray();

            // Filter secret yang bukan customer
            $secretIDNonCustomers = array_diff($pppsecretsIDs, $customersSecretIDs);
            $secretNonCustomer = $pppsecrets->whereIn('.id', $secretIDNonCustomers)->toArray();

            $activationDate = Carbon::now();

            foreach ($secretNonCustomer as $secret) {
                $pppTypeId = PppType::where('name', $secret['service'])->value('id');
                if (!$pppTypeId) {
                    throw new Exception('No matching PPP service found. Check ppp_types table.');
                }

                $paketId = Paket::where('mikrotik_id', $mikrotikId)->where('profile', $secret['profile'])->value('id');
                if (!$paketId) {
                    throw new Exception('No package found for this Mikrotik profile. Create one in [Home] -> [Package] -> [Create New Package] or import from [PPP] -> [PPP Profiles] -> [Import Profiles]');
                }

                $customer = Customer::create([
                    'slug' => str($secret['name'] . '-' . Str::random(4))->slug(),
                    'name' => $secret['name'],
                    'password' => Hash::make($secret['password']),
                    'mikrotik_id' => $mikrotikId,
                    'paket_id' => $paketId,
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
            }

            // Logging sukses
            $logBody = [
                'mikrotik_id' => $mikrotikId,
                'imported_customers' => count($secretNonCustomer),
            ];
            // LogHelper::logInfo(null, $logBody, 200, 'Import customers successfully');

            return redirect()->route('customers.index')->with('success', 'Import customers successfully.');
        } catch (Exception $e) {
            // Logging error
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to import customers');
            return redirect()->back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
}
