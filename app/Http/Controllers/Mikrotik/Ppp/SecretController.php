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
        //$router = $mikrotik;
        $secretsActive = $this->secretService->getAllPppSecrets($mikrotik);
        $secrets = $this->secretService->getAllPppSecrets($mikrotik);
        $pppsecrets = SecretResource::collection($secrets);
        $pppSecretsActive = SecretResource::collection($secretsActive);

        return view('mikrotiks.ppp.secrets.index', [
            'mikrotik' => $mikrotik,
            'pppsecrets' => $pppsecrets,
            'pppSecretsActive' => $pppSecretsActive,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Mikrotik $mikrotik)
    {
        //$router = $mikrotik;
        $profiles = $this->secretService->getPppProfile($mikrotik);
        $pppProfiles = ProfileResource::collection($profiles);
        // $pppServices = PppType::pluck('name', 'id');
        //return dd($pppServices);
        return view('mikrotiks.ppp.secrets.create', [
            'mikrotik' => $mikrotik,
            'pppprofiles' => $pppProfiles,
            'pppservices' => PppType::All(),
        ]);
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
        $systems = Websystem::all();
        $comment = $systems[0]->comment_unpayment;
        $username = $request->name;
        $password = $request->password;
        $service = $request->service;
        $profile = $request->profile;

        //$router = $mikrotik;

        $response = $this->secretService->createSecret($mikrotik, $username, $password, $service, $profile,  $comment);


        if (isset($response['after']['message'])) {
            return redirect()->back()->with('error', $response['after']['message']);
            //return dd($createSecret['after']['message']);
        } else {
            return redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])
                ->with('success', 'Secret created successfully.');
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
        //  $router = $mikrotik;

        // $username = request()->input('username');
        //  $secrets = $this->pppService->getPppSecretByName($router, $username);
        // $id = request()->input('id');
        $id = request('id');

        $secrets = $this->secretService->getSecretById($mikrotik, $id);
        $pppsecrets = SecretResource::collection($secrets);

        $profiles = $this->secretService->getPppProfile($mikrotik);
        $pppProfiles = ProfileResource::collection($profiles);

        return view('mikrotiks.ppp.secrets.edit', [
            'mikrotik' => $mikrotik,
            'pppsecrets' => $pppsecrets,
            'pppprofiles' => $pppProfiles,
            'pppservices' => PppType::All(),
        ]);
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
        $username = $request->name;
        $password = $request->password;
        $service = $request->service;
        $profile = $request->profile;
        $id = request('id');
        $response = $this->secretService->updateSecret($mikrotik, $username, $password, $service, $profile, $id);
        if (isset($response['after']['message'])) {
            return redirect()->back()->with('error', $response['after']['message']);
            //return dd($createSecret['after']['message']);
        } else {
            return redirect()->route('ppp.secrets.index', ['mikrotik' => $mikrotik])
                ->with('success', 'Secret updated successfully.');
        }
    }

    public function getCountPppSecrets(Mikrotik $mikrotik)
    {
        $allPppSecrets = $this->secretService->getAllPppSecrets($mikrotik);
        $allPppSecretsCount = count($allPppSecrets);
        return response()->json([
            'allPppSecretsCount' => $allPppSecretsCount,
        ]);
    }

    public function getCountPppSecretsActive(Mikrotik $mikrotik)
    {
        $allPppActive = $this->secretService->getPppActive($mikrotik);
        $allPppActiveCount = count($allPppActive);
        return response()->json([
            'allPppActiveCount' => $allPppActiveCount,
        ]);
    }
    //===================================PPP Secret Action ==============================================
    /**
     * Remove the specified resource from storage.
     */
    public function delete(Mikrotik $mikrotik)
    {
        $secretId = request('id');
        $this->secretService->deleteOneSecret($mikrotik, $secretId);
        return back();
    }

    public function disable(Mikrotik $mikrotik)
    {
        $username = request()->input('username');
        dispatch(new DisablePppSecretJob($mikrotik, $username))->onQueue('default');
        return back();
    }

    public function enable(Mikrotik $mikrotik)
    {
        $username = request()->input('username');
        dispatch(new EnablePppSecretJob($mikrotik, $username))->onQueue('default');
        return back();
    }

    public function clone_secret(Request $request, Mikrotik $mikrotik)
    {
        $mikrotikId = $mikrotik->id;
        $allSecrets = $this->secretService->getAllPppSecrets($mikrotik);
        $pppsecrets = SecretResource::collection($allSecrets);
        $pppsecretsIDs = $pppsecrets->pluck(['.id'])->toArray();
        $scretIDs = collect($pppsecretsIDs);
        $activationDate = Carbon::now();
        $customers = Customer::where('mikrotik_id', $mikrotikId)->pluck('secret_id')->toArray();
        $customersSecretIDs = collect($customers);
        $secretIDNonCustomers = $scretIDs->diff($customersSecretIDs);
        $secretNonCustomer =  $pppsecrets->whereIn(['.id'], $secretIDNonCustomers)
            ->toArray();
        foreach ($secretNonCustomer as $secret) {
            $pppTypeId = PppType::where('name', $secret['service'])->value('id');
            if (!isset($pppTypeId)) {
                return redirect()->back()->withErrors(['msg' => 'There isnot PPP services are the same, please check ppp_types table on your database.']);
            }
            $paketId = Paket::where('mikrotik_id', $mikrotikId)->where('profile', $secret['profile'])->value('id');
            if (!isset($paketId)) {
                return redirect()->back()->withErrors(['msg' => 'Please create one package according to your Mikrotik profile!', 'Go to on sidebar menu then select [Home] -> [Package] -> [Create New Package]', 'Or you can import profile on your mikrotik, select menu [PPP] -> [PPP Profiles] -> [Import Profiles]']);
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
            $getCustomer = $customer->fresh();
            CustomerOnt::create([
                'customer_id' => $getCustomer->id,
            ]);
        }
        return redirect()->route('customers.index')
            ->with('success', 'Import customers successfully.');
    }
}
