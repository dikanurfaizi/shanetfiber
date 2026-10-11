<?php

namespace App\Http\Controllers\WhatsApp;

use App\Helpers\ExecutionTimer;
use App\Http\Controllers\Controller;
use App\Services\WhatsAppWebhook\WebhookValidator;
use App\Services\WhatsAppWebhook\WebhookEventRouter;
use App\Services\WhatsAppWebhook\Meta\MetaWebhookEventRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\LogHelper;

class WhatsAppWebhookController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | ONPREM WEBHOOK (TIDAK DIUBAH)
    |--------------------------------------------------------------------------
    */
    public function onpremWebhook(
        Request $request,
        WebhookValidator $validator,
        WebhookEventRouter $router
    ) {
        $traceId = (string) Str::uuid();
        $timer = new ExecutionTimer();
        $payload = $request->getContent();

        if (! $validator->validateSignature($payload, $request)) {
            return response()->json(
                ['error' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $data = $validator->parseJson($payload, $traceId);
        if ($data === null) {
            return response()->json(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $router->route($data, $traceId);

            return response()->json(
                ['status' => 'OK'],
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500, '[ONPREM] Error handling event', $traceId, $timer->lap());

            return response()->json(
                ['error' => 'Server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | META CLOUD WEBHOOK
    |--------------------------------------------------------------------------
    */
    public function metaWebhook(
        Request $request,
        MetaWebhookEventRouter $router
    ) {
        $traceId = (string) Str::uuid();

        $rawPayload = $request->getContent();

        if (!$this->isValidMetaSignature($request)) {
            LogHelper::logWarning(null, [], 401, '[META] Invalid signature', $traceId);
            return response('Invalid signature', 401);
        }

        $data = json_decode($rawPayload, true);

        if (!is_array($data)) {
            LogHelper::logWarning(null, [], 400, '[META] Invalid JSON', $traceId);
            return response('Invalid JSON', 400);
        }

        try {
            $router->route($data, $traceId);

            return response('EVENT_RECEIVED', 200);
        } catch (\Throwable $e) {
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500, '[META] Error handling event', $traceId);

            return response('Server error', 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | META VERIFY ENDPOINT
    |--------------------------------------------------------------------------
    */
    public function metaVerify(Request $request)
    {
        if (
            $request->query('hub_mode') === 'subscribe' &&
            $request->query('hub_verify_token') === config('services.meta.verify_token')
        ) {
            return response($request->query('hub_challenge'), 200);
        }

        return response('Forbidden', 403);
    }

    /*
    |--------------------------------------------------------------------------
    | META SIGNATURE VALIDATION
    |--------------------------------------------------------------------------
    */
    private function isValidMetaSignature(Request $request): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();

        $expected = 'sha256=' . hash_hmac(
            'sha256',
            $payload,
            config('services.meta.app_secret')
        );

        return hash_equals($expected, $signature);
    }
}
