<?php

namespace App\Services\Customers;

use App\Models\Mikrotik;
use App\Models\PppType;
use App\Models\User;
use App\Models\Member;
use App\Models\Customer;
use App\Models\Packages\Package;
use App\Models\Billing\BillingPeriode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Helpers\FormatReceiver;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;
use App\Services\Routerboard\TestConnectionService;

class CustomerFormService
{
    private TestConnectionService $testConnectionService;

    public function __construct(
        TestConnectionService $testConnectionService
    ) {
        $this->testConnectionService = $testConnectionService;
    }

    public function getCreateFormData(string $traceId): array
    {
        try {
            $data = [
                'mikrotiks' => Mikrotik::where('disabled', 'false')
                    ->orderBy('name', 'ASC')
                    ->pluck('name', 'id'),
                'pppservices' => PppType::orderBy('name', 'ASC')->pluck('name', 'id'),
                'pppprofiles' => $this->getPackageProfiles($traceId),
                'users' => User::all(),
                'members' => Member::all()
            ];

            LogHelper::logInfo(
                null,
                [
                    'mikrotiks_count' => $data['mikrotiks']->count(),
                    'pppservices_count' => $data['pppservices']->count(),
                    'pppprofiles_count' => $data['pppprofiles']->count(),
                    'users_count' => $data['users']->count(),
                    'members_count' => $data['members']->count()
                ],
                200,
                'Successfully retrieved create form data',
                $traceId
            );

            return $data;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ],
                500,
                'Failed to retrieve create form data',
                $traceId
            );
            throw $e;
        }
    }

    private function getPackageProfiles(string $traceId): Collection
    {
        try {
            $profiles = DB::table('packages')
                ->join('ppp_profiles', 'packages.profile', '=', 'ppp_profiles.profile_name')
                ->where('ppp_profiles.disabled', 'false')
                ->orderBy('packages.name')
                ->select('packages.id', 'packages.name', 'packages.price')
                ->get()
                ->mapWithKeys(fn($package) => [
                    $package->id => $package->name . ' - Rp ' . number_format($package->price, 0, ',', '.')
                ]);

            LogHelper::logInfo(
                null,
                [
                    'profiles_count' => $profiles->count()
                ],
                200,
                'Successfully retrieved package profiles',
                $traceId
            );

            return $profiles;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ],
                500,
                'Failed to retrieve package profiles',
                $traceId
            );
            return collect();
        }
    }

    public function getEditFormData(Customer $customer, string $traceId): array
    {
        try {
            $formatter = new FormatReceiver();
            $router = Mikrotik::find($customer->mikrotik_id);
            $serverDisconnect = $this->checkServerStatus($router, $traceId);

            $data = [
                'customer' => $customer,
                'customer_number' => $formatter->formatReceiver($customer->contact, null, false),
                'users' => User::all(),
                'packages' => Package::where('disabled', 'false')->orderBy('price', 'ASC')->get(),
                'members' => Member::all(),
                'serverDisconnect' => $serverDisconnect,
                'availablePeriodes' => $this->getAvailablePeriodes($traceId)
            ];

            LogHelper::logInfo(
                null,
                [
                    'customer_id' => $customer->id,
                    'users_count' => $data['users']->count(),
                    'packages_count' => $data['packages']->count(),
                    'members_count' => $data['members']->count(),
                    'periodes_count' => $data['availablePeriodes']->count()
                ],
                200,
                'Successfully retrieved edit form data',
                $traceId
            );

            return $data;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ],
                    'customer_id' => $customer->id ?? null
                ],
                500,
                'Failed to retrieve edit form data',
                $traceId
            );
            throw $e;
        }
    }

    private function checkServerStatus(Mikrotik $router, string $traceId): string
    {
        if ($router->disabled == 'false') {
            try {
                $this->testConnectionService->checkOnline($router, $traceId);
                $serverDisconnect = 'false';

                LogHelper::logInfo(
                    null,
                    [
                        'router_id' => $router->id,
                        'status' => 'online'
                    ],
                    200,
                    'Server connection successful',
                    $traceId
                );
            } catch (\Throwable $e) {
                $serverDisconnect = 'true';

                LogHelper::logError(
                    null,
                    [
                        'exception' => [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ],
                        'router_id' => $router->id
                    ],
                    500,
                    'Server connection failed',
                    $traceId
                );
            }
        } else {
            $serverDisconnect = 'true';
            LogHelper::logWarning(
                null,
                [
                    'router_id' => $router->id,
                    'status' => 'disabled'
                ],
                400,
                'Server is disabled',
                $traceId
            );
        }
        return $serverDisconnect;
    }

    private function getAvailablePeriodes(string $traceId): Collection
    {
        try {
            $current = Carbon::createFromFormat('F Y', BillingPeriode::latest('created_at')->first()->name);
            $periodes = collect(range(0, 2))->map(fn($i) => $current->copy()->addMonths($i)->format('F Y'));

            LogHelper::logInfo(
                null,
                [
                    'periodes_count' => $periodes->count()
                ],
                200,
                'Successfully retrieved available periodes',
                $traceId
            );

            return $periodes;
        } catch (\Throwable $e) {
            LogHelper::logError(
                null,
                [
                    'exception' => [
                        'message' => $e->getMessage(),
                        'code' => $e->getCode(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ],
                500,
                'Failed to retrieve available periodes',
                $traceId
            );
            return collect();
        }
    }
}
