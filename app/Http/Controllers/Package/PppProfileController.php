<?php

namespace App\Http\Controllers\Package;

// Framework & Laravel Core
use App\Http\Controllers\Controller;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Form Requests
use App\Http\Requests\Package\GetMikrotikProfilesRequest;
use App\Http\Requests\Package\ImportPppProfileRequest;
use App\Http\Requests\Package\StorePppProfileRequest;
use App\Http\Requests\Package\UpdatePppProfileRequest;

// Models
use App\Models\Mikrotik;
use App\Models\Packages\PppProfile;

// Services & Helpers
use App\Helpers\LogHelper;
use App\Helpers\UserAuth;
use App\Services\Packages\PppProfileCalculationService;
use App\Services\Radius\RadiusProfileService;
use App\Services\Routerboard\Ppp\SecretService;

// Events & Jobs
use App\Events\Radius\RadiusProfileUpdateRequested;
use App\Jobs\ExportMikrotik\CreatePppProfileToRouter;
use App\Jobs\UpdateRateLimitProfileSecretJob;

// PHP Native Exceptions
use Throwable;

class PppProfileController extends Controller
{
    private SecretService $secretService;
    private RadiusProfileService $radiusProfileService;
    protected $model;

    public function __construct(SecretService $secretService, RadiusProfileService $radiusProfileService)
    {
        $this->secretService = $secretService;
        $this->radiusProfileService = $radiusProfileService;
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
    public function store(StorePppProfileRequest $request, PppProfileCalculationService $calculationService)
    {
        $traceId = (string) Str::uuid();

        // 1. Ekstrak data yang sudah lolos validasi dari Form Request
        $validated = $request->validated();

        // 2. Olah perhitungan CIR, burst, dan format rate limit via Service
        $calculatedData = $calculationService->calculate($validated);

        // 3. Gabungkan atribut form dengan hasil perhitungan kalkulasi
        $payload = array_merge($validated, $calculatedData, [
            'dns_server'          => $request->dns_server ?? '8.8.8.8, 8.8.4.4',
            'only_one'            => $request->only_one ?? 'yes',
            'insert_queue_before' => $request->insert_before,
            'script_on_up'        => $request->on_up,
            'script_on_down'      => $request->on_down,
        ]);

        // 4. Simpan ke database
        $profile = PppProfile::create($payload);

        // 5. Sinkronkan ke FreeRADIUS secara async/service
        $this->radiusProfileService->syncProfile($profile, $traceId);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'PPP Profile created successfully.');
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
    public function update(
        UpdatePppProfileRequest $request,
        PppProfile $pppProfile,
        PppProfileCalculationService $calculationService
    ) {
        $traceId = (string) Str::uuid();
        $validated = $request->validated();

        // 1. Olah kalkulasi bandwidth & format rate limit Mikrotik
        $calculatedData = $calculationService->calculate($validated);

        // 2. Susun payload pembaruan
        $payload = array_merge($validated, $calculatedData, [
            'insert_queue_before' => $request->input('insert_before'),
            'script_on_up'        => $request->input('on_up'),
            'script_on_down'      => $request->input('on_down'),
        ]);

        // 3. Update data profil di database utama
        $pppProfile->update($payload);

        // 4. Dispatch job pembaruan rate limit ke seluruh router MikroTik aktif
        $userId = auth()->id() ?? UserAuth::getUserDetails()['userId'];
        $routers = Mikrotik::where('disabled', 'false')->get();

        foreach ($routers as $router) {
            UpdateRateLimitProfileSecretJob::dispatch(
                $router->id,
                $pppProfile->profile_name,
                $calculatedData['rateLimitMikrotik'] ?? $pppProfile->rate_limit,
                $userId,
                $traceId
            );
        }

        // 5. Sinkronkan profil terbaru ke FreeRADIUS (radgroupreply)
        $this->radiusProfileService->syncProfile($pppProfile->refresh(), $traceId);

        return redirect()->route('ppp-profiles.index')
            ->with('success', 'Profile updated. Router update is being processed via background job.');
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
        $traceId = (string) Str::uuid();
        $this->radiusProfileService->deleteProfile($pppProfile, $traceId);
        $pppProfile->delete(); // hapus profile dari database
        return redirect()->route('ppp-profiles.index') // sesuai resource route kamu
            ->with('success', 'Profile deleted successfully');
    }


    public function viewServer(Request $request)
    {
        $mikrotikServers = Mikrotik::where('disabled', 'false')->orderBy('name', 'ASC')->pluck('name', 'id');

        return view('packages.profiles.import', compact('mikrotikServers'));
    }

    public function get_mikrotik_profiles(GetMikrotikProfilesRequest $request)
    {
        $traceId = (string) Str::uuid();

        try {
            $sourceRouter = Mikrotik::findOrFail($request->mikrotik_id);
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
            LogHelper::logError($e, ['mikrotik_id' => $request->mikrotik_id], 404, 'Mikrotik server not found', $traceId);
            return response()->json(['error' => 'Mikrotik server not found'], 404);
        } catch (\Exception $e) {
            LogHelper::logError($e, ['mikrotik_id' => $request->mikrotik_id], 500, 'Gagal mengambil profil dari Mikrotik', $traceId);
            return response()->json(['error' => 'Gagal mengambil profil dari Mikrotik: ' . $e->getMessage()], 500);
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

    public function import(ImportPppProfileRequest $request)
    {
        $traceId = (string) Str::uuid();
        $importedProfiles = $request->validated()['profiles'];

        DB::beginTransaction();
        try {
            foreach ($importedProfiles as $profileData) {
                $profile = PppProfile::updateOrCreate(
                    ['profile_name' => $profileData['profile_name']],
                    [
                        'max_upload'     => $profileData['max_upload'] ?? '0',
                        'max_download'   => $profileData['max_download'] ?? '0',
                        'min_upload'     => $profileData['min_upload'] ?? '0',
                        'min_download'   => $profileData['min_download'] ?? '0',
                        'rasio_cir'      => $profileData['rasio_cir'] ?? 3.33,
                        'priority'       => $profileData['priority'] ?? 8,
                        'dns_server'     => $profileData['dns_server'] ?? '8.8.8.8, 8.8.4.4',
                        'use_encryption' => $profileData['use_encryption'] ?? 'yes',
                        'rate_limit'     => $profileData['rate_limit'] ?? null,
                        'only_one'       => $profileData['only_one'] ?? 'yes',
                    ]
                );

                // Pemicu otomatis sinkronisasi ke FreeRADIUS
                $this->radiusProfileService->syncProfile($profile, $traceId);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($importedProfiles) . ' profile(s) imported and synced to FreeRADIUS successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            LogHelper::logError($e, ['count' => count($importedProfiles)], 500, 'Failed to import profiles', $traceId);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import profiles: ' . $e->getMessage()
            ], 500);
        }
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

    public function export_one(int $profileId)
    {
        $traceId = (string) Str::uuid();
        $uri = request()->path();
        $logBase = 'ppp-profiles/export';

        $profile = PppProfile::findOrFail($profileId);

        $mikrotik = Mikrotik::where('disabled', 'false')->get();

        if ($mikrotik->isEmpty()) {
            return redirect()->route('ppp-profiles.index')
                ->with('error', 'Tidak ada perangkat Mikrotik yang tersedia untuk ekspor.');
        }

        try {
            foreach ($mikrotik as $device) {
                CreatePppProfileToRouter::dispatch($device, $profile, $traceId)->onQueue('default');
                LogHelper::logInfo($uri, [], 200, 'Export job dispatched', $traceId);
            }

            return redirect()->route('ppp-profiles.index')
                ->with('success', "Ekspor profil {$profile->profile_name} berhasil dimulai.");
        } catch (Throwable $e) {
            LogHelper::logError(
                $uri,
                [
                    'error' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 500) . '...'
                ],
                500,
                "$logBase exception",
                $traceId
            );

            return redirect()->route('ppp-profiles.index')
                ->with('error', "Gagal mengekspor profil: " . $e->getMessage());
        }
    }

    public function enable(int $id)
    {
        $traceId = (string) Str::uuid();
        $profile = PppProfile::findOrFail($id);
        $profile->disabled = 'false';
        $profile->save();

        $this->radiusProfileService->syncProfile($profile, $traceId);

        return redirect()->back()->with('success', 'Profile enabled successfully.');
    }

    public function disable(int $id)
    {
        $traceId = (string) Str::uuid();
        $profile = PppProfile::findOrFail($id);
        $profile->disabled = 'true';
        $profile->save();

        $this->radiusProfileService->deleteProfile($profile, $traceId);

        return redirect()->back()->with('success', 'Profile disabled successfully.');
    }

    public function syncRadius(Request $request)
    {
        $traceId = (string) Str::uuid();

        try {
            event(new RadiusProfileUpdateRequested($traceId));

            return response()->json([
                'success' => true,
                'message' => 'Proses sinkronisasi PPP Profile ke FreeRADIUS berhasil dipicu secara asynchronous.',
                'trace_id' => $traceId
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memicu sinkronisasi RADIUS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize a single PPP Profile to FreeRADIUS database asynchronously/synchronously.
     */
    public function singleSyncRadius(Request $request, PppProfile $pppProfile)
    {
        $traceId = (string) Str::uuid();
        $uri = $request->path();

        LogHelper::logInfo($uri, [
            'profile_id'   => $pppProfile->id,
            'profile_name' => $pppProfile->profile_name,
        ], 200, 'Initiating single PPP Profile sync to FreeRADIUS', $traceId);

        try {
            // Eksekusi sinkronisasi profil tunggal
            $this->radiusProfileService->syncProfile($pppProfile, $traceId);

            LogHelper::logInfo($uri, [
                'profile_id'   => $pppProfile->id,
                'profile_name' => $pppProfile->profile_name,
            ], 200, 'Successfully synced single PPP Profile to FreeRADIUS', $traceId);

            // Pesan sukses terjemahan dinamis
            $message = __('package.profile.messages.sync_success', [
                'name' => $pppProfile->profile_name,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => $message,
                    'trace_id' => $traceId,
                ], 200);
            }

            return redirect()->back()->with('success', $message);
        } catch (Throwable $e) {
            LogHelper::logError($uri, [
                'profile_id'   => $pppProfile->id,
                'profile_name' => $pppProfile->profile_name,
                'error'        => $e->getMessage(),
                'trace'        => substr($e->getTraceAsString(), 0, 500),
            ], 500, 'Failed to sync single PPP Profile to FreeRADIUS', $traceId);

            // Pesan gagal terjemahan dinamis
            $errorMessage = __('package.profile.messages.sync_failed', [
                'name'  => $pppProfile->profile_name,
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 500);
            }

            return redirect()->back()->with('error', $errorMessage);
        }
    }
}
