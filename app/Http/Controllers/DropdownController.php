<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddressListResource;
use App\Http\Resources\InterfaceResource;
use App\Models\Paket\Paket;
use App\Models\Mikrotik;
use App\Models\MerkRouter;
use App\Models\TypeRouter;
use Illuminate\Http\Request;
use App\Services\Routerboard\ScriptService;
use App\Http\Resources\Pppoe\ProfileResource;
use App\Http\Resources\QueueResource;
use App\Models\Bank;
use App\Services\Routerboard\IpStaticService;
use App\Services\Routerboard\ScheduleService;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\QueueService;
use Illuminate\Support\Str;

class DropdownController extends Controller
{
    private SecretService $secretService;
    private ScriptService $scriptService;
    private ScheduleService $scheduleService;
    private QueueService $queueService;
    private IpStaticService $ipStaticService;

    public function __construct(SecretService $secretService, ScriptService $scriptService, ScheduleService $scheduleService, QueueService $queueService, IpStaticService $ipStaticService)
    {
        $this->secretService = $secretService;
        $this->scriptService = $scriptService;
        $this->scheduleService = $scheduleService;
        $this->queueService = $queueService;
        $this->ipStaticService = $ipStaticService;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function fetchPaket(Request $request)
    {
        $traceId = (string) Str::uuid();
        $data['pakets'] = Paket::where('mikrotik_id', $request->mikrotik_id)
            ->where('disabled', 'false')
            ->orderBy('price', 'ASC')
            ->get(["name", "id", "price"]);
        return response()->json($data);
    }

    public function fetchProfile(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();;
        $profiles = $this->secretService->getPppProfile($router, $traceId);
        $pppProfiles = ProfileResource::collection($profiles)->pluck('name', 'name');
        return response()->json($pppProfiles);
    }

    public function fetchParentQueue(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $parentSimpleQueues = $this->queueService->getParentSimpleQueues($router, $traceId);
        if ($parentSimpleQueues != 'error') {

            $parentSimpleQueues = QueueResource::collection($parentSimpleQueues);
        }
        // $simpleQueuesCollection = QueueResource::collection($parentSimpleQueues);
        return response()->json($parentSimpleQueues);
    }

    public function fetchQueueType(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $queueTypes = $this->queueService->getQueueTypes($router, $traceId);
        if ($queueTypes != 'error') {

            $queueTypes = QueueResource::collection($queueTypes);
        }
        // $simpleQueuesCollection = QueueResource::collection($parentSimpleQueues);
        return response()->json($queueTypes);
    }

    public function fetchScript(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $scripts = $this->scriptService->getAllScript($router, $traceId);
        $collectionScript = ProfileResource::collection($scripts);
        return response()->json($collectionScript);
    }

    public function fetchSchedule(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $schedules = $this->scheduleService->getAllSchedule($router, $traceId);
        $collectionSchedule = ProfileResource::collection($schedules);
        return response()->json($collectionSchedule);
    }

    public function fetchTypeRouter(Request $request)
    {
        $traceId = (string) Str::uuid();
        // $merkOnt = 'ZTE';
        $merkRouterId = MerkRouter::where('name', $request->merk_ont)->value('id');

        $data = TypeRouter::where('merk_router_id', $merkRouterId)
            ->where('disabled', 'false')
            ->orderBy('name', 'ASC')
            ->get(["name"]);

        return response()->json($data);
    }

    public function fetchAccountBank(Request $request)
    {
        $traceId = (string) Str::uuid();
        $data['banks'] = Bank::where('disabled', 'false')
            ->orderBy('bank', 'ASC')
            ->orderBy('name', 'ASC')
            ->get(["name", "id", "bank", "account_number"]);
        return response()->json($data);
    }

    public function fetchInterface(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $getInterfaces = $this->ipStaticService->getInterfaces($router, $traceId);
        $interfaces = InterfaceResource::collection($getInterfaces);
        return response()->json($interfaces);
    }

    public function fetchAddressList(Request $request)
    {
        $traceId = (string) Str::uuid();
        $router = Mikrotik::where('id', $request->mikrotik_id)->first();
        $addressList = $this->ipStaticService->getAddressList($router, $traceId);
        $addressList = AddressListResource::collection($addressList);
        return response()->json($addressList);
    }
}
