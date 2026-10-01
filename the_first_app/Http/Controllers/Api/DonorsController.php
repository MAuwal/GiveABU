<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\DonorSession;
use App\Models\DeviceSession;
use App\Http\Resources\DonorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DonorsController extends Controller
{
    /**
     * Store a newly created donor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Only create donor if donor_type, name, and surname are provided
        if (!$request->has('donor_type') || empty($request->donor_type)) {
            return response()->json([
                'success' => false,
                'message' => 'Donor type, name, and surname are required to create a donor'
            ], 422);
        }

        // Validate input based on donor type
        $normalizedType = strtolower($request->donor_type);
        $isAlumni = str_contains($normalizedType, 'alumni');
        
        $validationRules = [
            'donor_type' => 'required|string',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'device_fingerprint' => 'nullable|string|max:500',
            'session_id' => 'nullable|exists:donor_sessions,id',
        ];
        
        // Add conditional validation based on donor type
        if ($isAlumni) {
            $validationRules['department_id'] = 'required|exists:departments,id';
            $validationRules['entry_year'] = 'nullable|integer|min:1950|max:' . date('Y');
            $validationRules['graduation_year'] = 'nullable|integer|min:1950|max:' . date('Y');
            $validationRules['reg_number'] = 'nullable|string|max:255';

        } elseif ($normalizedType === 'staff') {
            $validationRules['department_id'] = 'required|exists:departments,id';

        } elseif ($normalizedType === 'corporate') {
            $validationRules['organization_name'] = 'required|string|max:255';
        } else {
            $validationRules['faculty_id'] = 'nullable|exists:faculties,id';
            $validationRules['department_id'] = 'nullable|exists:departments,id';
        }
        
        // Common validation for all donor types - make optional for minimal registration
        $validationRules['nationality'] = 'nullable|string|max:255';
        $validationRules['state'] = 'nullable|string|max:255';
        $validationRules['lga'] = 'nullable|string|max:255';
        $validationRules['address'] = 'nullable|string';
        
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if a donor with this email already exists
            $existingDonor = Donor::where('email', $request->email)->first();
            if ($existingDonor) {
                // Check if they already have a session — return login info
                $existingSession = DonorSession::where('donor_id', $existingDonor->id)->first();
                if ($existingSession) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email already registered. Please login instead.',
                        'existing_donor' => true,
                        'donor' => new DonorResource($existingDonor),
                        'session_id' => $existingSession->id,
                    ], 409);
                }
            }

            // Prepare data for creation
            $donorData = $request->except(['password', 'device_fingerprint', 'session_id']);
            
            // Handle different donor types
            if ($normalizedType === 'supporter') {
                $donorData['reg_number'] = null;
                $donorData['faculty_id'] = null;
                $donorData['department_id'] = null;
                $donorData['entry_year'] = null;
                $donorData['graduation_year'] = null;
            } else {
                if (empty($donorData['reg_number'])) {
                    $donorData['reg_number'] = 'REG-' . strtoupper(substr($donorData['surname'], 0, 3)) . '-' . date('Y') . '-' . str_pad(Donor::count() + 1, 4, '0', STR_PAD_LEFT);
                }
                
                // AUTOMATED FACULTY LOOKUP (for Alumni or any type with department)
                if (!empty($donorData['department_id'])) {
                    $department = \App\Models\Department::find($donorData['department_id']);
                    if ($department) {
                        $donorData['faculty_id'] = $department->faculty_id;
                    }
                }
            }
            
            // Set defaults for optional fields
            if (empty($donorData['surname'])) {
                $donorData['surname'] = '';
            }
            if (empty($donorData['phone'])) {
                $donorData['phone'] = null;
            }
            if (empty($donorData['nationality'])) {
                $donorData['nationality'] = 'Nigerian';
            }
            if (empty($donorData['state'])) {
                $donorData['state'] = null;
            }
            if (empty($donorData['lga'])) {
                $donorData['lga'] = null;
            }

            // Create donor (or use existing one without a session)
            $donor = $existingDonor ?? Donor::create($donorData);

            // Create DeviceSession if fingerprint provided
            $deviceSession = null;
            $fingerprint = $request->input('device_fingerprint');
            if ($fingerprint) {
                $deviceSession = DeviceSession::firstOrCreate(
                    ['device_fingerprint' => $fingerprint],
                    [
                        'donor_id' => $donor->id,
                        'session_token' => Str::random(64),
                        'user_agent' => $request->userAgent() ?? 'unknown',
                        'ip_address' => $request->ip() ?? '0.0.0.0',
                        'expires_at' => now()->addYears(10),
                    ]
                );
                // Update donor_id if device existed but had no donor
                if (!$deviceSession->donor_id) {
                    $deviceSession->update(['donor_id' => $donor->id]);
                }
            }

            // Handle session: use existing or create new
            $session = null;
            if ($request->input('session_id')) {
                $session = DonorSession::find($request->input('session_id'));
                if ($session) {
                    $session->update([
                        'donor_id' => $donor->id,
                        'device_session_id' => $deviceSession?->id,
                    ]);
                }
            }
            
            // If no session exists, use updateOrCreate to avoid UNIQUE constraint crash
            if (!$session) {
                $session = DonorSession::updateOrCreate(
                    ['username' => $donor->email],
                    [
                        'donor_id' => $donor->id,
                        'password' => $request->input('password'),
                        'device_session_id' => $deviceSession?->id,
                        'auth_provider' => 'email',
                    ]
                );
            }
            
            Log::info('Donor registered successfully', [
                'donor_id' => $donor->id,
                'email' => $donor->email,
                'donor_type' => $donor->donor_type,
                'session_id' => $session->id,
                'device_session_id' => $deviceSession?->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful!',
                'donor' => new DonorResource($donor),
                'session_id' => $session->id,
                'username' => $session->username,
                'auth_token' => $session->id,
                'device_session_id' => $deviceSession?->id,
                'session_token' => $deviceSession?->session_token,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Donor registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified donor in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $donor = Donor::find($id);

        if (!$donor) {
            return response()->json([
                'success' => false,
                'message' => 'Donor not found'
            ], 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name'              => 'nullable|string|max:255',
            'surname'           => 'nullable|string|max:255',
            'other_name'        => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20|unique:donors,phone,' . $id,
            'gender'            => 'nullable|string|max:10',
            'donor_type'        => 'nullable|string|max:50',
            'department_id'     => 'nullable|exists:departments,id',
            'entry_year'        => 'nullable|integer|min:1950|max:' . date('Y'),
            'graduation_year'   => 'nullable|integer|min:1950|max:' . date('Y'),
            'organization_name' => 'nullable|string|max:255',
            'email'             => 'nullable|email|unique:donors,email,' . $id,
            'state'             => 'nullable|string|max:255',
            'lga'               => 'nullable|string|max:255',
            'nationality'       => 'nullable|string|max:255',
            'address'           => 'nullable|string',
            'profile_image'     => 'nullable|string',
            'program_ids'       => 'nullable|array',
            'program_ids.*'     => 'integer|exists:programs,id',
            'donor_tier_id'     => 'nullable|exists:donor_tiers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $donor->update($request->only([
                'name', 'surname', 'other_name', 'email', 'phone', 'gender',
                'donor_type', 'department_id', 'entry_year', 'graduation_year',
                'organization_name', 'state', 'lga', 'nationality', 'address',
                'profile_image', 'donor_tier_id',
            ]));

            // Sync program selections to pivot
            if ($request->has('program_ids')) {
                $rows = collect($request->program_ids)->map(fn($pid) => [
                    'donor_id'   => $donor->id,
                    'program_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \DB::table('department_programs')->where('donor_id', $donor->id)->delete();
                if ($rows->isNotEmpty()) {
                    \DB::table('department_programs')->insert($rows->all());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'donor' => new DonorResource($donor)
            ]);
        } catch (\Exception $e) {
            Log::error('Donor update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Update failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for a donor by email.
     *
     * @param  string  $email
     * @return \Illuminate\Http\Response
     */
    public function searchByEmail($email)
    {
        $donor = Donor::where('email', $email)->first();

        if ($donor) {
            return response()->json([
                'exists' => true,
                'donor' => new DonorResource($donor)
            ]);
        }

        return response()->json([
            'exists' => false,
            'message' => 'Donor not found'
        ], 404);
    }

    /**
     * Search for addressable alumni by registration number.
     *
     * @param  string  $regNumber
     * @return \Illuminate\Http\Response
     */
    public function searchAddressableAlumni($regNumber)
    {
        $donor = Donor::with(['faculty', 'department'])
            ->where('reg_number', $regNumber)
            ->where('donor_type', 'addressable_alumni')
            ->first();

        if ($donor) {
            return response()->json([
                'success' => true,
                'donor' => new DonorResource($donor)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Addressable alumni not found with this registration number'
        ], 404);
    }

    /**
     * Check if a device has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkByDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_fingerprint' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the device fingerprint is associated with any donor via DeviceSession
        $deviceSession = DeviceSession::where('device_fingerprint', $request->device_fingerprint)
            ->with('donor')
            ->first();

        if ($deviceSession && $deviceSession->donor) {
            $donorSession = DonorSession::where('donor_id', $deviceSession->donor_id)->first();
            return response()->json([
                'exists' => true,
                'donor' => new DonorResource($deviceSession->donor),
                'device_session_id' => $deviceSession->id,
                'session_token' => $deviceSession->session_token,
                'donor_session' => $donorSession ? [
                    'id' => $donorSession->id,
                    'username' => $donorSession->username,
                ] : null,
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }

    /**
     * Upload profile image for a donor
     * 
     * POST /api/donors/{id}/profile-image
     */
    public function uploadProfileImage(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $donor = Donor::findOrFail($id);

            // Delete old image if exists
            if ($donor->profile_image) {
                Storage::disk('public')->delete($donor->profile_image);
            }

            // Store new image
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $donor->profile_image = $path;
            $donor->save();

            // Load relationships for response
            $donor->load(['faculty', 'department']);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'data' => [
                    'donor' => [
                        'id' => $donor->id,
                        'name' => $donor->name,
                        'surname' => $donor->surname,
                        'other_name' => $donor->other_name,
                        'email' => $donor->email,
                        'phone' => $donor->phone,
                        'profile_image' => $donor->profile_image ? Storage::url($donor->profile_image) : null,
                        'profile_image_path' => $donor->profile_image,
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Donor not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error uploading profile image', [
                'donor_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error uploading profile image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link a donor to a session immediately after creation/update.
     */
    private function linkDonorToSession(Donor $donor, $sessionId = null, ?string $email = null): void
    {
        try {
            $sessionQuery = DonorSession::query();

            if ($sessionId) {
                $sessionQuery->where('id', $sessionId);
            } elseif ($email) {
                $sessionQuery->where('username', $email);
            } else {
                return;
            }

            $session = $sessionQuery->first();

            if (!$session) {
                return;
            }

            if ($session->donor_id === $donor->id) {
                return;
            }

            $session->update(['donor_id' => $donor->id]);

            Log::info('Linked donor to session', [
                'donor_id' => $donor->id,
                'session_id' => $session->id,
                'username' => $session->username,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to link donor to session', [
                'donor_id' => $donor->id,
                'session_id' => $sessionId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}