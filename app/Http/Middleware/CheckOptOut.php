<?php

namespace App\Http\Middleware;

use App\Services\WhatsApp\OptOutService;
use Closure;

class CheckOptOut
{
    public function handle($request, Closure $next)
    {
        $recipient = $request->input('to');

        if (app(OptOutService::class)->shouldBlock($recipient)) {
            return response()->json([
                'error' => 'Recipient opted out of broadcast messaging'
            ], 403);
        }

        return $next($request);
    }
}
