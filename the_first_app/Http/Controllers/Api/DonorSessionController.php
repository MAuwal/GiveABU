<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\DonorSession;
use App\Models\Donor;
use App\Models\DeviceSession;
use App\Services\GoogleAuthService;
use App\Mail\PasswordResetLinkMail;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DonorSessionController extends Controller
{
    /**
     * Register a new donor session (create username/password for a donor)
     */
    public function register(Request $request)
    {
        try {
            // Build validation rules
            $rules = [
                'username' => 'required|string|min:3|max:255|unique:donor_sessions,username',
                'password' => 'required|string|min:6',
                'device_session_id' => 'nullable|exists:device_sessions,id',
            ];

            // Make donor_id optional - only validate if provided
            if ($request->has('donor_id') && !empty($request->donor_id)) {
                $rules['donor_id'] = 'nullable|integer|exists:donors,id';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if username already exists (instead of checking by donor_id)
            $existingSession = DonorSession::where('username', $request->username)->first();
            if ($existingSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already exists. Please login instead.'
                ], 409);
            }

            // If donor_id is provided, check if donor already has a session
            if ($request->has('donor_id') && !empty($request->donor_id)) {
                $existingDonorSession = DonorSession::where('donor_id', $request->donor_id)->first();
                if ($existingDonorSession) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Donor already has a registered session. Please login instead.'
                    ], 409);
                }
            }

            // Create new donor session with email/password authentication
            $donorSession = DonorSession::create([
                'username' => $request->username,
                'password' => $request->password, // Will be hashed automatically via mutator
                'donor_id' => $request->donor_id ?? null, // ✅ Can be null
                'device_session_id' => $request->device_session_id ?? null,
                'auth_provider' => 'email', // Explicitly set to email for traditional registration
            ]);
            $donorSession->load('donor');
            $donorSummary = $this->summarizeDonations($donorSession->donor);

            // Determine message based on whether donor exists
            $message = $donorSession->donor 
                ? 'Registration successful' 
                : 'Registration successful! Please complete your profile.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'id' => $donorSession->id,
                    'username' => $donorSession->username,
                    'donor' => $donorSession->donor, // Will be null if donor_id is null
                    'device_session_id' => $donorSession->device_session_id,
                    'donor_summary' => $donorSummary,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function hasExceededResetRateLimit(int $donorSessionId): bool
    {
        return PasswordReset::where('donor_session_id', $donorSessionId)
                ->where('created_at', '>=', now()->subHour())
                ->count() >= 5;
    }



    private function validateResetToken(string $token): ?PasswordReset
    {
        return PasswordReset::with('donorSession')
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Login with username and password
     */
    /**
     * Handle device session creation/update for persistent login
     */
    private function handleDeviceSession(DonorSession $donorSession, Request $request)
    {
        $fingerprint = $request->header('X-Device-Fingerprint') ?? $request->input('device_fingerprint');
        
        if (!$fingerprint) {
            return null;
        }

        // We need a donor_id to link the device session. 
        // If the session has no donor_id (incomplete profile), we can't persist device session yet.
        if (!$donorSession->donor_id) {
            return null; 
        }

        $deviceSession = DeviceSession::firstOrNew([
            'donor_id' => $donorSession->donor_id,
            'device_fingerprint' => $fingerprint
        ]);

        $deviceSession->ip_address = $request->ip();
        $deviceSession->user_agent = $request->userAgent();
        // Persistent session: 10 years expiry
        $deviceSession->expires_at = now()->addYears(10); 
        
        if (!$deviceSession->exists || !$deviceSession->session_token) {
            $deviceSession->session_token = Str::random(64);
        }
        
        $deviceSession->save();
        
        // Update DonorSession to point to this device session (Last Active)
        if ($donorSession->device_session_id !== $deviceSession->id) {
            $donorSession->device_session_id = $deviceSession->id;
            $donorSession->save();
        }
        
        return $deviceSession;
    }

    /**
     * Login with username and password
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
                'device_session_id' => 'nullable|exists:device_sessions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find donor session by username
            $donorSession = DonorSession::where('username', $request->username)->first();

            if (!$donorSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Check if user is trying to login with Google account
            if ($donorSession->auth_provider === 'google') {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is registered with Google. Please use "Login with Google" instead.'
                ], 401);
            }

            // Verify password
            if (!Hash::check($request->password, $donorSession->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Ensure auth_provider is set to 'email' for email/password authentication
            $updates = [];
            if ($donorSession->auth_provider !== 'email') {
                $updates['auth_provider'] = 'email';
            }
            
            if (!empty($updates)) {
                $donorSession->update($updates);
            }

            // Handle persistent device session
            $deviceSession = $this->handleDeviceSession($donorSession, $request);

            // Load donor relationship with latest data
            $donorSession->load('donor');
            $donorSummary = $this->summarizeDonations($donorSession->donor);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                'data' => [
                    'session_id' => $donorSession->id,
                    'username' => $donorSession->username,
                    'donor' => $donorSession->donor,
                    'device_session_id' => $donorSession->device_session_id,
                    'session_token' => $deviceSession ? $deviceSession->session_token : null,
                    'donor_summary' => $donorSummary,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout (for future use - can be extended with session tokens if needed)
     */
    /**
     * Logout - Invalidate device session
     */
    public function logout(Request $request)
    {
        try {
            $fingerprint = $request->header('X-Device-Fingerprint') ?? $request->input('device_fingerprint');
            $sessionId = $request->input('session_id');

            if ($sessionId && $fingerprint) {
                $donorSession = DonorSession::find($sessionId);
                if ($donorSession && $donorSession->donor_id) {
                    // Invalidate specific device session by expiring it
                    DeviceSession::where('donor_id', $donorSession->donor_id)
                        ->where('device_fingerprint', $fingerprint)
                        ->update(['expires_at' => now()]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logout successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current session info (requires session_id in request)
     */
    public function me(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|exists:donor_sessions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $donorSession = DonorSession::with(['donor', 'deviceSession'])->find($request->session_id);

            if (!$donorSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            // Validate Device Session if fingerprint is provided
            $fingerprint = $request->header('X-Device-Fingerprint') ?? $request->input('device_fingerprint');
            if ($fingerprint && $donorSession->donor_id) {
                $deviceSession = DeviceSession::where('donor_id', $donorSession->donor_id)
                    ->where('device_fingerprint', $fingerprint)
                    ->first();

                if (!$deviceSession) {
                    // Device not recognized for this user
                    return response()->json([
                        'success' => false,
                        'message' => 'Device not recognized. Please login again.'
                    ], 401);
                }

                if ($deviceSession->expires_at && $deviceSession->expires_at->isPast()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired. Please login again.'
                    ], 401);
                }

                // Keep session alive
                $deviceSession->touch();
            }

            // If donor is still null but we have a matching email, try auto-linking
            if (!$donorSession->donor && filter_var($donorSession->username, FILTER_VALIDATE_EMAIL)) {
                $this->ensureSessionHasDonor($donorSession);
            }
            $donorSession->loadMissing('donor');
            $donorSummary = $this->summarizeDonations($donorSession->donor);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $donorSession->id,
                    'username' => $donorSession->username,
                    'donor' => $donorSession->donor,
                    'device_session_id' => $donorSession->device_session_id,
                    'device_session' => $donorSession->deviceSession,
                    'donor_summary' => $donorSummary,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update donor profile for authenticated user
     * 
     * POST /api/donor-sessions/profile
     * Body: { session_id, donor_type, name, surname, ... }
     */
    public function createOrUpdateProfile(Request $request)
    {
        try {
            // Validate session_id
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|exists:donor_sessions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated session
            $donorSession = DonorSession::find($request->session_id);
            
            if (!$donorSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }

            // Validate donor data
            $donorValidator = Validator::make($request->all(), [
                'donor_type' => 'required|string|in:supporter,addressable_alumni,non_addressable_alumni,Individual,Organization,NGO',
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'nationality' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'lga' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'gender' => 'nullable|in:male,female',
                'country' => 'nullable|string|max:100',
            ]);

            if ($donorValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $donorValidator->errors()
                ], 422);
            }

            // Check if email matches session username (for security)
            if ($request->email !== $donorSession->username) {
                Log::warning('Profile update email mismatch', [
                    'session_id' => $donorSession->id,
                    'session_username' => $donorSession->username,
                    'provided_email' => $request->email
                ]);
                
                // Allow but log - user might want to update email
                // You can make this stricter if needed
            }

            // Check if donor already exists for this session
            $donor = null;
            if ($donorSession->donor_id) {
                // Update existing donor
                $donor = Donor::find($donorSession->donor_id);
                
                if ($donor) {
                    // Check if email is being changed and if it conflicts with another donor
                    if ($request->email !== $donor->email) {
                        $emailExists = Donor::where('email', $request->email)
                                           ->where('id', '!=', $donor->id)
                                           ->exists();
                        
                        if ($emailExists) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Email already exists for another donor'
                            ], 422);
                        }
                    }
                    
                    // Update donor
                    $donor->update([
                        'donor_type' => $request->donor_type,
                        'name' => $request->name,
                        'surname' => $request->surname,
                        'other_name' => $request->other_name ?? null,
                        'email' => $request->email,
                        'phone' => $request->phone ?? null,
                        'nationality' => $request->nationality ?? 'Nigerian',
                        'state' => $request->state ?? null,
                        'lga' => $request->lga ?? null,
                        'address' => $request->address ?? null,
                        'gender' => $request->gender ?? null,
                        'country' => $request->country ?? null,
                    ]);
                    
                    Log::info('Donor profile updated via authenticated session', [
                        'session_id' => $donorSession->id,
                        'donor_id' => $donor->id,
                        'email' => $donor->email
                    ]);
                }
            }

            // If no donor exists, create new one
            if (!$donor) {
                // Check if email already exists (for another donor)
                $emailExists = Donor::where('email', $request->email)->exists();
                
                if ($emailExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email already exists. Please use a different email or contact support.'
                    ], 422);
                }

                // Create new donor
                $donor = Donor::create([
                    'donor_type' => $request->donor_type,
                    'name' => $request->name,
                    'surname' => $request->surname,
                    'other_name' => $request->other_name ?? null,
                    'email' => $request->email,
                    'phone' => $request->phone ?? null,
                    'nationality' => $request->nationality ?? 'Nigerian',
                    'state' => $request->state ?? null,
                    'lga' => $request->lga ?? null,
                    'address' => $request->address ?? null,
                    'gender' => $request->gender ?? null,
                    'country' => $request->country ?? null,
                ]);

                // Link donor to session
                $donorSession->update([
                    'donor_id' => $donor->id
                ]);

                Log::info('Donor profile created and linked to session', [
                    'session_id' => $donorSession->id,
                    'donor_id' => $donor->id,
                    'email' => $donor->email
                ]);
            }

            // Refresh session + relationships so subsequent /me calls work immediately
            $donorSession->refresh()->load(['donor']);
            $donor->load(['faculty', 'department']);
            $donorSummary = $this->summarizeDonations($donor);

            return response()->json([
                'success' => true,
                'message' => $donorSession->donor_id ? 'Profile updated successfully' : 'Profile created successfully',
                'data' => [
                    'donor' => [
                        'id' => $donor->id,
                        'name' => $donor->name,
                        'surname' => $donor->surname,
                        'other_name' => $donor->other_name,
                        'full_name' => trim(implode(' ', array_filter([$donor->surname, $donor->name, $donor->other_name]))),
                        'email' => $donor->email,
                        'phone' => $donor->phone,
                        'donor_type' => $donor->donor_type,
                        'nationality' => $donor->nationality,
                        'state' => $donor->state,
                        'lga' => $donor->lga,
                        'address' => $donor->address,
                        'gender' => $donor->gender,
                        'country' => $donor->country,
                    ],
                    'session' => [
                        'id' => $donorSession->id,
                        'username' => $donorSession->username,
                        'donor_id' => $donorSession->donor_id,
                    ],
                    'donor_summary' => $donorSummary,
                ]
            ], $donorSession->donor_id ? 200 : 201);

        } catch (\Exception $e) {
            Log::error('Error creating/updating donor profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create/update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Summarize donor donations for quick frontend display
     */
    private function summarizeDonations(?Donor $donor): array
    {
        if (!$donor) {
            return [
                'total_amount' => 0.0,
                'donations_count' => 0,
                'last_donation_at' => null,
            ];
        }

        $baseQuery = $donor->donations()->where('status', 'completed');
        $totalAmount = (float) $baseQuery->sum('amount');
        $donationsCount = (int) $donor->donations()->where('status', 'completed')->count();
        $lastDonation = $donor->donations()->where('status', 'completed')->latest('created_at')->first();

        return [
            'total_amount' => $totalAmount,
            'donations_count' => $donationsCount,
            'last_donation_at' => optional($lastDonation)->created_at,
        ];
    }

    /**
     * Ensure a donor session is linked to a donor whenever possible
     */
    private function ensureSessionHasDonor(DonorSession $donorSession): void
    {
        if ($donorSession->donor_id) {
            return;
        }

        if (!filter_var($donorSession->username, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $matchingDonor = Donor::where('email', $donorSession->username)->first();

        if ($matchingDonor) {
            $donorSession->update(['donor_id' => $matchingDonor->id]);
            $donorSession->setRelation('donor', $matchingDonor);

            Log::info('Auto-linked donor to session', [
                'session_id' => $donorSession->id,
                'donor_id' => $matchingDonor->id,
                'email' => $donorSession->username,
            ]);
        }
    }

    /**
     * Check if device is recognized and if donor has a session
     * 
     * GET /api/donor-sessions/check-device
     * Headers: X-Device-Fingerprint: {fingerprint}
     */
    public function checkDevice(Request $request)
    {
        try {
            $deviceFingerprint = $request->header('X-Device-Fingerprint');
            
            if (!$deviceFingerprint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device fingerprint not provided'
                ], 400);
            }

            // Find device session
            $deviceSession = DeviceSession::where('device_fingerprint', $deviceFingerprint)
                ->with('donor')
                ->first();

            if (!$deviceSession) {
                return response()->json([
                    'success' => false,
                    'recognized' => false,
                    'message' => 'Device not recognized'
                ], 200);
            }

            // Check if donor has a session
            $donorSession = DonorSession::where('donor_id', $deviceSession->donor_id)
                ->first();

            return response()->json([
                'success' => true,
                'recognized' => true,
                'device_session' => [
                    'id' => $deviceSession->id,
                    'donor_id' => $deviceSession->donor_id,
                ],
                'donor' => $deviceSession->donor,
                'has_donor_session' => $donorSession !== null,
                'donor_session' => $donorSession ? [
                    'id' => $donorSession->id,
                    'username' => $donorSession->username,
                ] : null,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update username for a donor session
     * 
     * PUT /api/donor-sessions/{session_id}/username
     */
    public function updateUsername(Request $request, $sessionId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|min:3|max:255|unique:donor_sessions,username,' . $sessionId,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $session = DonorSession::findOrFail($sessionId);
            $session->username = $request->username;
            $session->save();

            return response()->json([
                'success' => true,
                'message' => 'Username updated successfully',
                'data' => [
                    'id' => $session->id,
                    'username' => $session->username,
                    'donor_id' => $session->donor_id,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating username: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update password for a donor session
     * 
     * PUT /api/donor-sessions/{session_id}/password
     */
    public function updatePassword(Request $request, $sessionId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $session = DonorSession::findOrFail($sessionId);

            // Verify current password
            if (!Hash::check($request->current_password, $session->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 401);
            }

            // Update password (will be hashed automatically via mutator)
            $session->password = $request->new_password;
            $session->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Google OAuth login
     * Authenticates using google_id in donor_sessions table
     * 
     * POST /api/donor-sessions/google-login
     * Body: { token: "google_id_token", device_session_id: number|null }
     */
    public function googleLogin(Request $request, GoogleAuthService $googleAuth)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_session_id' => 'nullable|exists:device_sessions,id',
        ]);

        // Verify Google token
        $googleUser = $googleAuth->verifyToken($validated['token']);
        
        if (!$googleUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired Google token'
            ], 401);
        }

        // Check if email is verified
        if (!$googleUser['email_verified']) {
            return response()->json([
                'success' => false,
                'message' => 'Google email is not verified'
            ], 401);
        }

        // Validate required fields
        if (empty($googleUser['email']) || empty($googleUser['google_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google token: missing required information'
            ], 401);
        }

        try {
            DB::beginTransaction();
            
            Log::info('Google login attempt', [
                'email' => $googleUser['email'],
                'google_id' => $googleUser['google_id'],
                'name' => $googleUser['name']
            ]);

            // Find donor_sessions record by google_id (primary authentication method for Google users)
            $donorSession = DonorSession::where('google_id', $googleUser['google_id'])->first();

            if ($donorSession) {
                // Existing Google user - update Google info if needed
                $updates = [];
                if ($donorSession->google_email !== $googleUser['email']) {
                    $updates['google_email'] = $googleUser['email'];
                }
                if ($donorSession->google_name !== $googleUser['name']) {
                    $updates['google_name'] = $googleUser['name'];
                }
                if ($donorSession->google_picture !== $googleUser['picture']) {
                    $updates['google_picture'] = $googleUser['picture'];
                }
                
                if (!empty($updates)) {
                    $donorSession->update($updates);
                }

                // Handle persistent device session
                $deviceSession = $this->handleDeviceSession($donorSession, $request);

                // Also update donor record if needed
                $donor = $donorSession->donor;
                if ($donor) {
                    $donorUpdates = [];
                    if (empty($donor->name) && !empty($googleUser['given_name'])) {
                        $donorUpdates['name'] = $googleUser['given_name'];
                    }
                    if (empty($donor->surname) && !empty($googleUser['family_name'])) {
                        $donorUpdates['surname'] = $googleUser['family_name'];
                    }
                    if (empty($donor->gender) && !empty($googleUser['gender'])) {
                        $donorUpdates['gender'] = $googleUser['gender'];
                    }
                    if (empty($donor->profile_image) && !empty($googleUser['picture'])) {
                        $donorUpdates['profile_image'] = $googleUser['picture'];
                    }
                    
                    if (!empty($donorUpdates)) {
                        $donor->update($donorUpdates);
                    }
                }

                // Load donor relationship
                $donorSession->load('donor');

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Google login successful',
                    'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                    'data' => [
                        'session_id' => $donorSession->id,
                        'username' => $donorSession->username,
                        'donor' => $donorSession->donor,
                        'device_session_id' => $donorSession->device_session_id,
                        'session_token' => $deviceSession ? $deviceSession->session_token : null,
                        'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                    ]
                ], 200);
            }

            // Google account doesn't exist - check if email exists in donors table
            $donor = Donor::where('email', $googleUser['email'])->first();

            if (!$donor) {
                // Create new donor record
                $donor = Donor::create([
                    'email' => $googleUser['email'],
                    'name' => $googleUser['given_name'] ?? $this->extractFirstName($googleUser['name']),
                    'surname' => $googleUser['family_name'] ?? $this->extractLastName($googleUser['name']),
                    'gender' => $googleUser['gender'] ?? null,
                    'profile_image' => $googleUser['picture'] ?? null,
                    'phone' => null, // Google OAuth doesn't provide phone number
                    'donor_type' => 'Individual',
                ]);
            } else {
                // Update existing donor with Google info if fields are empty
                $updates = [];
                if (empty($donor->name) && !empty($googleUser['given_name'])) {
                    $updates['name'] = $googleUser['given_name'];
                }
                if (empty($donor->surname) && !empty($googleUser['family_name'])) {
                    $updates['surname'] = $googleUser['family_name'];
                }
                if (empty($donor->gender) && !empty($googleUser['gender'])) {
                    $updates['gender'] = $googleUser['gender'];
                }
                if (empty($donor->profile_image) && !empty($googleUser['picture'])) {
                    $updates['profile_image'] = $googleUser['picture'];
                }
                
                if (!empty($updates)) {
                    $donor->update($updates);
                }
            }

            // Check if a donor_session already exists with this username (email)
            $existingSession = DonorSession::where('username', $googleUser['email'])->first();
            
            if ($existingSession) {
                // Update existing session instead of creating new one
                $existingSession->update([
                    'donor_id' => $donor->id,
                    'device_session_id' => $validated['device_session_id'] ?? null,
                    'auth_provider' => 'google',
                    'google_id' => $googleUser['google_id'],
                    'google_email' => $googleUser['email'],
                    'google_name' => $googleUser['name'],
                    'google_picture' => $googleUser['picture'],
                ]);
                
                $donorSession = $existingSession;
                
                Log::info('Google login: Updated existing donor session', [
                    'session_id' => $donorSession->id,
                    'donor_id' => $donor->id,
                    'email' => $googleUser['email']
                ]);
            } else {
                // Create new donor_sessions record for Google auth
                $donorSession = DonorSession::create([
                    'username' => $googleUser['email'], // Use email as username
                    'password' => null, // No password for Google auth
                    'donor_id' => $donor->id,
                    'device_session_id' => $validated['device_session_id'] ?? null,
                    'auth_provider' => 'google',
                    'google_id' => $googleUser['google_id'],
                    'google_email' => $googleUser['email'],
                    'google_name' => $googleUser['name'],
                    'google_picture' => $googleUser['picture'],
                ]);
                
                Log::info('Google login: Created new donor session', [
                    'session_id' => $donorSession->id,
                    'donor_id' => $donor->id,
                    'email' => $googleUser['email']
                ]);
            }

            // Handle persistent device session
            $deviceSession = $this->handleDeviceSession($donorSession, $request);

            // Load donor relationship
            $donorSession->load('donor');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                'data' => [
                    'session_id' => $donorSession->id,
                    'username' => $donorSession->username,
                    'donor' => $donorSession->donor,
                    'device_session_id' => $donorSession->device_session_id,
                    'session_token' => $deviceSession ? $deviceSession->session_token : null,
                    'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google login error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Google login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Google OAuth registration
     * 
     * POST /api/donor-sessions/google-register
     * Body: { token: "google_id_token", device_session_id: number|null }
     */
    public function googleRegister(Request $request, GoogleAuthService $googleAuth)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'device_session_id' => 'nullable|exists:device_sessions,id',
        ]);

        // Verify Google token
        $googleUser = $googleAuth->verifyToken($validated['token']);
        
        if (!$googleUser) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired Google token'
            ], 401);
        }

        // Check if email is verified
        if (!$googleUser['email_verified']) {
            return response()->json([
                'success' => false,
                'message' => 'Google email is not verified'
            ], 401);
        }

        // Validate required fields
        if (empty($googleUser['email']) || empty($googleUser['google_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google token: missing required information'
            ], 401);
        }

        try {
            DB::beginTransaction();

            // Check if Google account already exists in donor_sessions
            $existingSession = DonorSession::where('google_id', $googleUser['google_id'])->first();
            
            if ($existingSession) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This Google account is already registered. Please login instead.'
                ], 409);
            }

            // Check if email already exists in donors table
            $donor = Donor::where('email', $googleUser['email'])->first();

            if (!$donor) {
                // Create new donor record with Google information
                $donor = Donor::create([
                    'email' => $googleUser['email'],
                    'name' => $googleUser['given_name'] ?? $this->extractFirstName($googleUser['name']),
                    'surname' => $googleUser['family_name'] ?? $this->extractLastName($googleUser['name']),
                    'gender' => $googleUser['gender'] ?? null, // Store gender from Google
                    'profile_image' => $googleUser['picture'] ?? null,
                    'phone' => null, // Google OAuth doesn't provide phone number
                    'donor_type' => 'Individual', // Default type
                ]);
            } else {
                // Update existing donor with Google info if fields are empty
                $updates = [];
                if (empty($donor->name) && !empty($googleUser['given_name'])) {
                    $updates['name'] = $googleUser['given_name'];
                }
                if (empty($donor->surname) && !empty($googleUser['family_name'])) {
                    $updates['surname'] = $googleUser['family_name'];
                }
                if (empty($donor->gender) && !empty($googleUser['gender'])) {
                    $updates['gender'] = $googleUser['gender'];
                }
                if (empty($donor->profile_image) && !empty($googleUser['picture'])) {
                    $updates['profile_image'] = $googleUser['picture'];
                }
                
                if (!empty($updates)) {
                    $donor->update($updates);
                }
            }

            // Check if donor already has a session with email/password auth
            $existingEmailSession = DonorSession::where('username', $googleUser['email'])
                ->where('auth_provider', 'email')
                ->first();
            
            if ($existingEmailSession) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'An account with this email already exists. Please login with your email and password, or link your Google account in settings.'
                ], 409);
            }

            // Create donor_sessions record for Google auth
            $donorSession = DonorSession::create([
                'username' => $googleUser['email'], // Use email as username
                'password' => null, // No password for Google auth
                'donor_id' => $donor->id,
                'device_session_id' => $validated['device_session_id'] ?? null,
                'auth_provider' => 'google',
                'google_id' => $googleUser['google_id'],
                'google_email' => $googleUser['email'],
                'google_name' => $googleUser['name'], // Full name from Google
                'google_picture' => $googleUser['picture'],
            ]);

            // Handle persistent device session
            $deviceSession = $this->handleDeviceSession($donorSession, $request);

            // Load donor relationship
            $donorSession->load('donor');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Google registration successful',
                'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                'data' => [
                    'session_id' => $donorSession->id,
                    'username' => $donorSession->username,
                    'donor' => $donorSession->donor,
                    'device_session_id' => $donorSession->device_session_id,
                    'session_token' => $deviceSession ? $deviceSession->session_token : null,
                    'token' => $deviceSession ? $deviceSession->session_token : $donorSession->id,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Google registration error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Google registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/donor-sessions/send-verification
     * Generates (or regenerates) an email verification token and sends the verification email.
     * Body: { session_id: int }
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer|exists:donor_sessions,id',
        ]);

        $donorSession = DonorSession::with('donor')->find($request->session_id);

        if (!$donorSession) {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }

        if ($donorSession->email_verified_at) {
            return response()->json(['success' => true, 'message' => 'Email already verified']);
        }

        $token = Str::random(64);
        $donorSession->update(['email_verification_token' => $token]);

        $verificationUrl = url('/verify-email/' . $token);
        $recipientName   = $donorSession->donor->name ?? $donorSession->username;

        try {
            Mail::to($donorSession->username)->send(new \App\Mail\EmailVerificationMail($verificationUrl, $recipientName));
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'error'      => $e->getMessage(),
                'session_id' => $donorSession->id,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Verification email sent']);
    }

    /**
     * GET /verify-email/{token}  (web route)
     * Marks the donor session as verified and clears the token.
     */
    public function verifyEmail(string $token)
    {
        $donorSession = DonorSession::where('email_verification_token', $token)->first();

        if (!$donorSession) {
            return redirect('/')->with('error', 'This verification link is invalid or has already been used.');
        }

        $donorSession->update([
            'email_verified_at'         => now(),
            'email_verification_token'  => null,
        ]);

        return redirect('/')->with('message', 'Your email has been verified! Thank you.');
    }

    /**
     * Helper method to extract first name from full name
     */
    private function extractFirstName($fullName)
    {
        if (empty($fullName)) {
            return null;
        }
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? null;
    }

    /**
     * Helper method to extract last name from full name
     */
    private function extractLastName($fullName)
    {
        if (empty($fullName)) {
            return null;
        }
        $parts = explode(' ', trim($fullName));
        if (count($parts) > 1) {
            return implode(' ', array_slice($parts, 1));
        }
        return null;
    }

    /**
     * POST /api/donor-sessions/forgot-password
     */
    private function buildResetUrl(string $token, ?string $callbackUrl = null): string
    {
        // Use provided callback URL or fallback to config
        $base = $callbackUrl ? rtrim($callbackUrl, '/') : rtrim(env('FRONTEND_URL', config('app.url')), '/');
        
        // If the callback URL already contains the path, just append query param
        if (strpos($base, '/reset-password') !== false) {
            return $base . '?token=' . $token;
        }
        
        return $base . '/reset-password?token=' . $token;
    }

    /**
     * POST /api/donor-sessions/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'callback_url' => 'nullable|url',
        ]);

        $email = $request->input('email');
        $callbackUrl = $request->input('callback_url');
        
        $donorSession = DonorSession::where('username', $email)
            ->where(function ($query) {
                $query->whereNull('auth_provider')
                    ->orWhere('auth_provider', 'email');
            })
            ->first();

        $genericResponse = response()->json([
            'success' => true,
            'message' => 'If the email exists, a reset link has been sent.',
        ], 200);

        if (!$donorSession) {
            return $genericResponse;
        }

        if ($this->hasExceededResetRateLimit($donorSession->id)) {
            Log::warning('Password reset rate limit hit', [
                'session_id' => $donorSession->id,
                'email' => $email,
            ]);
            return $genericResponse;
        }

        PasswordReset::where('donor_session_id', $donorSession->id)
            ->where('used', false)
            ->delete();

        $token = Str::random(64);
        $resetRecord = PasswordReset::create([
            'donor_session_id' => $donorSession->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        $resetUrl = $this->buildResetUrl($token, $callbackUrl);

        try {
            Mail::to($donorSession->username)->send(new PasswordResetLinkMail($resetUrl, $donorSession->username));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'session_id' => $donorSession->id,
            ]);
        }

        return $genericResponse;
    }

    /**
     * GET /api/donor-sessions/reset/{token}
     */
    public function getResetToken(string $token)
    {
        $record = $this->validateResetToken($token);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'username' => $record->donorSession->username,
            ],
        ]);
    }

    /**
     * POST /api/donor-sessions/reset/{token}
     */
    public function resetPasswordWithToken(Request $request, string $token)
    {
        $request->validate([
            'password' => 'required|string|confirmed|min:6',
        ]);

        $record = $this->validateResetToken($token);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 404);
        }

        $donorSession = $record->donorSession;
        $donorSession->update([
            'password' => $request->password,
        ]);

        $record->markAsUsed();
        PasswordReset::where('donor_session_id', $donorSession->id)
            ->where('used', false)
            ->update(['used' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful.',
        ]);
    }
}
