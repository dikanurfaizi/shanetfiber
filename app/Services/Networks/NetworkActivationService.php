<?php

namespace App\Services\Networks;

use App\Models\Mikrotik;
use App\Models\AutoIsolir;
use App\Services\Routerboard\Ppp\SecretService;
use App\Services\Routerboard\IpStaticService;
use App\Http\Resources\Pppoe\SecretResource;
use App\Jobs\AddPaymentBeforeDueDateJob;
use App\Jobs\AddPaymentAfterDueDateJob;
use App\Jobs\AddPaymentIpStaticAfterDueDateJob;
use App\Helpers\LogHelper;
use Illuminate\Support\Carbon;

class NetworkActivationService
{
    public function __construct(
        private SecretService $secretService,
        private IpStaticService $ipStaticService,
    ) {}

    /**
     * Entry point
     */
    public function handle(
        array $customerData,
        string $profile,
        string $periodeName,
        string $traceId
    ): void {
        $router     = Mikrotik::find($customerData['mikrotik_id']);
        $autoIsolir = AutoIsolir::where('mikrotik_id', $customerData['mikrotik_id'])->first();

        if (!$router || !$autoIsolir) {
            LogHelper::logWarning(null, [
                'mikrotik_id' => $customerData['mikrotik_id'],
                'router'      => (bool) $router,
                'auto_isolir' => (bool) $autoIsolir,
            ], 200, '[Tripay][Network] Router or AutoIsolir not found', $traceId);
            return;
        }

        if ($router->disabled === 'true' || $autoIsolir->disabled === 'true') {
            LogHelper::logInfo(null, [
                'router_id'     => $router->id,
                'auto_isolir_id' => $autoIsolir->id,
            ], 200, '[Tripay][Network] Router/AutoIsolir disabled, skip', $traceId);
            return;
        }

        $comment = $this->buildComment(
            $autoIsolir,
            $customerData['activation_date'],
            $periodeName,
            $traceId
        );

        match ($customerData['internet_service']) {
            'ppp'       => $this->handlePpp($router, $autoIsolir, $customerData, $profile, $comment, $traceId),
            'ip_static' => $this->handleIpStatic($router, $autoIsolir, $customerData, $comment, $traceId),
            default     => LogHelper::logWarning(
                null,
                ['internet_service' => $customerData['internet_service']],
                200,
                '[Tripay][Network] Unknown internet service',
                $traceId,
            ),
        };
    }

    /* ==========================================================
     * PPP HANDLER
     * ========================================================== */
    protected function handlePpp(
        Mikrotik $router,
        AutoIsolir $autoIsolir,
        array $data,
        string $profile,
        string $comment,
        string $traceId
    ): void {
        $secret = $this->secretService
            ->getSecretById($router, $data['secret_id'], $traceId);

        if ($secret === 'error') {
            throw new \RuntimeException('PPP Secret not found');
        }

        $currentProfile = SecretResource::collection($secret)->value('profile');

        $job = $currentProfile === $profile
            ? new AddPaymentBeforeDueDateJob(
                $router,
                $comment,
                $data['secret_id'],
                $traceId
            )
            : new AddPaymentAfterDueDateJob(
                $router,
                $profile,
                $comment,
                $data['secret_id'],
                $data['username'],
                $traceId
            );

        dispatch($job);

        LogHelper::logInfo(null, [
            'router_id' => $router->id,
            'secret_id' => $data['secret_id'],
            'job'       => get_class($job),
        ], 200, '[Tripay][Network] PPP job dispatched', $traceId);
    }

    /* ==========================================================
     * IP STATIC HANDLER
     * ========================================================== */
    protected function handleIpStatic(
        Mikrotik $router,
        AutoIsolir $autoIsolir,
        array $data,
        string $comment,
        string $traceId
    ): void {
        $commentFull = $data['username'] . '_' . $comment;

        $address = $this->ipStaticService
            ->getAddressListByIpAddress($router, $data['ip_static'], $traceId);

        $job = isset($address[0]['list'])
            && $address[0]['list'] !== $autoIsolir->address_list_isolir
            ? new AddPaymentBeforeDueDateJob(
                $router,
                $data['simpleque_id'],
                $comment,
                $traceId
            )
            : new AddPaymentIpStaticAfterDueDateJob(
                $router,
                $data['ip_static'],
                $data['simpleque_id'],
                $autoIsolir->address_list_isolir,
                $comment,
                $commentFull,
                $traceId
            );

        dispatch($job);

        LogHelper::logInfo(null, [
            'router_id' => $router->id,
            'ip_static' => $data['ip_static'],
            'job'       => get_class($job),
        ], 200, '[Tripay][Network] IP Static job dispatched', $traceId);
    }

    /* ==========================================================
     * COMMENT BUILDER
     * ========================================================== */
    protected function buildComment(
        AutoIsolir $autoIsolir,
        int $activationDate,
        string $periodeName,
        string $traceId
    ): string {
        $nextMonth = Carbon::parse($periodeName)->addMonth()->format('m_Y');

        $adjustedDate = min(
            $activationDate,
            Carbon::now()->addMonth()->daysInMonth
        );

        $comment = $autoIsolir->activation_date === 'false'
            ? $autoIsolir->comment_payment
            : "{$autoIsolir->comment_unpayment}_{$adjustedDate}_{$nextMonth}";

        LogHelper::logInfo(null, [
            'comment' => $comment,
        ], 200, '[Tripay][Network] Comment built', $traceId);

        return $comment;
    }
}
