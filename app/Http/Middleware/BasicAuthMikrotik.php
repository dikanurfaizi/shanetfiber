<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BasicAuthMikrotik
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUsername = 'shanet';
        $expectedPassword = 'berkah25';

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        $encodedCredentials = substr($authHeader, 6);
        $decoded = base64_decode($encodedCredentials, true);

        if (!$decoded || !str_contains($decoded, ':')) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        [$username, $password] = explode(':', $decoded, 2);

        if ($username !== $expectedUsername || $password !== $expectedPassword) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
