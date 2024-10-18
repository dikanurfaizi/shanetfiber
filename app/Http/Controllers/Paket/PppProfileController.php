<?php

namespace App\Http\Controllers\Paket;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Mikrotik;
use App\Models\Paket\PppProfile;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;

class PppProfileController extends Controller
{
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;

    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginate = 10;
        $profiles = PppProfile::orderBy('profile_name', 'ASC')->paginate($paginate);
        return view('packets.profiles.index', [
            'profiles' =>  $profiles,
        ])
            ->with('i', (request()->input('page', 1) - 1) * $paginate);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');

        return view('packets.profiles.create', [
            'mikrotiks' => $mikrotiks,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PppProfile $pppProfile)
    {
        /*
        $this->validate(
            $request,
            [
                'mikrotik_id' => 'required',
                'profile_name' => 'required',
                'dns_server' => 'nullable|ipv4',
                'local_address' => 'nullable|ipv4',
                'remote_address' => 'nullable|ipv4',

            ],
            [

                'profile_name.required' => 'Profile name required.'

            ]
        );
        if ($request->mikrotik_id) {
            $router = Mikrotik::where('id', $request->mikrotik_id)->first();
            $this->validate(
                $request,
                [
                    'profile_name' => 'unique:ppp_profiles,profile_name,null,null,mikrotik_id,' . $request->mikrotik_id

                ],
                [

                    'profile_name.unique' => 'Profile name ' . $request->name . ' already exist in server ' . $router->name,
                ]
            );
        }
*/
        // return dd($router);
        $maxLimitUpload = $request->max_upload;
        $maxLimitDownload = $request->max_download;
        $patternUnit = '/[^a-zA-Z]/';
        $patternLimit = '/[^0-9]/';
        $maxLimitUploadUnit = preg_replace($patternUnit, '', $maxLimitUpload);
        $maxLimitUpload = preg_replace($patternLimit, '', $maxLimitUpload);

        if ($maxLimitUploadUnit == 'm' || $maxLimitUploadUnit == 'M') {
            $maxUpload = strtoupper($request->max_upload);
            $maxLimitUpload = $maxLimitUpload * 1000;
        } else if ($maxLimitUploadUnit == 'K' || $maxLimitUploadUnit == 'k') {
            $maxUpload = strtolower($request->max_upload);
        } else {
            $maxUpload = $request->max_upload . 'k';
        }

        $maxLimitDownloadUnit = preg_replace($patternUnit, '', $maxLimitDownload);
        $maxLimitDownload = preg_replace($patternLimit, '', $maxLimitDownload);
        if ($maxLimitDownloadUnit == 'm' || $maxLimitDownloadUnit == 'M') {
            $maxDownload = strtoupper($request->max_download);
            $maxLimitDownload = $maxLimitDownload * 1000;
        } else if ($maxLimitDownloadUnit == 'K' || $maxLimitDownloadUnit == 'k') {
            $maxDownload = strtolower($request->max_download);
        } else {
            $maxDownload = $request->max_download . 'k';
        }


        //  return dd($maxLimitUpload);

        $mikrotikId = $request->mikrotik_id;
        $profileName = $request->profile_name;
        $localAddress = $request->local_address;
        $remoteAddress = $request->remote_address;
        $DNSServer = $request->dns_server;
        $rasioCir = $request->rasio_cir;
        $priority = $request->priority;
        $parentQueue = $request->parent_queue;
        $queueType = $request->queue_type;
        $insertBefore = $request->insert_before;

        $limitAtUpload = number_format($maxLimitUpload / $rasioCir, 0, '.', '');
        $limitAtDownload = number_format($maxLimitDownload / $rasioCir, 0, '.', '');


        $rateLimitMikrotik = $maxLimitUpload . 'k/' . $maxLimitDownload . 'k 0/0 0/0 0/0 ' . $priority . ' ' . $limitAtUpload . 'k/' . $limitAtDownload . 'k';

        $router = Mikrotik::where('id', $mikrotikId)->first();
        // try {
        //      $updateProfilesMikrotik = $this->secretService->updateRateLimitProfileSecret($router, $rateLimitMikrotik, $pppProfile->profile_id);
        //  } catch (\Exception $e) {
        //      return redirect()->route('ppp-profiles.index')->withErrors(['msg' => 'Server ' . $router->name . ' Offline.', 'Please check your connection!']);
        //  }

        // if (isset($updateProfilesMikrotik['after'])) {
        //     return redirect()->back()->withErrors(['msg' => $updateProfilesMikrotik['after']['message'], $rateLimitMikrotik]);
        // }


        $request->request->add(['burst_time_download' => 0]);
        //  $request->request->remove('max_limit_download');
        //  $request->request->remove('cir');
        // $request->request->remove('unit_max_limit');
        // $request->request->remove('priority');
        $pppProfile->create([
            'mikrotik_id' => $mikrotikId,
            'profile_name' => $profileName,
            'local_address' => $localAddress,
            'remote_address' => $remoteAddress,
            'dns_server' => $DNSServer,
            'max_upload' => $maxUpload,
            'max_download' => $maxDownload,
            'min_upload' => $limitAtUpload . 'k',
            'min_download' => $limitAtDownload . 'k',
            'rasio_cir' => $rasioCir,
            'parent_queue' => $parentQueue,
            'queue_type' => $queueType,
            'insert_queue_before' => $insertBefore
        ]);
        return redirect()->route('ppp-profiles.index')
            ->with('success', 'Packet updated successfully');
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
    public function edit(PppProfile $pppProfile)
    {

        // return dd($maxLimitUpload . $maxLimitDownload . $priority . $limitAtUpload . $limitAtDownload);
        $router = Mikrotik::where('id', $pppProfile->mikrotik_id)->first();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PppProfile $pppProfile)
    {
        //format 10M/4M 0/0 0/0 0/0 8 5000k/750k
        $maxLimitUpload = $request->max_limit_upload;
        $maxLimitDownload = $request->max_limit_download;
        $priority = 8;
        if ($request->priority) {
            $priority = $request->priority;
        }
        $cir = $request->rasio_cir;
        if ($request->unit_max_limit == 'mb') {
            $maxLimitUpload = $request->max_limit_upload * 1000;
            $maxLimitDownload = $request->max_limit_download * 1000;
        }
        $limitAtUpload = number_format($maxLimitUpload / $cir, 0, '.', '');
        $limitAtDownload = number_format($maxLimitDownload / $cir, 0, '.', '');

        // return dd($limitAtDownload);

        //return dd($request->max_limit_upload);
        $rateLimit = $maxLimitUpload . 'k/' . $maxLimitDownload . 'k#0/0 0/0 0/0#' . $priority . '#' . $limitAtUpload . 'k/' . $limitAtDownload . 'k';
        $rateLimitMikrotik = $maxLimitUpload . 'k/' . $maxLimitDownload . 'k 0/0 0/0 0/0 ' . $priority . ' ' . $limitAtUpload . 'k/' . $limitAtDownload . 'k';
        // if ($request->max_limit_upload > 0 || $request->max_limit_download > 0) {

        //}
        $router = Mikrotik::where('id', $pppProfile->mikrotik_id)->first();
        $updateProfilesMikrotik = $this->secretService->updateRateLimitProfileSecret($router, $rateLimitMikrotik, $pppProfile->profile_id);
        //return dd($updateProfilesMikrotik);
        if (isset($updateProfilesMikrotik['after'])) {
            return redirect()->back()->withErrors(['msg' => $updateProfilesMikrotik['after']['message'], $rateLimitMikrotik]);
        }
        //return dd($updateProfilesMikrotik['after']['message']);
        $request->request->add(['rate_limit' => $rateLimit]);
        $request->request->remove('max_limit_upload');
        $request->request->remove('max_limit_download');
        //  $request->request->remove('cir');
        $request->request->remove('unit_max_limit');
        $request->request->remove('priority');
        $pppProfile->update($request->all());
        return redirect()->route('packets.index')
            ->with('success', 'Packet updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
