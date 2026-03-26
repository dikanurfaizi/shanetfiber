<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\CustomerAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerLoginController extends Controller
{
    protected $authService;

    public function __construct(CustomerAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login()
    {
        // $data = $this->authService->getLoginData();
        return view('customers.auth.login');
    }

    public function postLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $result = $this->authService->attemptLogin(
            $request->input('email'),
            $request->input('password')
        );

        if ($result['success']) {
            return redirect()->route('customer.dashboard')
                ->withSuccess('You have successfully logged in');
        }

        return back()->with('error', $result['message']);
    }

    public function customerLogout(Request $request)
    {
        $this->authService->logout();
        Session::flush();
        return redirect(route('customerLogin'))
            ->withSuccess('You are logout successfully');
    }
}
