<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Http\RedirectResponse;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Log;

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
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Log::info('Login attempt:', [
        //     'email' => $request->input('email'),
        //     'password' => $request->input('password') // Hati-hati dengan logging password
        // ]);

        if (auth()->guard('customer')->attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ])) {
            $user = auth()->guard('customer')->user();
            return redirect()->route('customer.dashboard')
                ->withSuccess('You have Successfully loggedin');
            //return dd($user);
            //if ($user->is_customer == 1) {
            //return redirect()->route('customerDashboard')->with('success', 'You are Logged in sucessfully.');
            // }
        } else {
            return back()->with('error', 'Whoops! invalid email and password.');
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
