<?php

namespace App\Http\Controllers;

//use view;
use App\Models\User;
use App\Models\Order;
use App\Models\Paket;
use App\Models\PppType;
//use App\Jobs\AddSecretJob;
use App\Models\Customer;

use App\Models\Mikrotik;
use App\Models\Websystem;
use Illuminate\View\View;
use App\Models\AutoIsolir;
use App\Models\MerkRouter;
use App\Models\TypeRouter;
use App\Models\CustomerOnt;
use Illuminate\Support\Arr;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Billing\Billing;
use App\Models\DeletedCustomer;
//use App\Http\Resources\MikrotikResource;
use Illuminate\Validation\Rule;
use App\Jobs\DeletePppSecretJob;
use App\Jobs\EnablePppSecretJob;
use App\Jobs\DisablePppSecretJob;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\PaketResource;
use Illuminate\Http\RedirectResponse;
use App\Models\Billing\BillingPeriode;
use App\Jobs\AddPaymentAfterDueDateJob;
use RouterOS\Exceptions\QueryException;
use App\Jobs\AddPaymentBeforeDueDateJob;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
//use Symfony\Component\Console\Input\Input;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Resources\Pppoe\SecretResource;
use App\Http\Requests\CustomerPppUpdateRequest;
use App\Services\Routerboard\Ppp\SecretService;
use RouterOS\Exceptions\BadCredentialsException;
use App\Http\Requests\CustomerRouterUpdateRequest;
use App\Http\Requests\CustomerStoreRequest;
use App\Models\Receipt;
use App\Services\Routerboard\TestConnectionService;
//use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ExportController extends Controller
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;



    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->middleware('permission:secret-create|secret-disable|permission:secret-edit|permission:secret-delete', ['only' => ['index', 'exportToRouter']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');
        return view('exports.index', compact('mikrotiks'));
    }

    public function exportToRouter(Request $request)
    {
        $this->validate($request, [
            'servers' => ['required'],
        ]);
        $mikrotikId = $request->servers;

        $router = Mikrotik::where('id', $mikrotikId)->first();
        try {
            $getAllProfiles = $this->secretService->getPppProfile($router);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['msg' => 'Router offline, please check your router.', 'Server ' . $router->name . ' Offline']);
        }

        $pakets = Paket::where('mikrotik_id', $mikrotikId)->get();
        $paketsUnique = $pakets->unique('profile');
        $profiles = $paketsUnique->pluck('profile')->toArray();
        $collectPaketProfiles = collect($profiles);

        $collectionProfile = SecretResource::collection($getAllProfiles);
        $profileNames = $collectionProfile->pluck(['name'])->toArray();
        $collectProfileNames = collect($profileNames);

        $paketsNonProfile = $collectPaketProfiles->diff($collectProfileNames);
        foreach ($paketsNonProfile as $paketNonProfile) {
            $profiles = Paket::where('profile', $paketNonProfile)->first();
            $comment = 'created_from_customer_management';
            $response = $this->secretService->createPppProfile($router, $profiles->profile,  $comment);

            if (isset($response['after']['message'])) {
                return redirect()->back()->withErrors(['msg' => $response['after']['message'], 'Something wrong.']);
            }
        }


        $customers = Customer::where('mikrotik_id', $mikrotikId)->pluck('username')->toArray();
        $collectCustomerUsernames = collect($customers);
        $getAllSecret = $this->secretService->getAllPppSecrets($router);
        $collectionSecret = SecretResource::collection($getAllSecret);
        $secretNames = $collectionSecret->pluck(['name'])->toArray();
        $collectSecretNames = collect($secretNames);
        $customersNonSecret = $collectCustomerUsernames->diff($collectSecretNames);
        $countSecretExported = 0;
        foreach ($customersNonSecret as $customerNonSecret) {
            $customers = Customer::where('username', $customerNonSecret)->first();
            $service = PppType::where('id', $customers->ppp_type_id)->value('name');
            $profile = Paket::where('id', $customers->paket_id)->value('profile');
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotikId)->first();
            if ($autoIsolir != NULL) {
                $comment = $autoIsolir->comment_unpayment;
            } else {
                $comment = 'import_from_customer_management';
            }

            $response = $this->secretService->createSecret($router, $customers->username,  $customers->password_ppp, $service, $profile, $comment);
            if (isset($response['after']['message'])) {
                Customer::where('id', $customers->id)->update([
                    'secret_id' => 0,
                ]);
            } else {
                $countSecretExported++;
                Customer::where('id', $customers->id)->update([
                    'secret_id' => $response['after']['ret'],
                ]);
            }
        }
        return redirect()->back()->with('success', $countSecretExported . ' Customer successfully export to server ' . $router->name);
    }
}
