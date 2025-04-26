<?php

namespace App\Http\Controllers\Paket;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Mikrotik;
use App\Models\Paket\PppProfile;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;
use App\Jobs\CreatePppProfileToRouter;

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
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name')->get();
        return view('packets.profiles.index', [
            'profiles' =>  $profiles,
            'mikrotiks' => $mikrotiks,
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
        // dd($request);
        // Validasi input
        $this->validate(
            $request,
            [
                'profile_name' => 'required|unique:ppp_profiles,profile_name',
                'dns_server' => 'nullable|string',
                'use_encryption' => 'required|string',
                'max_upload' => 'required_if:rate_limit,true',  // Jika rate_limit diaktifkan, max_upload harus diisi
                'max_download' => 'required_if:rate_limit,true', // Jika rate_limit diaktifkan, max_download harus diisi
                'rasio_cir' => 'required|numeric',
                'priority' => 'required',
            ],
            [
                'profile_name.required' => 'Profile name is required.',
                'profile_name.unique' => 'Profile name already exists.',
                'max_upload.required_if' => 'Maximum upload is required when rate limit is enabled.',
                'max_download.required_if' => 'Maximum download is required when rate limit is enabled.',
                'use_encryption.required' => 'Encryption setting is required.',
            ]
        );

        // Pemrosesan max_upload dan max_download (konversi unit ke 'M')
        $maxLimitUpload = $request->max_upload;
        $maxLimitDownload = $request->max_download;

        $patternUnit = '/[0-9]/';       // Untuk ambil unit huruf (k, M, dll)
        $patternLimit = '/[^0-9]/';     // Untuk ambil angka

        // ==== Upload ====
        $unitUpload = strtolower(preg_replace($patternUnit, '', $maxLimitUpload));
        $maxLimitUploadValue = (int) preg_replace($patternLimit, '', $maxLimitUpload);

        if ($unitUpload === 'k') {
            $maxLimitUploadValue = $maxLimitUploadValue / 1000;
        } elseif ($unitUpload === '' || $unitUpload === null) {
            // anggap nilai numeric langsung satuan Kbps → ubah ke Mbps
            $maxLimitUploadValue = $maxLimitUploadValue . 'M';
        }
        $maxUpload = $maxLimitUploadValue . 'M';

        // ==== Download ====
        $unitDownload = strtolower(preg_replace($patternUnit, '', $maxLimitDownload));
        $maxLimitDownloadValue = (int) preg_replace($patternLimit, '', $maxLimitDownload);

        if ($unitDownload === 'k') {
            $maxLimitDownloadValue = $maxLimitDownloadValue / 1000;
        } elseif ($unitDownload === '' || $unitDownload === null) {
            $maxLimitDownloadValue = $maxLimitDownloadValue . 'M';
        }
        $maxDownload = $maxLimitDownloadValue . 'M';

        // Penghitungan limit upload dan download
        $rasioCir = $request->rasio_cir;
        $limitAtUpload = number_format(($maxLimitUploadValue * 1000) / $rasioCir, 0, '.', '');
        $limitAtDownload = number_format(($maxLimitDownloadValue * 1000) / $rasioCir, 0, '.', '');

        // Output rate-limit
        $rateLimitMikrotik = $maxUpload . '/' . $maxDownload;
        // $rateLimitMikrotik = $maxUpload . '/' . $maxDownload . ' 0/0 0/0 0/0 ' . $request->priority . ' ' . $limitAtUpload . 'k/' . $limitAtDownload . 'k';

        // Menyiapkan data untuk disimpan
        $pppProfile->create([
            'profile_name' => $request->profile_name,
            'local_address' => $request->local_address ?? null,
            'remote_address' => $request->remote_address ?? null,
            'dns_server' => $request->dns_server ?? '8.8.8.8, 8.8.4.4',
            'max_upload' => $maxUpload,
            'max_download' => $maxDownload,
            'min_upload' => $limitAtUpload . 'k',
            'min_download' => $limitAtDownload . 'k',
            'rasio_cir' => $rasioCir,
            'parent_queue' => $request->parent_queue ?? null,
            'queue_type' => $request->queue_type ?? null,
            'insert_queue_before' => $request->insert_before ?? null,
            'rate_limit' => $rateLimitMikrotik,
            'use_encryption' => $request->use_encryption ?? null,
            'script_on_up' => $request->on_up ?? null,
            'script_on_down' => $request->on_down ?? null,
            'change_tcp_mss' => $request->change_tcp_mss,
            'use_upnp' => $request->use_upnp,
            'comment' => $request->comment ?? null,
            'only_one' => $request->only_one ?? 'yes',
        ]);

        // Redirect ke halaman index setelah berhasil
        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile created successfully.');
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

    public function viewServer(Request $request)
    {
        $mikrotikServers = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');

        return view('packets.profiles.import', compact('mikrotikServers'));
    }

    public function get_mikrotik_profiles(Request $request)
    {
        $sourceMikrotikId = $request->mikrotik_id;
        if (is_null($sourceMikrotikId)) {
            return response()->json(['error' => 'Mikrotik ID is required'], 400);
        }
        try {
            $sourceRouter = Mikrotik::findOrFail($sourceMikrotikId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Mikrotik server not found'], 404);
        }

        try {
            // Ambil dari Mikrotik
            $mikrotikProfiles = $this->secretService->getPppProfile($sourceRouter);
            if (empty($mikrotikProfiles)) {
                return response()->json(['error' => 'No profiles found from Mikrotik'], 404);
            }

            // Ambil dari DB
            $dbProfiles = PppProfile::all()->keyBy('profile_name');

            LogHelper::logWarning(null, [
                'mikrotik_profiles' => $mikrotikProfiles,
                'db_profiles' => $dbProfiles,
            ], 200, 'Successfully get Profile Data');

            $profileList = collect();

            foreach ($mikrotikProfiles as $profile) {
                $profileName = $profile['name'] ?? '';
                $dbProfile = $dbProfiles->get($profileName);

                $status = $dbProfile ? 'conflict' : 'mikrotik';

                // Data dari Mikrotik
                if (!$profileList->contains('profile_name', $profileName)) {
                    $profileList->push([
                        'source' => 'mikrotik',
                        'profile_name' => $profileName,
                        'max_upload' => explode('/', $profile['rate-limit'] ?? '0/0')[0],
                        'max_download' => explode('/', $profile['rate-limit'] ?? '0/0')[1],
                        'min_upload' => '0',
                        'min_download' => '0',
                        'rasio_cir' => '',
                        'priority' => $profile['priority'] ?? '8',
                        'mikrotik_name' => $sourceRouter->name,
                        'status' => $status,
                        'db_profile_id' => $dbProfile?->id,
                        'db_profile_name' => $dbProfile?->profile_name,
                        'dns_server' => $profile['dns-server'] ?? '',
                        'use_encryption' => $profile['use-encryption'] ?? '',
                        'rate_limit' => $profile['rate-limit'] ?? '',
                        'only_one' => $profile['only-one'] ?? '',
                        'difference' => $dbProfile ? [
                            'rate_limit' => [
                                'mikrotik' => $profile['rate-limit'] ?? '',
                                'db' => $dbProfile->rate_limit,
                            ],
                            'priority' => [
                                'mikrotik' => $profile['priority'] ?? '',
                                'db' => $dbProfile->priority,
                            ],
                            'dns_server' => [
                                'mikrotik' => $profile['dns-server'] ?? '',
                                'db' => $dbProfile->dns_server,
                            ],
                            'use_encryption' => [
                                'mikrotik' => $profile['use-encryption'] ?? '',
                                'db' => $dbProfile->use_encryption,
                            ],
                            'only_one' => [
                                'mikrotik' => $profile['only-one'] ?? '',
                                'db' => $dbProfile->only_one,
                            ]
                        ] : null,
                    ]);
                }

                // Data dari DB
                if ($dbProfile) {
                    $profileList->push([
                        'source' => 'database',
                        'profile_name' => $dbProfile->profile_name,
                        'max_upload' => $dbProfile->max_upload,
                        'max_download' => $dbProfile->max_download,
                        'min_upload' => $dbProfile->min_upload,
                        'min_download' => $dbProfile->min_download,
                        'rasio_cir' => $dbProfile->rasio_cir,
                        'priority' => $dbProfile->priority,
                        'mikrotik_name' => $sourceRouter->name,
                        'status' => 'conflict',
                        'db_profile_id' => $dbProfile->id,
                        'db_profile_name' => $dbProfile->profile_name,
                        'dns_server' => $dbProfile->dns_server,
                        'use_encryption' => $dbProfile->use_encryption,
                        'rate_limit' => $dbProfile->rate_limit,
                        'only_one' => $dbProfile->only_one,
                        'difference' => $dbProfile ? [
                            'rate_limit' => [
                                'mikrotik' => $profile['rate-limit'] ?? '',
                                'db' => $dbProfile->rate_limit,
                            ],
                            'priority' => [
                                'mikrotik' => $profile['priority'] ?? '',
                                'db' => $dbProfile->priority,
                            ],
                            'dns_server' => [
                                'mikrotik' => $profile['dns-server'] ?? '',
                                'db' => $dbProfile->dns_server,
                            ],
                            'use_encryption' => [
                                'mikrotik' => $profile['use-encryption'] ?? '',
                                'db' => $dbProfile->use_encryption,
                            ],
                            'only_one' => [
                                'mikrotik' => $profile['only-one'] ?? '',
                                'db' => $dbProfile->only_one,
                            ]
                        ] : null,
                    ]);
                }
            }

            LogHelper::logWarning(null, [
                'mikrotik_profiles' => $mikrotikProfiles,
                'db_profiles' => $dbProfiles,
            ], 200, 'Successfully get Profile Data');

            return response()->json($profileList, 200);
        } catch (\Exception $e) {
            LogHelper::logError($e, [], 500, 'Gagal mengambil profil dari Mikrotik.');
            return response()->json(['error' => 'Gagal mengambil profil dari Mikrotik.'], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'profiles' => 'required|array',
            'profiles.*.profile_name' => 'required|string',
            // tambahkan validasi sesuai field lainnya...
        ]);

        foreach ($request->profile as $profileData) {
            DB::table('ppp_profiles')->insert([
                'profile_name' => $profileData['profile_name'],
                'max_upload' => $profileData['max_upload'],
                'max_download' => $profileData['max_download'],
                'min_upload' => $profileData['min_upload'],
                'min_download' => $profileData['min_download'],
                'rasio_cir' => $profileData['rasio_cir'],
                'priority' => $profileData['priority'],
                'mikrotik_name' => $profileData['mikrotik_name'],
                'db_profile_id' => $profileData['db_profile_id'],
                'db_profile_name' => $profileData['db_profile_name'],
                'dns_server' => $profileData['dns_server'],
                'use_encryption' => $profileData['use_encryption'],
                'rate_limit' => $profileData['rate_limit'],
                'only_one' => $profileData['only_one'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Profiles saved successfully']);
    }

    public function export($mikrotikSlug)
    {
        $uri = request()->path(); // Get current URI
        $logBase = 'ppp-profiles/export';

        try {
            $mikrotik = Mikrotik::where('slug', $mikrotikSlug)
                ->where('disabled', 'false')
                ->firstOrFail();

            LogHelper::logInfo(request()->path(), [
                'mikrotik' => $mikrotik->slug,
                'step' => 'export_started'
            ], 200, 'Export initiated');

            // Get all active profiles
            $profilesToExport = PppProfile::where('disabled', 'false')
                ->whereNotNull('profile_name')
                ->get();

            // Validate if there are profiles to export
            if ($profilesToExport->isEmpty()) {
                LogHelper::logWarning(
                    $uri,
                    [
                        'mikrotik' => $mikrotik->only(['id', 'name']),
                        'profile_count' => 0
                    ],
                    404,
                    'Export failed: No active profiles found'
                );

                return response()->json([
                    'success' => false,
                    'message' => 'No active profiles found to export'
                ], 404);
            }

            // Batch the jobs
            $profileJobs = $profilesToExport->map(fn($profile) => new CreatePppProfileToRouter($mikrotik, $profile));

            $batch = Bus::batch($profileJobs)
                ->then(function (Batch $batch) use ($uri, $logBase) {
                    LogHelper::logInfo(
                        $uri,
                        [
                            'batch_id' => $batch->id ?? 'unknown',
                            'successful_jobs' => $batch->totalJobs - $batch->failedJobs,
                            'failed_jobs' => $batch->failedJobs
                        ],
                        200,
                        "$logBase completed"
                    );
                })
                ->catch(function (Batch $batch, Throwable $e) use ($uri, $logBase) {
                    LogHelper::logError(
                        $uri,
                        [
                            'batch_id' => $batch->id ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => substr($e->getTraceAsString(), 0, 500)
                        ],
                        500,
                        "$logBase failed"
                    );
                })
                ->finally(function (Batch $batch) use ($uri, $logBase) {
                    try {
                        LogHelper::logInfo(
                            $uri,
                            [
                                'batch_id' => $batch->id ?? 'unknown',
                                'processed_jobs' => $batch->processedJobs()
                            ],
                            200,
                            "$logBase finalized"
                        );
                    } catch (Throwable $e) {
                        // Fallback logging jika gagal
                        LogHelper::logError(
                            $uri,
                            [
                                'batch_id' => $batch->id ?? 'unknown',
                                'error' => $e->getMessage(),
                                'trace' => substr($e->getTraceAsString(), 0, 500)
                            ],
                            500,
                            "$logBase failed to log batch finalization"
                        );
                    }
                })
                ->dispatch();
            // $batchId = 'temp-batch-' . Str::random(8);

            LogHelper::logInfo(
                $uri,
                [
                    'batch_id' => $batch->id ?? 'unknown',
                    'mikrotik' => $mikrotik->only(['id', 'name']),
                    'profile_count' => $profilesToExport->count()
                ],
                202,
                "$logBase started"
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile export process started successfully',
                'data' => [
                    'total_profiles' => $profilesToExport->count(),
                    'batch_id' => $batch->id ?? null,
                    'mikrotik' => $mikrotik->only(['id', 'name', 'host'])
                ],
                'timestamp' => now()->toDateTimeString()
            ], 202);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            LogHelper::logError(request()->path(), [
                'slug' => $mikrotikSlug,
                'error' => 'Mikrotik not found'
            ], 404, 'Mikrotik lookup failed');

            return response()->json([
                'success' => false,
                'message' => 'Mikrotik device not found'
            ], 404);
        } catch (Throwable $e) {
            LogHelper::logError(
                $uri,
                [
                    'mikrotik' => $mikrotik->only(['id', 'name']),
                    'error' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 500) // Truncated trace
                ],
                500,
                "$logBase exception"
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to start export process',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function enable($id)
    {
        $profile = PppProfile::findOrFail($id);
        $profile->disabled = 'false';
        $profile->save();

        return redirect()->back()->with('success', 'Profile enabled successfully.');
    }

    public function disable($id)
    {
        $profile = PppProfile::findOrFail($id);
        $profile->disabled = 'true';
        $profile->save();

        return redirect()->back()->with('success', 'Profile disabled successfully.');
    }
}
