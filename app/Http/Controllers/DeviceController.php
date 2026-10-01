<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceSession;
use App\Models\Donor;

class DeviceController extends Controller
{
    /**
     * Register a device session
     */
    public function register(Request $request)
    {
        try {
            Log::info('Device registration attempt', [
                'request_data' => $request->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $request->validate([
                'device_fingerprint' => 'required|string',
                'donor_id' => 'required|exists:donors,id',
                'expires_in' => 'integer|min:1|max:525600' // Max 1 year in minutes
            ]);

            $expiresIn = $request->get('expires_in', 10080); // Default 1 week
            $expiresAt = now()->addMinutes($expiresIn);

            // Delete existing sessions for this device
            DeviceSession::where('device_fingerprint', $request->device_fingerprint)->delete();

            // Generate unique session token
            $sessionToken = bin2hex(random_bytes(32));

            // Create new session
            $deviceSession = DeviceSession::create([
                'donor_id' => $request->donor_id,
                'session_token' => $sessionToken,
                'device_fingerprint' => $request->device_fingerprint,
                'expires_at' => $expiresAt,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            Log::info('Device session created successfully', [
                'session_id' => $deviceSession->id,
                'donor_id' => $request->donor_id,
                'device_fingerprint' => $request->device_fingerprint
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device session registered successfully',
                'session_id' => $deviceSession->id,
                'session_token' => $sessionToken,
                'expires_at' => $expiresAt->toISOString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Device registration validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Device registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check session validity
     */
    public function checkSession(Request $request)
    {
        try {
            $request->validate([
                'session_id' => 'required|exists:device_sessions,id',
                'device_fingerprint' => 'required|string'
            ]);

            $session = DeviceSession::where('id', $request->session_id)
                                   ->where('device_fingerprint', $request->device_fingerprint)
                                   ->with('donor')
                                   ->first();

            if (!$session) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Session not found'
                ]);
            }

            if ($session->expires_at <= now()) {
                $session->delete();
                return response()->json([
                    'valid' => false,
                    'message' => 'Session expired'
                ]);
            }

            return response()->json([
                'valid' => true,
                'message' => 'Session is valid',
                'donor' => [
                    'id' => $session->donor->id,
                    'name' => $session->donor->name,
                    'surname' => $session->donor->surname,
                    'email' => $session->donor->email,
                ],
                'expires_at' => $session->expires_at->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Session check failed: ' . $e->getMessage());
            return response()->json([
                'valid' => false,
                'message' => 'Check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check device recognition by fingerprint
     */
    public function checkDevice($fingerprint)
    {
        try {
            $deviceSession = DeviceSession::where('device_fingerprint', $fingerprint)
                                         ->where('expires_at', '>', now())
                                         ->with('donor')
                                         ->first();

            if (!$deviceSession || !$deviceSession->donor) {
                return response()->json([
                    'recognized' => false,
                    'message' => 'Device not recognized'
                ]);
            }

            $donor = $deviceSession->donor;
            $totalDonations = $donor->donations()->where('status', 'success')->sum('amount');

            return response()->json([
                'recognized' => true,
                'message' => 'Device recognized',
                'session_id' => $deviceSession->id,
                'donor' => [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'surname' => $donor->surname,
                    'email' => $donor->email,
                    'phone' => $donor->phone,
                    'total_donations' => $totalDonations
                ],
                'expires_at' => $deviceSession->expires_at->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Device check failed: ' . $e->getMessage());
            return response()->json([
                'recognized' => false,
                'message' => 'Check failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if device is recognized (legacy method)
     */
    public function check(Request $request)
    {
        try {
            $deviceFingerprint = $request->header('X-Device-Fingerprint');
            
            if (!$deviceFingerprint) {
                return response()->json([
                    'recognized' => false,
                    'message' => 'Device fingerprint not provided'
                ]);
            }

            // Find device session
            $deviceSession = DeviceSession::where('device_fingerprint', $deviceFingerprint)
                                         ->where('expires_at', '>', now())
                                         ->with('donor')
                                         ->first();

            if (!$deviceSession || !$deviceSession->donor) {
                return response()->json([
                    'recognized' => false,
                    'message' => 'Device not recognized'
                ]);
            }

            $donor = $deviceSession->donor;
            $totalDonations = $donor->donations()->where('status', 'success')->sum('amount');

            return response()->json([
                'recognized' => true,
                'message' => 'Device recognized',
                'donor' => [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'surname' => $donor->surname,
                    'other_names' => $donor->other_name,
                    'full_name' => trim(implode(' ', array_filter([$donor->surname, $donor->name, $donor->other_name]))),
                    'email' => $donor->email,
                    'phone' => $donor->phone,
                    'donor_type' => $donor->donor_type,
                    'address' => $donor->address,
                    'state' => $donor->state,
                    'city' => $donor->lga,
                    'country' => $donor->country,
                    'session_id' => $deviceSession->id,
                    'total_donations' => $totalDonations
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking device recognition', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'recognized' => false,
                'message' => 'Error checking device recognition'
            ], 500);
        }
    }

    /**
     * Create/Update device session
     */
    public function createSession(Request $request)
    {
        try {
            $request->validate([
                'device_fingerprint' => 'required|string',
                'donor_id' => 'required|exists:donors,id'
            ]);

            $deviceFingerprint = $request->device_fingerprint;
            $donorId = $request->donor_id;

            // Check if device session already exists
            $deviceSession = DeviceSession::where('device_fingerprint', $deviceFingerprint)->first();

            if ($deviceSession) {
                // Update existing session
                $deviceSession->update([
                    'donor_id' => $donorId,
                    'expires_at' => now()->addDays(30),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip()
                ]);
            } else {
                // Create new session
                $deviceSession = DeviceSession::create([
                    'donor_id' => $donorId,
                    'device_fingerprint' => $deviceFingerprint,
                    'session_token' => \Illuminate\Support\Str::random(60),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                    'expires_at' => now()->addDays(30)
                ]);
            }

            Log::info('Device session created/updated', [
                'session_id' => $deviceSession->id,
                'donor_id' => $donorId,
                'device_fingerprint' => $deviceFingerprint
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device session created successfully',
                'session_id' => $deviceSession->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating device session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating device session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donor info by device fingerprint
     */
    public function getDonorInfo(Request $request)
    {
        try {
            $deviceFingerprint = $request->header('X-Device-Fingerprint');
            
            if (!$deviceFingerprint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device fingerprint not provided'
                ], 400);
            }

            $deviceSession = DeviceSession::where('device_fingerprint', $deviceFingerprint)
                                         ->where('expires_at', '>', now())
                                         ->with('donor')
                                         ->first();

            if (!$deviceSession || !$deviceSession->donor) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active session found'
                ], 404);
            }

            $donor = $deviceSession->donor;

            return response()->json([
                'success' => true,
                'message' => 'Donor info retrieved successfully',
                'donor' => [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'surname' => $donor->surname,
                    'other_names' => $donor->other_name,
                    'full_name' => trim(implode(' ', array_filter([$donor->surname, $donor->name, $donor->other_name]))),
                    'email' => $donor->email,
                    'phone' => $donor->phone,
                    'donor_type' => $donor->donor_type,
                    'address' => $donor->address,
                    'state' => $donor->state,
                    'city' => $donor->lga,
                    'country' => $donor->country
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting donor info', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting donor info: ' . $e->getMessage()
            ], 500);
        }
    }
}
