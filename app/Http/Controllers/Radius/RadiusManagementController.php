<?php

namespace App\Http\Controllers\Radius;

use App\Events\Radius\RadiusUserProvisionRequested;
use App\Helpers\ExecutionTimer;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Models\Packages\PppProfile;
use App\Models\Websystem;
use App\Services\Mikrotik\MikrotikActivation;
use App\Services\Radius\RadiusProvisioningStateService;
use App\Services\Radius\RadiusUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Exception;

class RadiusManagementController extends Controller
{
    public function __construct(
        protected RadiusUserService $radiusUserService,
        protected RadiusProvisioningStateService $radiusProvisioningStateService,
        protected MikrotikActivation $mikrotikActivation
    ) {}

    private function startTrace(): array
    {
        return [
            'traceId' => (string) Str::uuid(),
            'timer'   => new ExecutionTimer(),
        ];
    }

    /**
     * Halaman Index Monitoring Provisioning RADIUS & Mikrotik
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status'); // 'synced', 'failed', 'pending'

        $query = Customer::query()
            ->with(['provisioningState', 'activeSubscription.package'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%");
            })
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->whereHas('provisioningState', function ($sq) use ($statusFilter) {
                    $sq->where('radius_status', $statusFilter);
                });
            });

        $customers = $query->orderBy('name', 'asc')
            ->paginate(20)
            ->appends($request->query());

        return view('radius.index', compact('customers', 'search', 'statusFilter'));
    }

    /**
     * Action Export Massal Seluruh Customer Aktif ke Database RADIUS
     */
    public function exportActiveToRadius(Request $request): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            Artisan::call('radius:sync-customers', ['--active-only' => true]);

            LogHelper::logInfo(
                null,
                ['triggered_by' => auth()->id()],
                200,
                'Bulk export active customers to RADIUS triggered successfully',
                $traceId,
                $timer->lap()
            );

            return redirect()->back()->with('success', __('radius.messages.export_active_success'));
        } catch (Exception $e) {
            LogHelper::logError(
                null,
                ['error' => $e->getMessage()],
                500,
                'Failed to execute bulk export to RADIUS',
                $traceId,
                $timer->lap()
            );

            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.export_active_failed', ['error' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Manual Sync Single Customer ke FreeRADIUS via Event
     */
    public function syncSingleToRadius(Customer $customer): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            $subscription = $customer->activeSubscription;
            $pppProfile = null;
            if ($subscription && $subscription->package) {
                $pppProfile = PppProfile::where('profile_name', $subscription->package->profile)->first();
            }

            event(new RadiusUserProvisionRequested(
                customer: $customer,
                pppProfile: $pppProfile,
                traceId: $traceId
            ));

            LogHelper::logInfo(
                null,
                ['customerId' => $customer->id],
                200,
                "Single customer sync to RADIUS event dispatched: {$customer->name}",
                $traceId,
                $timer->lap()
            );

            return redirect()->back()->with('success', __('radius.messages.sync_radius_queued', ['name' => $customer->name]));
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.sync_radius_failed', ['error' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Fallback Action: Sync Manual Single Customer ke API Mikrotik (Legacy Backup)
     */
    public function syncSingleToMikrotik(Customer $customer): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            $this->mikrotikActivation->handle($customer, $customer->activeSubscription, $traceId);

            $this->radiusProvisioningStateService->updateProvisioningState(
                customerId: $customer->id,
                target: 'mikrotik',
                status: 'synced'
            );

            return redirect()->back()->with('success', __('radius.messages.sync_mikrotik_success', ['name' => $customer->name]));
        } catch (Exception $e) {
            $this->radiusProvisioningStateService->updateProvisioningState(
                customerId: $customer->id,
                target: 'mikrotik',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.sync_mikrotik_failed', ['error' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Action Disconnect / Kick Session Aktif Single Customer (CoA / PoD)
     */
    public function disconnectCustomer(Customer $customer): RedirectResponse
    {
        ['traceId' => $traceId, 'timer' => $timer] = $this->startTrace();

        try {
            $username = $customer->username;

            if (empty($username)) {
                return redirect()->back()->withErrors([
                    'msg' => __('radius.messages.customer_no_username')
                ]);
            }

            // Panggil method disconnectUser dari RadiusUserService
            $disconnected = $this->radiusUserService->disconnectUser(
                username: $username,
                traceId: $traceId,
                timer: $timer
            );

            if ($disconnected) {
                LogHelper::logInfo(
                    null,
                    ['customerId' => $customer->id, 'username' => $username],
                    200,
                    "Customer disconnect packet sent successfully: {$customer->name}",
                    $traceId,
                    $timer->lap()
                );

                return redirect()->back()->with('success', __('radius.messages.disconnect_success', ['name' => $customer->name]));
            }

            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.disconnect_failed', ['name' => $customer->name])
            ]);
        } catch (Exception $e) {
            LogHelper::logError(
                null,
                ['customerId' => $customer->id, 'error' => $e->getMessage()],
                500,
                'Exception during customer disconnect request',
                $traceId,
                $timer->lap()
            );

            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.disconnect_failed_error', ['error' => $e->getMessage()])
            ]);
        }
    }

    /**
     * Halaman Monitoring Session Aktif RADIUS
     */
    public function activeSessions(): View
    {
        $activeSessions = $this->radiusUserService->getActiveSessions();
        return view('radius.active_sessions', compact('activeSessions'));
    }

    /**
     * Tampilkan Halaman Setting Mode Provisioning
     */
    public function settings(): View
    {
        $webSystem = Websystem::first();
        $currentDriver = $webSystem->provisioning_driver ?? 'both';

        return view('radius.settings', compact('currentDriver'));
    }

    /**
     * Simpan Perubahan Mode Provisioning Driver
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'provisioning_driver' => 'required|in:radius,mikrotik,both',
        ]);

        try {
            $webSystem = Websystem::first();
            if ($webSystem) {
                $webSystem->update([
                    'provisioning_driver' => $request->input('provisioning_driver')
                ]);
            }

            return redirect()->back()->with('success', __('radius.messages.settings_updated'));
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'msg' => __('radius.messages.settings_failed', ['error' => $e->getMessage()])
            ]);
        }
    }
}
