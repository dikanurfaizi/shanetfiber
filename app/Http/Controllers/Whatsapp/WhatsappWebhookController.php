<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Services\WhatsappWebhook\WebhookValidator;
use App\Services\WhatsappWebhook\WebhookEventRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\LogHelper;

class WhatsappWebhookController extends Controller
{
    public function handle(
        Request $request,
        WebhookValidator $validator,
        WebhookEventRouter $router
    ) {
        $traceId = (string) Str::uuid();
        $payload = $request->getContent();

        // 1. Validasi signature
        if (! $validator->validateSignature($payload, $request)) {
            return response()->json(
                ['error' => 'Unauthorized'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // 2. Parse JSON
        $data = $validator->parseJson($payload, $traceId);
        if ($data === null) {
            return response()->json(
                ['error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            // 3. Routing ke handler
            $router->route($data, $traceId);

            return response()->json(
                ['status' => 'OK'],
                Response::HTTP_OK
            );
        } catch (\Throwable $e) {
            // kamu sudah punya LogHelper, bisa dipakai di sini juga
            LogHelper::logError(null, [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ], 500, '[Webhook] Error handling event', $traceId);

            return response()->json(
                ['error' => 'Server error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
