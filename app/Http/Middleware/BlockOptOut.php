<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\WhatsApp\OptOutService;

class BlockOptOut
{
    public function handle($request, Closure $next)
    {
        $to = $request->input('to');

        if (app(OptOutService::class)->shouldBlock($to)) {
            return response()->json([
                'error' => 'Recipient opted out of marketing messages'
            ], 403);
        }

        return $next($request);
    }
}
