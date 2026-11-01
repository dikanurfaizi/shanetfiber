<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika pengguna pernah memilih bahasa secara manual (tersimpan di Session)
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } else {
            // 2. Jika belum ada di Session, deteksi bahasa dari browser/device pengguna (hanya izinkan 'id' atau 'en')
            $browserLanguage = $request->getPreferredLanguage(['id', 'en']);

            // Gunakan bahasa browser jika valid, jika tidak gunakan fallback config bawaan (en)
            $locale = in_array($browserLanguage, ['id', 'en'])
                ? $browserLanguage
                : config('app.fallback_locale', 'en');
        }

        // Set locale aplikasi Laravel
        App::setLocale($locale);

        return $next($request);
    }
}
