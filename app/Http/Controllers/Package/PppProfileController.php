<?php

namespace App\Http\Controllers\Package;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Mikrotik;
use App\Models\Packages\PppProfile;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\TestConnectionService;
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;
use App\Jobs\ExportMikrotik\CreatePppProfileToRouter;
use App\Jobs\UpdateRateLimitProfileSecretJob;

class PppProfileController extends Controller
{
    private SecretService $secretService;
    private TestConnectionService $testConnectionService;
    protected $model;

    public function __construct(SecretService $secretService, TestConnectionService $testConnectionService)
    {
        $this->secretService = $secretService;
        $this->testConnectionService = $testConnectionService;
        $this->model = PppProfile::class;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginate = 10;
        $profiles = PppProfile::orderBy('profile_name', 'ASC')->paginate($paginate);
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name')->get();
        return view('packages.profiles.index', [
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

        return view('packages.profiles.create', [
            'mikrotiks' => $mikrotiks,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, PppProfile $pppProfile)
    {
        $validated = $request->validate([
            'profile_name' => 'required|unique:ppp_profiles,profile_name',
            'dns_server' => 'nullable|string',
            'use_encryption' => 'required|string',
            'max_upload' => 'required_if:rate_limit,true',
            'max_download' => 'required_if:rate_limit,true',
            'rasio_cir' => 'required|numeric',
            'priority' => 'required',
        ], [
            'profile_name.required' => 'Profile name is required.',
            'profile_name.unique' => 'Profile name already exists.',
            'max_upload.required_if' => 'Maximum upload is required when rate limit is enabled.',
            'max_download.required_if' => 'Maximum download is required when rate limit is enabled.',
            'use_encryption.required' => 'Encryption setting is required.',
        ]);

        $calculation = $this->calculation(
            $request->max_upload,
            $request->max_download,
            $request->rasio_cir ?? 3.33,
            $request->burst_upload ?? '',
            $request->burst_download ?? '',
            $request->threshold_upload ?? '',
            $request->threshold_download ?? '',
            $request->burst_time_upload ?? '',
            $request->burst_time_download ?? '',
            $request->limit_at_upload ?? '',
            $request->limit_at_download ?? '',
            $request->priority ?? '',
        );

        // Destructuring hasil perhitungan
        [
            'maxLimitUpload'         => $maxUpload,
            'maxLimitDownload'       => $maxDownload,
            'minLimitUpload'         => $minUpload,
            'minLimitDownload'       => $minDownload,
            'rasioCir'               => $rasioCir,
            'rateLimitMikrotik'      => $rateLimit,
            'burstLimitUpload'       => $burstUpload,
            'burstLimitDownload'     => $burstDownload,
            'thresholdUpload'        => $thresholdUpload,
            'thresholdDownload'      => $thresholdDownload,
            'burstTimeUpload'        => $burstTimeUpload,
            'burstTimeDownload'      => $burstTimeDownload,
            'limitAtUpload'          => $limitAtUpload,
            'limitAtDownload'        => $limitAtDownload,
        ] = $calculation;

        $data = array_merge($validated, [
            'local_address'           => $request->local_address,
            'remote_address'          => $request->remote_address,
            'dns_server'              => $request->dns_server ?? '8.8.8.8, 8.8.4.4',
            'parent_queue'            => $request->parent_queue,
            'queue_type'              => $request->queue_type,
            'insert_queue_before'     => $request->insert_before,
            'rate_limit'              => $rateLimit,
            'use_encryption'          => $request->use_encryption,
            'script_on_up'            => $request->on_up,
            'script_on_down'          => $request->on_down,
            'change_tcp_mss'          => $request->change_tcp_mss,
            'use_upnp'                => $request->use_upnp,
            'comment'                 => $request->comment,
            'only_one'                => $request->only_one ?? 'yes',

            'max_upload'              => $maxUpload,
            'max_download'            => $maxDownload,
            'min_upload'              => $minUpload,
            'min_download'            => $minDownload,
            'rasio_cir'               => $rasioCir,

            'burst_upload'            => $burstUpload,
            'burst_download'          => $burstDownload,
            'burst_threshold_upload'  => $thresholdUpload,
            'burst_threshold_download' => $thresholdDownload,
            'burst_time_upload'       => $burstTimeUpload,
            'burst_time_download'     => $burstTimeDownload,
            'limit_at_upload'         => $limitAtUpload,
            'limit_at_download'       => $limitAtDownload,
        ]);

        $pppProfile->create($data);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile created successfully.');
    }

    private function calculation(
        string  $max_upload,
        string  $max_download,
        float   $rasio_cir = 3.33,
        ?string $burst_up = null,
        ?string $burst_down = null,
        ?string $threshold_up = null,
        ?string $threshold_down = null,
        ?string $burst_time_up = null,
        ?string $burst_time_down = null,
        ?string $limit_at_upload = null,
        ?string $limit_at_download = null,
        ?int    $priority = 8
    ) {
        // ── Helper untuk normalisasi input ──
        $normalize = function (?string $raw, bool $allowEmpty = true) {
            if ($raw === null || trim($raw) === '') {
                return $allowEmpty ? [null, null] : throw new \Exception('Input wajib diisi.');
            }

            $clean = str_replace(',', '.', trim($raw));
            $unit = strtolower(preg_replace('/[0-9.\s]/', '', $clean)) ?: 'k'; // Default ke K jika hanya angka

            if (!in_array($unit, ['k', 'm'])) {
                throw new \Exception("Satuan harus k atau M (input: $raw)");
            }

            $value = floatval(preg_replace('/[^0-9.]/', '', $clean));
            return [$value, $value . strtoupper($unit)];
        };

        // ── Normalisasi semua field ──
        $maxUp  = $normalize($max_upload, false);
        $maxDn  = $normalize($max_download, false);

        $burstUp = $normalize($burst_up);
        $burstDn = $normalize($burst_down);

        $thresUp = $normalize($threshold_up);
        $thresDn = $normalize($threshold_down);

        $limUp   = $normalize($limit_at_upload);
        $limDn   = $normalize($limit_at_download);

        $btUp = $burst_time_up !== null ? trim($burst_time_up) : null;
        $btDn = $burst_time_down !== null ? trim($burst_time_down) : null;

        // ── Bantu susun pasangan ──
        $pair = fn($a, $b) => ($a && $b) ? "$a/$b" : null;

        // Ambil string satuan (M/k)
        [$maxUpStr, $maxDnStr]     = [$maxUp[1], $maxDn[1]];
        [$burstUpStr, $burstDnStr] = [$burstUp[1] ?? null, $burstDn[1] ?? null];
        [$thresUpStr, $thresDnStr] = [$thresUp[1] ?? null, $thresDn[1] ?? null];
        [$limUpStr, $limDnStr]     = [$limUp[1] ?? null, $limDn[1] ?? null];

        $burst   = $pair($burstUpStr, $burstDnStr);
        $thres   = $pair($thresUpStr, $thresDnStr);
        $burstT  = $pair($btUp, $btDn);
        $limitAt = $pair($limUpStr, $limDnStr);
        $max     = $pair($maxUpStr, $maxDnStr);

        // Cek apakah hanya max yang valid
        $hasPartialOther = $burst && $thres && $burstT && $limitAt;
        $onlyMaxFilled = $max && !$hasPartialOther;

        // Susun final segments
        $segments = [];
        if ($max) $segments[] = $max;

        if (!$onlyMaxFilled) {
            if ($burst)   $segments[] = $burst;
            if ($thres)   $segments[] = $thres;
            if ($burstT)  $segments[] = $burstT;
            $segments[] = $priority ?? 8;
            if ($limitAt) $segments[] = $limitAt;
        }

        $rateLimit = implode(' ', $segments);

        return [
            'maxLimitUpload'      => $maxUpStr,
            'maxLimitDownload'    => $maxDnStr,
            'minLimitUpload'      => $limUpStr,
            'minLimitDownload'    => $limDnStr,
            'limitAtUpload'       => $limUpStr,
            'limitAtDownload'     => $limDnStr,
            'burstLimitUpload'    => $burstUpStr,
            'burstLimitDownload'  => $burstDnStr,
            'thresholdUpload'     => $thresUpStr,
            'thresholdDownload'   => $thresDnStr,
            'burstTimeUpload'     => $btUp,
            'burstTimeDownload'   => $btDn,
            'priority'            => $priority ?? 8,
            'rasioCir'            => $rasio_cir,
            'rateLimitMikrotik'   => $rateLimit,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(PppProfile $pppProfiles)
    {
        // dd($pppProfiles);
        return view('packages.profiles.show', compact('pppProfiles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PppProfile $profile)
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name')->get();

        // Mengirimkan satu objek PppProfile, bukan koleksi
        return view('packages.profiles.edit', [
            'mikrotiks' => $mikrotiks,
            'pppProfile' => $profile,  // Mengirimkan model langsung
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PppProfile $pppProfile)
    {
        $traceId = (string) Str::uuid();

        $data = $request->only([
            'profile_name',
            'local_address',
            'remote_address',
            'dns_server',
            'rate_limit',
            'rasio_cir',
            'priority',
            'parent_queue',
            'queue_type',
            'bridge_learning',
            'use_ipv6',
            'use_mpls',
            'use_compression',
            'use_encryption',
            'only_one',
            'change_tcp_mss',
            'use_upnp',
            'comment',
        ]);

        $data = array_merge($data, [
            'script_on_up' => $request->input('on_up'),
            'script_on_down' => $request->input('on_down'),
            'insert_queue_before' => $request->input('insert_before'),
        ]);

        $calculation = $this->calculation(
            $request->max_upload,
            $request->max_download,
            $request->rasio_cir ?? 3.33,
            $request->burst_upload ?? '',
            $request->burst_download ?? '',
            $request->threshold_upload ?? '',
            $request->threshold_download ?? '',
            $request->burst_time_upload ?? '',
            $request->burst_time_download ?? '',
            $request->limit_at_upload ?? '',
            $request->limit_at_download ?? '',
            $request->priority ?? '',
        );

        [
            'maxLimitUpload'      => $data['max_upload'],
            'maxLimitDownload'    => $data['max_download'],
            'minLimitUpload'      => $data['min_upload'],
            'minLimitDownload'    => $data['min_download'],
            'rasioCir'            => $data['rasio_cir'],
            'rateLimitMikrotik'   => $rateLimitMikrotik,
            'burstLimitUpload'    => $data['burst_upload'],
            'burstLimitDownload'  => $data['burst_download'],
            'thresholdUpload'     => $data['burst_threshold_upload'],
            'thresholdDownload'   => $data['burst_threshold_download'],
            'burstTimeUpload'     => $data['burst_time_upload'],
            'burstTimeDownload'   => $data['burst_time_download'],
            'limitAtUpload'       => $data['limit_at_upload'],
            'limitAtDownload'     => $data['limit_at_download'],
        ] = $calculation;

        $userId = UserAuth::getUserDetails()['userId']; // Atau auth()->id()

        $routers = Mikrotik::where('disabled', 'false')->get();

        foreach ($routers as $router) {
            UpdateRateLimitProfileSecretJob::dispatch(
                $router->id,
                $pppProfile->profile_name,
                $rateLimitMikrotik,
                $userId,
                $traceId
            );
        }

        $data['rate_limit'] = $rateLimitMikrotik;
        $pppProfile->update($data);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'Profile updated. Update to router is being processed via background job.');
    }

    public function delete(PppProfile $pppProfile)
    {
        // dd($pppProfile);
        return view('packages.profiles.delete', compact('pppProfile'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PppProfile $pppProfile)
    {
        $pppProfile->delete(); // hapus profile dari database
        return redirect()->route('ppp-profiles.index') // sesuai resource route kamu
            ->with('success', 'Profile deleted successfully');
    }


    public function viewServer(Request $request)
    {
        $mikrotikServers = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');

        return view('packages.profiles.import', compact('mikrotikServers'));
    }

    public function get_mikrotik_profiles(Request $request)
    {
        $traceId = (string) Str::uuid();
        $sourceMikrotikId = $request->mikrotik_id;
        if (!$sourceMikrotikId) {
            return response()->json(['error' => 'Mikrotik ID is required'], 400);
        }

        try {
            $sourceRouter = Mikrotik::findOrFail($sourceMikrotikId);

            $mikrotikProfiles = $this->secretService->getPppProfile($sourceRouter, $traceId);
            if (empty($mikrotikProfiles)) {
                return response()->json(['error' => 'No profiles found from Mikrotik'], 404);
            }

            $dbProfiles = PppProfile::all()->keyBy('profile_name');
            $profileList = collect();

            foreach ($mikrotikProfiles as $profile) {
                $profileName = $profile['name'] ?? '';
                $dbProfile = $dbProfiles->get($profileName);
                $status = $dbProfile ? 'conflict' : 'mikrotik';

                $profileList->push($this->mapProfile('mikrotik', $profile, $dbProfile, $sourceRouter->name, $status));

                if ($dbProfile) {
                    $profileList->push($this->mapProfile('database', $profile, $dbProfile, $sourceRouter->name, 'conflict'));
                }
            }

            return response()->json($profileList, 200);
        } catch (ModelNotFoundException $e) {
            LogHelper::logError($e, ['error' => $e->getMessage()], 500, 'Gagal mendapat informasi mikrotik', $traceId);
            return response()->json(['error' => 'Mikrotik server not found'], 404);
        } catch (\Exception $e) {
            LogHelper::logError($e, ['error' => $e->getMessage()], 500, 'Gagal mengambil profil dari Mikrotik.', $traceId);
            return response()->json(['error' => 'Gagal mengambil profil dari Mikrotik.'], 500);
        }
    }

    private function mapProfile($source, $profile, $dbProfile, $mikrotikName, $status)
    {
        return [
            'source' => $source,
            'profile_name' => $source === 'database' ? $dbProfile->profile_name : ($profile['name'] ?? ''),
            'max_upload' => $source === 'database' ? $dbProfile->max_upload : explode('/', $profile['rate-limit'] ?? '0/0')[0],
            'max_download' => $source === 'database' ? $dbProfile->max_download : explode('/', $profile['rate-limit'] ?? '0/0')[1],
            'min_upload' => $source === 'database' ? $dbProfile->min_upload : '0',
            'min_download' => $source === 'database' ? $dbProfile->min_download : '0',
            'rasio_cir' => $source === 'database' ? $dbProfile->rasio_cir : '',
            'priority' => $source === 'database' ? $dbProfile->priority : ($profile['priority'] ?? '8'),
            'mikrotik_name' => $mikrotikName,
            'status' => $status,
            'db_profile_id' => $dbProfile?->id,
            'db_profile_name' => $dbProfile?->profile_name,
            'dns_server' => $source === 'database' ? $dbProfile->dns_server : ($profile['dns-server'] ?? ''),
            'use_encryption' => $source === 'database' ? $dbProfile->use_encryption : ($profile['use-encryption'] ?? ''),
            'rate_limit' => $source === 'database' ? $dbProfile->rate_limit : ($profile['rate-limit'] ?? ''),
            'only_one' => $source === 'database' ? $dbProfile->only_one : ($profile['only-one'] ?? ''),
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
        ];
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
        $traceId = (string) Str::uuid();
        $uri = request()->path(); // Get current URI
        $logBase = 'ppp-profiles/export';

        try {
            $mikrotik = Mikrotik::where('slug', $mikrotikSlug)
                ->where('disabled', 'false')
                ->firstOrFail();

            LogHelper::logInfo(request()->path(), [
                'mikrotik' => $mikrotik->slug,
                'step' => 'export_started'
            ], 200, 'Export initiated', $traceId);

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
                    'Export failed: No active profiles found',
                    $traceId
                );

                return response()->json([
                    'success' => false,
                    'message' => 'No active profiles found to export'
                ], 404);
            }

            // Batch the jobs
            $profileJobs = $profilesToExport->map(
                fn($profile) =>
                new CreatePppProfileToRouter($mikrotik->id, $profile->id, $traceId)
            );

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
                "$logBase started",
                $traceId
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
            LogHelper::logError(
                request()->path(),
                [
                    'slug' => $mikrotikSlug,
                    'error' => 'Mikrotik not found'
                ],
                404,
                'Mikrotik lookup failed',
                $traceId
            );

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
                "$logBase exception",
                $traceId
            );

            return response()->json([
                'success' => false,
                'message' => 'Failed to start export process',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function export_one($profileId)
    {
        $traceId = (string) Str::uuid();
        $uri = request()->path();
        $logBase = 'ppp-profiles/export';

        $mikrotik = Mikrotik::where('disabled', 'false')->get();

        if ($mikrotik->isEmpty()) {
            return redirect()->route('ppp-profiles.index')
                ->with('error', 'Tidak ada perangkat Mikrotik yang tersedia untuk ekspor.');
        }

        try {
            foreach ($mikrotik as $device) {
                CreatePppProfileToRouter::dispatch($device->id, $profileId, $traceId)->onQueue('default');
                LogHelper::logInfo($uri, [], 200, 'Export job dispatched', $traceId);
            }

            return redirect()->route('ppp-profiles.index')
                ->with('success', "Ekspor profil dengan id {$profileId} berhasil dimulai.");
        } catch (Throwable $e) {
            LogHelper::logError(
                $uri,
                [
                    'error' => $e->getMessage(),
                    'trace' => Str::limit($e->getTraceAsString(), 500)
                ],
                500,
                "$logBase exception",
                $traceId
            );

            return redirect()->route('ppp-profiles.index')
                ->with('error', "Gagal mengekspor profil dengan id {$profileId}: " . $e->getMessage());
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
