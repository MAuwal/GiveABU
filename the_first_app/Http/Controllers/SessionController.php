<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DeviceSession;

class SessionController extends Controller
{
    /**
     * Create device session after verification
     */
    public function create(Request $request)
    {
        try {
            Log::info('Session creation request received', [
                'data' => $request->all(),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Log each field individually to debug
            Log::info('Individual fields check', [
                'has_donorData' => $request->has('donorData'),
                'has_deviceInfo' => $request->has('deviceInfo'),
                'has_verificationData' => $request->has('verificationData'),
                'donorData_type' => gettype($request->input('donorData')),
                'deviceInfo_type' => gettype($request->input('deviceInfo')),
                'verificationData_type' => gettype($request->input('verificationData'))
            ]);

            $request->validate([
                'donorData' => 'required|array',
                'deviceInfo' => 'required|array',
                'verificationData' => 'nullable|array' // Make verification data optional
            ]);

            $donorData = $request->donorData;
            $deviceInfo = $request->deviceInfo;
            
            Log::info('Processing donor data', [
                'donorData' => $donorData,
                'deviceInfo' => $deviceInfo
            ]);
            
            // Create or update user
            $user = User::updateOrCreate(
                ['email' => $donorData['email']],
                [
                    'name' => $donorData['name'],
                    'phone' => $donorData['phone'],
                    'donor_type' => $donorData['donor_type'] ?? 'alumni',
                    // Add other fields as needed
                ]
            );
            
            Log::info('User created/updated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            // Create device session
            $sessionToken = Str::random(64);
            $deviceFingerprint = $deviceInfo['fingerprint'] ?? md5($deviceInfo['userAgent'] . $request->ip());
            
            $deviceSession = DeviceSession::create([
                'user_id' => $user->id,
                'session_token' => $sessionToken,
                'device_fingerprint' => $deviceFingerprint,
                'user_agent' => $deviceInfo['userAgent'],
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30)
            ]);
            
            Log::info('Device session created', [
                'session_id' => $deviceSession->id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Session created successfully',
                'user' => $user,
                'session_token' => $sessionToken
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Session creation validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Session creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Session creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if device is recognized
     */
    public function check(Request $request)
    {
        try {
            $request->validate([
                'deviceInfo' => 'required|array'
            ]);

            $deviceInfo = $request->deviceInfo;
            
            $session = DeviceSession::where('device_fingerprint', $deviceInfo['fingerprint'])
                ->where('expires_at', '>', now())
                ->with('user')
                ->first();
            
            if ($session) {
                return response()->json([
                    'valid' => true,
                    'user' => $session->user
                ]);
            }
            
            return response()->json([
                'valid' => false
            ]);

        } catch (\Exception $e) {
            Log::error('Session check failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Session check failed'
            ], 500);
        }
    }

    /**
     * Login with device session
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required_without:email',
                'email' => 'required_without:phone|email',
                'deviceInfo' => 'required|array'
            ]);

            $deviceInfo = $request->deviceInfo;
            
            // Find user by phone or email
            $user = null;
            if ($request->phone) {
                $user = User::where('phone', $request->phone)->first();
            } else {
                $user = User::where('email', $request->email)->first();
            }
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Create new device session
            $sessionToken = Str::random(64);
            DeviceSession::create([
                'user_id' => $user->id,
                'session_token' => $sessionToken,
                'device_fingerprint' => $deviceInfo['fingerprint'],
                'user_agent' => $deviceInfo['userAgent'],
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30)
            ]);
            
            return response()->json([
                'success' => true,
                'user' => $user,
                'session_token' => $sessionToken
            ]);

        } catch (\Exception $e) {
            Log::error('Session login failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed'
            ], 500);
        }
    }

    /**
     * Logout device session
     */
    public function logout(Request $request)
    {
        try {
            $sessionToken = $request->header('X-Device-Session');
            
            if ($sessionToken) {
                DeviceSession::where('session_token', $sessionToken)->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Session logout failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * Login with donor credentials (email/phone) - Device-based auth without user records
     */
    public function loginWithDonor(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required_without:email',
                'email' => 'required_without:phone|email',
                'deviceInfo' => 'required|array'
            ]);

            $deviceInfo = $request->deviceInfo;
            
            // Find donor by phone or email
            $donor = null;
            if ($request->phone) {
                $donor = \App\Models\Donor::where('phone', $request->phone)->first();
            } else {
                $donor = \App\Models\Donor::where('email', $request->email)->first();
            }
            
            if (!$donor) {
                Log::warning('Donor not found for login', [
                    'phone' => $request->phone,
                    'email' => $request->email
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Donor not found. Please check your email/phone or register first.'
                ], 404);
            }
            
            Log::info('Donor found for login', [
                'donor_id' => $donor->id,
                'email' => $donor->email,
                'phone' => $donor->phone
            ]);
            
            // Create device session directly with donor_id (no user record needed)
            $sessionToken = Str::random(64);
            $deviceFingerprint = $deviceInfo['fingerprint'] ?? md5($deviceInfo['userAgent'] . $request->ip());
            
            // First, delete any existing sessions for this device
            DeviceSession::where('device_fingerprint', $deviceFingerprint)->delete();
            
            $deviceSession = DeviceSession::create([
                'user_id' => null, // No user record needed for donor device auth
                'donor_id' => $donor->id, // Store donor_id directly
                'session_token' => $sessionToken,
                'device_fingerprint' => $deviceFingerprint,
                'user_agent' => $deviceInfo['userAgent'],
                'ip_address' => $request->ip(),
                'expires_at' => now()->addDays(30)
            ]);
            
            Log::info('Donor device session created', [
                'donor_id' => $donor->id,
                'session_id' => $deviceSession->id,
                'session_token' => $sessionToken
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'donor' => $donor,
                'session_token' => $sessionToken,
                'auth_type' => 'device_based'
            ]);

        } catch (\Exception $e) {
            Log::error('Donor login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }
} 