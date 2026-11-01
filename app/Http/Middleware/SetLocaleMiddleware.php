<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Cek jika user pernah secara manual memilih bahasa (disimpan di Session)
        if ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        } else {
            // 2. Jika tidak ada di Session, ambil bahasa bawaan dari browser/device pengguna
            $browserLanguage = $request->getPreferredLanguage(['id', 'en']);

            // Gunakan bahasa browser jika didukung ('id' atau 'en'), jika tidak gunakan fallback bawaan config app
            $locale = in_array($browserLanguage, ['id', 'en']) ? $browserLanguage : config('app.fallback_locale', 'en');
        }

        // 3. Set locale aplikasi Laravel secara dinamis
        App::setLocale($locale);

        return $next($request);
    }
}
