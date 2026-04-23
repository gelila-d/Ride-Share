<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;

class LoginController extends Controller
{
    public function submit(Request $request){
        $request->validate([
            'phone' => 'required|numeric|digits:10',
        ]);

        $phone = $request->phone;

// Convert 0912345678 → +251912345678
$phone = '+251' . ltrim($phone, '0');

$user = User::firstOrCreate([
    'phone' => $phone,
]);

        if(!$user){
            return response()->json([
                'message' => 'Could not process a user with that phone number',
            ], 401);
        }

        $user->notify(new LoginNeedsVerification());

        return response()->json([
            'message' => 'Verification code sent to your phone',
        ], 200);
    }

public function verify(Request $request){
    $request->validate([
        'phone' => 'required|digits:10',
        'login_code' => 'required|digits:6',
    ]);

  $phone = '+251' . ltrim($request->phone, '0');

$user = User::where('phone', $phone)
    ->where('login_code', $request->login_code)
    ->first();

  if($user){
    $user->update(['login_code' => null]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'token' => $token
    ], 200);
}

    return response()->json([
        'message' => 'Invalid phone number or verification code',
    ], 401);
}
}