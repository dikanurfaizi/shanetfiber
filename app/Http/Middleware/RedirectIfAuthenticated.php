<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $dbName = config('database.connections.mysql.database');
        try {
            DB::connection()->getPdo();
            $status = 'false';
        } catch (Exception $e) {
            return redirect()->route('install');
        }

        $guards = empty($guards) ? [null] : $guards;
        foreach ($guards as $guard) {
            //return dd($guard);
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            } else if (Auth::guard('customer')->check()) {
                return redirect(RouteServiceProvider::CUSTOMERHOME);
            }
        }
        return $next($request);
    }
}
