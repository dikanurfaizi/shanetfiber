<?php

namespace App\Services\Billings;

use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Models\Customers\Customer;
use App\Jobs\Billing\CreateBillingJob;
use App\Jobs\Billing\CreateBillingIpStaticJob;
use App\Helpers\LogHelper;

class BillingUpdateCommentService
{
    public function dispatchBillingJobForCustomer(
        Customer $customer,
        Mikrotik $router,
        AutoIsolir $autoIsolir,
        float $price,
        string $traceId
    ): void {
        $startTime = microtime(true);
        $lastCheckpoint = $startTime;

        $context = [
            'customer_id'       => $customer->id,
            'router_id'         => $router->id,
            'internet_service'  => $customer->internet_service,
            'price'             => $price,
        ];

        try {
            // Tentukan comment
            $comment = $price > 0
                ? $autoIsolir->comment_unpayment
                : $autoIsolir->comment_payment;

            $jobs = [
                'ppp' => [
                    'class'       => CreateBillingJob::class,
                    'params'      => [$router, $comment, $customer->secret_id, $traceId],
                    'description' => 'PPP',
                ],
                'ip_static' => [
                    'class'       => CreateBillingIpStaticJob::class,
                    'params'      => [$router, $customer->simpleque_id, $comment, $traceId],
                    'description' => 'IP Static',
                ],
            ];

            $serviceType = $customer->internet_service;

            // Unknown service type
            if (!isset($jobs[$serviceType])) {
                $now = microtime(true);
                $exec = round($now - $lastCheckpoint, 3);
                $lastCheckpoint = $now;
                LogHelper::logWarning(
                    null,
                    $context,
                    400,
                    'Unsupported internet service type, billing job skipped',
                    $traceId,
                    $exec
                );
                return;
            }

            // Feature toggle OFF
            if (!config('services.shanetfiber.allow_mikrotik_actions')) {
                $now = microtime(true);
                $exec = round($now - $lastCheckpoint, 3);
                $lastCheckpoint = $now;
                LogHelper::logInfo(
                    null,
                    array_merge($context, [
                        'job' => $jobs[$serviceType]['description'],
                    ]),
                    200,
                    'Mikrotik action disabled by configuration, job not dispatched',
                    $traceId,
                    $exec
                );
                return;
            }

            // Dispatch job
            dispatch(new $jobs[$serviceType]['class'](...$jobs[$serviceType]['params']))
                ->onQueue('default');

            $now = microtime(true);
            $exec = round($now - $lastCheckpoint, 3);
            $lastCheckpoint = $now;

            LogHelper::logInfo(
                null,
                array_merge($context, [
                    'job' => $jobs[$serviceType]['description'],
                ]),
                200,
                'Billing job dispatched successfully',
                $traceId,
                $exec
            );
        } catch (\Throwable $e) {

            $now = microtime(true);
            $exec = round($now - $lastCheckpoint, 3);
            LogHelper::logError(
                null,
                array_merge($context, [
                    'exception'         => get_class($e),
                    'error'     => [
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    ],
                ]),
                500,
                'Failed to dispatch billing job',
                $traceId,
                $exec
            );

            throw $e;
        }
    }
}
