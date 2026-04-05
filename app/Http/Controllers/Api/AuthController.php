<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Send OTP
    public function sendOtp(Request $request)
    {
        $request->validate(['mobile' => 'required|string|exists:users,mobile']);
        
        $otp = rand(100000, 999999);
        $user = User::where('mobile', $request->mobile)->first();
        $user->update([
            'otp' => $otp,
            'otp_expire_at' => now()->addMinutes(5)
        ]);
        
        // In real project, send SMS here
        return response()->json(['message' => 'OTP sent successfully', 'otp' => $otp]);
    }
    
    // Verify OTP & Login
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|exists:users,mobile',
            'otp' => 'required|string|size:6'
        ]);
        
        $user = User::where('mobile', $request->mobile)
            ->where('otp', $request->otp)
            ->where('otp_expire_at', '>', now())
            ->first();
        
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }
        
        $user->update(['otp' => null, 'otp_expire_at' => null]);
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    // Get Profile
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
    
    // Update Profile
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $user->update($request->only(['name', 'email', 'address']));
        return response()->json(['message' => 'Profile updated', 'user' => $user]);
    }

}
