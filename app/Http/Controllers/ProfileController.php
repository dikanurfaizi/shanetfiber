<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;
use App\Helpers\LogHelper;
use Exception;
use App\Models\User;
use App\Models\Customer;

class ProfileController extends Controller
{
    public function show()
    {
        return view('auth.profile');
    }

    public function update(ProfileUpdateRequest $request)
    {
        $traceId = uniqid(); // Generate a unique trace ID for this request

        try {
            // Find authenticated user in either Users or Customers table
            $user = auth()->user();
            $userModel = User::find($user->id);

            if (!$userModel) {
                $userModel = Customer::find($user->id);

                if (!$userModel) {
                    LogHelper::logWarning(
                        null,
                        ['user_id' => $user->id],
                        404,
                        "User not found in either Users or Customers table",
                        $traceId
                    );
                    return redirect()->back()
                        ->with('error', 'User account not found.')
                        ->withInput();
                }
            }

            // Update password if provided
            if ($request->password) {
                $userModel->update(['password' => Hash::make($request->password)]);

                LogHelper::logInfo(
                    null,
                    [
                        'user_id' => $userModel->id,
                        'user_type' => get_class($userModel),
                        'action' => 'password_update'
                    ],
                    200,
                    "User password updated successfully",
                    $traceId
                );
            }

            // Update profile information
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            $userModel->update($updateData);

            LogHelper::logInfo(
                null,
                [
                    'user_id' => $userModel->id,
                    'user_type' => get_class($userModel),
                    'updated_fields' => $updateData
                ],
                200,
                "Profile updated successfully",
                $traceId
            );

            return redirect()->back()->with('success', 'Profile updated.');
        } catch (Exception $e) {
            LogHelper::logError(
                null,
                [
                    'user_id' => $user->id ?? null,
                    'request_data' => $request->except('password'),
                    'error' => $e->getMessage(),
                    'stack_trace' => $e->getTraceAsString()
                ],
                500,
                "Failed to update profile",
                $traceId
            );

            return redirect()->back()
                ->with('error', 'Profile update failed. Please try again.')
                ->withInput();
        }
    }
}
