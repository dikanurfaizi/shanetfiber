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
use App\Models\Paket\PppProfile;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Bus;
use App\Jobs\CreatePppProfileToRouter;
use App\Jobs\MigrateCustomerToRouter;

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
            'source_server' => ['required'],
            'destination_server' => ['required'],
        ]);

        $sourceMikrotik = Mikrotik::findOrFail($request->source_server);
        $targetMikrotik = Mikrotik::findOrFail($request->destination_server);

        $profilesToExport = PppProfile::all();

        $profileJobs = $profilesToExport->map(function ($profile) use ($targetMikrotik) {
            return new CreatePppProfileToRouter($targetMikrotik, $profile);
        })->toArray();

        // Chain: export profile ke target, lalu migrasi customer dari source ke target
        Bus::chain(array_merge(
            $profileJobs,
            [new MigrateCustomerToRouter($sourceMikrotik, $targetMikrotik)]
        ))->dispatch();

        return redirect()->back()->with('success', "Export profile dan migrasi customer sedang diproses untuk router {$targetMikrotik->name}.");
    }
}
