<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyXAuthKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $authKey = $request->header('X-Auth-Key');
        $expected = base64_encode('shanet:berkah25');

        if ($authKey !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
