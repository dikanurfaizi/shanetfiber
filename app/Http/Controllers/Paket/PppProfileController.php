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
    public function show(PppProfile $pppProfiles)
    {
        return view('packets.profiles.show', compact('pppProfiles'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PppProfile $profile)
    {
        $mikrotiks = Mikrotik::where('disabled', 'false')->orderBy('name')->get();

        // Mengirimkan satu objek PppProfile, bukan koleksi
        return view('packets.profiles.edit', [
            'mikrotiks' => $mikrotiks,
            'pppProfile' => $profile,  // Mengirimkan model langsung
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PppProfile $pppProfile)
    {
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

        $data['script_on_up'] = $request->input('on_up');
        $data['script_on_down'] = $request->input('on_down');
        $data['insert_queue_before'] = $request->input('insert_before');

        $maxLimitUpload = $request->input('max_upload');
        $maxLimitDownload = $request->input('max_download');

        // proses rate_limit
        $patternUnit = '/[0-9]/';
        $patternLimit = '/[^0-9]/';

        $unitUpload = strtolower(preg_replace($patternUnit, '', $maxLimitUpload));
        $maxLimitUploadValue = (int) preg_replace($patternLimit, '', $maxLimitUpload);
        if ($unitUpload === 'k') {
            $maxLimitUploadValue = $maxLimitUploadValue / 1000;
        } elseif ($unitUpload === '' || $unitUpload === null) {
            $maxLimitUploadValue = $maxLimitUploadValue . 'M';
        }
        $maxUpload = $maxLimitUploadValue . 'M';

        $unitDownload = strtolower(preg_replace($patternUnit, '', $maxLimitDownload));
        $maxLimitDownloadValue = (int) preg_replace($patternLimit, '', $maxLimitDownload);
        if ($unitDownload === 'k') {
            $maxLimitDownloadValue = $maxLimitDownloadValue / 1000;
        } elseif ($unitDownload === '' || $unitDownload === null) {
            $maxLimitDownloadValue = $maxLimitDownloadValue . 'M';
        }
        $maxDownload = $maxLimitDownloadValue . 'M';

        $rateLimitMikrotik = $maxUpload . '/' . $maxDownload;

        if ($pppProfile->rate_limit !== $rateLimitMikrotik) {
            $routers = Mikrotik::where('disabled', 'false')->get();
            foreach ($routers as $router) {
                $updateResult = $this->secretService->updateRateLimitProfileSecret($router, $rateLimitMikrotik, $pppProfile->profile_name);

                if (isset($updateResult['after'])) {
                    return redirect()->back()->withErrors([
                        'msg' => $updateResult['after']['message'],
                    ]);
                }
            }
            $data['rate_limit'] = $rateLimitMikrotik;
        }

        // dd($pppProfile);
        $pppProfile->update($data);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'Packet updated successfully');
    }

    public function delete(PppProfile $pppProfile)
    {
        // dd($pppProfile);
        return view('packets.profiles.delete', compact('pppProfile'));
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

        return view('packets.profiles.import', compact('mikrotikServers'));
    }

    public function get_mikrotik_profiles(Request $request)
    {
        $sourceMikrotikId = $request->mikrotik_id;
        if (!$sourceMikrotikId) {
            return response()->json(['error' => 'Mikrotik ID is required'], 400);
        }

        try {
            $sourceRouter = Mikrotik::findOrFail($sourceMikrotikId);

            $mikrotikProfiles = $this->secretService->getPppProfile($sourceRouter);
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

            // LogHelper::logWarning(null, [
            //     'mikrotik_profiles' => $mikrotikProfiles,
            //     'db_profiles' => $dbProfiles,
            // ], 200, 'Successfully get Profile Data');

            return response()->json($profileList, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Mikrotik server not found'], 404);
        } catch (\Exception $e) {
            LogHelper::logError($e, [], 500, 'Gagal mengambil profil dari Mikrotik.');
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
