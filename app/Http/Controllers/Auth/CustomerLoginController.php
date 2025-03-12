<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Helpers\LogHelper;

class CustomerLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::CUSTOMERHOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    //public function __construct()
    // {
    //    $this->middleware('guest')->except('logout');
    //}

    public function login()
    {

        return view('customers.auth.login');
    }

    public function postLogin(Request $request)
    {
        // Validasi input email atau nomor telepon
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        // Ambil input
        $input = $request->input('email');
        $password = $request->input('password');

        // Log input yang digunakan untuk login
        LogHelper::logInfo(
            null,
            [
                'user_login' => $input
            ],
            200,
            "Attempting login"
        );

        // Deteksi apakah input dimulai dengan "0"
        if (substr($input, 0, 1) === '0') {
            // Ubah "0" di awal menjadi "62"
            $localInput = '62' . substr($input, 1);
        } elseif (substr($input, 0, 2) === '62') {
            // Jika input dimulai dengan "62", coba ubah menjadi format lokal (dimulai dengan "0")
            $localInput = '0' . substr($input, 2);
        }

        // Cari pengguna berdasarkan email atau nomor telepon
        $user = \App\Models\Customer::where('email', $input)
            ->orWhere('contact', $input)
            ->orWhere('contact', $localInput ?? null) // Tambahkan pencarian dengan format lokal jika ada
            ->first();

        // Jika pengguna ditemukan, lakukan autentikasi
        if ($user) {
            LogHelper::logInfo(
                null,
                [
                    'user' => $user
                ],
                200,
                "User found. Checking password..."
            );

            if (Hash::check($password, $user->password)) {
                auth()->guard('customer')->login($user);

                // Ambil nama pengguna untuk log
                $userName = $user->name;

                // Periksa apakah nama mengandung '@'
                if (strpos($userName, '@') !== false) {
                    // Ambil bagian setelah '@'
                    $userName = explode('@', $userName)[1];
                }

                LogHelper::logInfo(
                    null,
                    [
                        'user' => $userName
                    ],
                    200,
                    "Successfully Login"
                );
                return redirect()->route('customer.dashboard')
                    ->withSuccess('You have Successfully logged in');
            } else {
                LogHelper::logWarning(
                    null,
                    [
                        'user' => $input
                    ],
                    400,
                    "Password mismatch"
                );
                return back()->with('error', 'Whoops! invalid credentials.');
            }
        } else {
            LogHelper::logWarning(
                null,
                [
                    'user' => $input
                ],
                404,
                "Login attempt failed. No user found."
            );
            return back()->with('error', 'Whoops! invalid credentials.');
        }
    }

    public function customerLogout(Request $request)
    {
        auth()->guard('customer')->logout();
        Session::flush();
        // Session::put('success', 'You are logout sucessfully');
        return redirect(route('customerLogin'))->withSuccess('You are logout sucessfully');
    }
}
