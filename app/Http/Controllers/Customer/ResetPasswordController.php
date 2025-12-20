<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;

class ResetPasswordController extends Controller
{
    public function showResetForm($token)
    {
        return view('customers.auth.passwords.reset', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'otp' => 'required|digits:6',
            'password' => 'required|confirmed|min:8',
        ]);

        $reset = DB::table('password_resets')
            ->where('token', $request->token)
            ->where('otp', $request->otp)
            ->first();

        if (!$reset) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid']);
        }

        $customer = Customer::where('email', $reset->email)->first();
        $customer->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $reset->email)->delete();

        return redirect()->route('customer.login')
            ->with('status', 'Password berhasil direset! Silakan login');
    }
}
