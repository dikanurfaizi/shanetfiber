<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordOTP;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Helpers\LogHelper;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate(['login' => 'required|string']);

        $customer = Customer::where('email', $request->login)
            ->orWhere('phone', $request->login)
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'login' => 'Email/nomor telepon tidak ditemukan'
            ]);
        }

        $otp = rand(100000, 999999);
        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $customer->email],
            [
                'token' => $token,
                'otp' => $otp,
                'created_at' => now()
            ]
        );

        Notification::send($customer, new ResetPasswordOTP($otp));

        // kalau lewat modal, jangan redirect ke route yg nabrak
        return response()->json([
            'status' => true,
            'message' => 'Kode OTP telah dikirim!',
            'redirect' => route('customer.password.reset', $token),
        ]);
    }

    public function sendResetLinkLoggedIn(Request $request)
    {
        $customer = auth('customer')->user();

        if (!$customer) {
            abort(403, 'Unauthorized');
        }

        $otp = rand(100000, 999999);
        $token = Str::random(60);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $customer->email],
            [
                'token' => $token,
                'otp' => $otp,
                'created_at' => now()
            ]
        );

        Notification::send($customer, new ResetPasswordOTP($otp));

        return redirect()->route('customer.password.reset', $token)
            ->with('status', 'Kode OTP telah dikirim!');
    }
}
