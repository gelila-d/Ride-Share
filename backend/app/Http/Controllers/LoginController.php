<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\LoginNeedsVerification;

class LoginController extends Controller
{
    public function submit(Request $request){
        $request->validate([
            'phone' => 'required|numeric|min:10',
        ]);

        $user = User:: firstOrCreate([
            'phone' => $request->phone,
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
}
