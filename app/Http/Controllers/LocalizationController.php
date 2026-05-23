<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationController extends Controller
{
    public function switchLang($locale)
    {
        // Validasi bahasa yang diperbolehkan sistem
        if (in_array($locale, ['id', 'en'])) {
            Session::put('locale', $locale);
        }

        return redirect()->back();
    }
}
