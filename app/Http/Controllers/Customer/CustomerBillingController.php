<?php

namespace App\Http\Controllers\Customer;

use App\Events\PackageChangesApplied;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Customers\Customer;
use App\Services\Customers\Billing\BillingUpdateService;
use DomainException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerBillingController extends Controller
{
    public function update(
        Request $request,
        Customer $customer,
        BillingUpdateService $updateService
    ) {
        $startTime = microtime(true);
        $traceId = (string) Str::uuid();
        $this->authorize('billing-edit', $customer);
        try {

            $validated = $request->validate([
                'package_id'           => ['required', 'exists:packages,id'],
                'package_duration_id'  => ['required', 'exists:package_durations,id'],
                'member_id'            => ['nullable', 'exists:memberships,id'],
                'billing_option'       => ['nullable', 'in:monthly,one_time'],
                'billing_periode_name' => ['nullable', 'string'],

                'addons'               => ['nullable', 'array'],
                'addons.*.addon_id'    => ['nullable', 'exists:addons,id'],
                'addons.*.qty'         => [
                    'nullable',
                    'integer',
                    'min:1',
                    'exclude_without:addons.*.addon_id',
                ],
                'addons.*.unit_price'  => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'exclude_without:addons.*.addon_id',
                ],
            ]);

            LogHelper::logInfo(null, ['payload_addon' => $validated['addons'] ?? []], 200, 'ADDONS_PAYLOAD', $traceId);

            $addons = collect($validated['addons'] ?? [])
                ->filter(
                    fn($a) =>
                    !empty($a['addon_id']) &&
                        isset($a['qty']) && (int) $a['qty'] > 0 &&
                        isset($a['unit_price'])
                )
                ->map(fn($a) => [
                    'addon_id'   => (int) $a['addon_id'],
                    'qty'        => (int) $a['qty'],
                    'unit_price' => (float) $a['unit_price'],
                ])
                ->values();

            $validated['addons'] = $addons;

            $result = $updateService->update(
                $customer,
                $validated,
                $traceId
            );

            foreach ($result['events'] as $event) {
                event($event);
            }

            return redirect()
                ->route('customers.show', [$customer->slug, 'tab' => 'billing'])
                ->with('billing_update_result', $result['results']);
        } catch (DomainException $e) {
            LogHelper::logError($e, [
                'customer_id' => $customer->id,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 422, 'Failed to update customer billing', $traceId);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update billing configuration: ' . $e->getMessage());
        } catch (Exception $e) {
            LogHelper::logError($e, [
                'customer_id' => $customer->id,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], 500, 'Failed to update customer billing', $traceId);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update billing configuration: ' . $e->getMessage());
        }
    }
}
