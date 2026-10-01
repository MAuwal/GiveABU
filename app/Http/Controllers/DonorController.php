<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Donor; // Make sure you have a Donation model
use App\Http\Resources\DonorResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Traits\ResolvesDonorSession;

class DonorController extends Controller
{
    use ResolvesDonorSession;
    /**
     * Display a listing of donors.
     */
    public function index(Request $request)
    {
        // Ensure user is authenticated
        $donor = $this->resolveDonorOrError($request);
        if ($donor instanceof \Illuminate\Http\JsonResponse) return $donor;

        $query = Donor::query();

        // Optional filtering
        if ($request->has('type')) {
            $query->where('donor_type', $request->type);
        }

        // Search by name/email/phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Limit fields for privacy as requested
        $donors = $query->paginate(50);

        $data = collect($donors->items())->map(function($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'surname' => $d->surname,
                'email' => $d->email,
                'profile_image' => $d->profile_image_url, // Use accessor for full URL
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $donors->currentPage(),
                'last_page' => $donors->lastPage(),
                'total' => $donors->total(),
            ]
        ]);
    }

    public function makeDonation(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:100',
                'email' => 'required|email',
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'phone' => 'required|string|max:20',
                'project_id' => 'nullable|exists:projects,id',
                'endowment' => 'required|in:yes,no',
                'type' => 'required|in:endowment,project',
                'device_fingerprint' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for authenticated session first
            $sessionToken = $request->header('X-Device-Session');
            $fingerprint = $request->header('X-Device-Fingerprint');
            $donor = null;

            if ($sessionToken) {
                $deviceSession = \App\Models\DeviceSession::where('session_token', $sessionToken)->first();
                if ($deviceSession && $deviceSession->donor_id) {
                    $donor = Donor::find($deviceSession->donor_id);
                }
            }

            if (!$donor && $fingerprint) {
                $deviceSession = \App\Models\DeviceSession::where('device_fingerprint', $fingerprint)->first();
                if ($deviceSession && $deviceSession->donor_id) {
                    $donor = Donor::find($deviceSession->donor_id);
                }
            }

            if ($donor) {
                // Update existing donor from session - SAVE SEPARATE NAME FIELDS
                // We trust the session donor is the correct one. 
                // Optionally update contact info if provided and different?
                // For now, let's update phone/names if they are provided in the form
                $donor->name = trim($request->name);
                $donor->surname = trim($request->surname);
                $donor->other_name = $request->other_name ? trim($request->other_name) : null;
                $donor->phone = $request->phone;
                // Only update email if it's not set or if we want to allow changing email via donation form (risky)
                // $donor->email = $request->email; 
                $donor->save();
            } else {
                // Fallback: Find or create donor by email
                $donor = Donor::where('email', $request->email)->first();

                if ($donor) {
                    // Update existing donor - SAVE SEPARATE NAME FIELDS
                    $donor->name = trim($request->name);
                    $donor->surname = trim($request->surname);
                    $donor->other_name = $request->other_name ? trim($request->other_name) : null;
                    $donor->phone = $request->phone;
                    $donor->save();
                } else {
                    // Create new donor - SAVE SEPARATE NAME FIELDS
                    $donor = Donor::create([
                        'name' => trim($request->name),
                        'surname' => trim($request->surname),
                        'other_name' => $request->other_name ? trim($request->other_name) : null,
                        'email' => $request->email,
                        'phone' => $request->phone,
                        'donor_type' => 'addressable_alumni', // Default type
                    ]);
                }
            }

            // Create donation record - INCLUDE TYPE FIELD
            $donation = \App\Models\Donation::create([
                'donor_id' => $donor->id,
                'project_id' => $request->project_id ?? null,
                'amount' => $request->amount,
                'endowment' => $request->endowment,
                'type' => $request->type, // CRITICAL: Required by database
                'frequency' => 'onetime', // Default to onetime
                'status' => 'completed', // Dummy payment = success
                'payment_reference' => 'FLUTTERWAVE_' . time() . '_' . $donor->id,
            ]);

            // Load relationships for response
            $donation->load(['donor', 'project']);

            Log::info('Donation recorded successfully', [
                'donation_id' => $donation->id,
                'donor_id' => $donor->id,
                'amount' => $request->amount,
                'type' => $request->type,
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Donation recorded successfully',
                'data' => [
                    'id' => $donation->id,
                    'donor_id' => $donation->donor_id,
                    'project_id' => $donation->project_id,
                    'amount' => $donation->amount,
                    'endowment' => $donation->endowment,
                    'type' => $donation->type,
                    'status' => $donation->status,
                    'payment_reference' => $donation->payment_reference,
                    'created_at' => $donation->created_at,
                    'donor' => [
                        'id' => $donor->id,
                        'name' => $donor->name,
                        'surname' => $donor->surname,
                        'other_name' => $donor->other_name,
                        'email' => $donor->email,
                        'phone' => $donor->phone,
                    ],
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error recording donation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while recording donation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchByRegNumber($reg_number)
    {
        $donor = Donor::with(['faculty', 'department'])
            ->where('reg_number', $reg_number)
            ->first();

        if (!$donor) {
            return response()->json(['message' => 'Donor not found'], 404);
        }

        return new DonorResource($donor);
    }

    public function searchByPhone($phone)
    {
        $donor = Donor::with(['faculty', 'department'])
            ->where('phone', $phone)
            ->first();

        if (!$donor) {
            return response()->json(['message' => 'Donor not found'], 404);
        }

        return new DonorResource($donor);
    }

    public function searchByEmail($email)
    {
        $donor = Donor::with(['faculty', 'department'])
            ->where('email', $email)
            ->first();

        if (!$donor) {
            return response()->json(['message' => 'Donor not found'], 404);
        }

        return new DonorResource($donor);
    }

    // Additional method for registration number search (alias)
    public function searchByRegistrationNumber($regNumber)
    {
        return $this->searchByRegNumber($regNumber);
    }

    /**
     * Parse full name into components (surname, name, other_names)
     */
    private function parseName($fullName)
    {
        $nameParts = array_filter(explode(' ', trim($fullName)));
        
        if (empty($nameParts)) {
            return [
                'surname' => '',
                'name' => '',
                'other_names' => ''
            ];
        }
        
        if (count($nameParts) === 1) {
            return [
                'surname' => '',
                'name' => $nameParts[0],
                'other_names' => ''
            ];
        }
        
        if (count($nameParts) === 2) {
            return [
                'surname' => $nameParts[0],
                'name' => $nameParts[1],
                'other_names' => ''
            ];
        }
        
        // For 3 or more parts: first is surname, last is name, middle are other_names
        $surname = array_shift($nameParts);
        $name = array_pop($nameParts);
        $other_names = implode(' ', $nameParts);
        
        return [
            'surname' => $surname,
            'name' => $name,
            'other_names' => $other_names
        ];
    }

    /**
     * Update the specified donor
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the donor
            $donor = Donor::findOrFail($id);
            
            // Log the incoming request for debugging
            Log::info('Donor update request', [
                'id' => $id,
                'data' => $request->all()
            ]);
            
            // Format phone number before validation
            $phone = $this->formatPhoneNumber($request->phone);
            
            // Log formatted phone number
            Log::info('Formatted phone number', [
                'original' => $request->phone,
                'formatted' => $phone
            ]);
            
            // Validate the request
            $validator = Validator::make(array_merge($request->all(), ['phone' => $phone]), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'other_name' => 'nullable|string|max:255',
                'email' => 'required|email|max:255',
                'alt_email' => 'nullable|email|max:255',
                'nin' => 'nullable|string|digits:11',
                'phone' => 'required|string|max:20|regex:/^\+[0-9]{10,15}$/',
                'address' => 'nullable|string|max:500',
                'state' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100', // Frontend sends 'city'
                'ranking' => 'nullable|integer|min:1',
            ], [
                'phone.regex' => 'Phone number must be in international format (e.g., +2348012345678)',
            ]);

            if ($validator->fails()) {
                Log::warning('Donor update validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log the update data BEFORE assignment
            Log::info('Donor update data - REQUEST VALUES', [
                'name' => $request->name,
                'surname' => $request->surname,
                'other_name' => $request->other_name,
            ]);
            
            // Explicitly set each field individually to avoid any mass assignment issues
            $donor->name = trim($request->name);
            $donor->surname = trim($request->surname);
            $donor->other_name = $request->other_name ? trim($request->other_name) : null;
            $donor->email = $request->email;
            if ($request->has('alt_email')) {
                $donor->alt_email = $request->alt_email;
            }
            if ($request->has('nin')) {
                $donor->nin = $request->nin;
            }
            $donor->phone = $phone;
            $donor->address = $request->address;
            $donor->state = $request->state;
            $donor->lga = $request->city;
            if ($request->has('ranking')) {
                $donor->ranking = $request->ranking;
            }
            
            // Log values BEFORE saving
            Log::info('Donor update data - MODEL VALUES BEFORE SAVE', [
                'name' => $donor->name,
                'surname' => $donor->surname,
                'other_name' => $donor->other_name,
            ]);
            
            $donor->save();
            
            // Refresh to get actual database values
            $donor->refresh();
            
            // Load relationships for response
            $donor->load(['faculty', 'department']);

            // Log values AFTER saving to verify
            Log::info('Donor updated successfully - DATABASE VALUES AFTER SAVE', [
                'id' => $donor->id,
                'name' => $donor->name,
                'surname' => $donor->surname,
                'other_name' => $donor->other_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Donor updated successfully',
                'data' => [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'surname' => $donor->surname,
                    'other_names' => $donor->other_name,
                    'full_name' => trim(implode(' ', array_filter([$donor->surname, $donor->name, $donor->other_name]))),
                    'email' => $donor->email,
                    'phone' => $donor->phone,
                    'address' => $donor->address,
                    'state' => $donor->state,
                    'city' => $donor->lga, // Map database 'lga' back to 'city' for frontend
                    'ranking' => $donor->ranking,
                    'donor_type' => $donor->donor_type,
                    'faculty_name' => $donor->faculty?->name,
                    'department_name' => $donor->department?->name,
                    'faculty' => $donor->faculty,
                    'department' => $donor->department,
                    'graduation_year' => $donor->graduation_year,
                    'entry_year' => $donor->entry_year,
                    'registration_number' => $donor->registration_number,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Donor not found', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Donor not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating donor', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating donor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove all non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If it doesn't start with +, assume it's a Nigerian number
        if (!str_starts_with($phone, '+')) {
            // Remove leading 0 if present
            if (str_starts_with($phone, '0')) {
                $phone = substr($phone, 1);
            }
            
            // Add Nigerian country code if not present
            if (!str_starts_with($phone, '234')) {
                $phone = '234' . $phone;
            }
            
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Store a newly created donor (for non-addressable alumni)
     */
    /**
     * Store a newly created donor (for non-addressable alumni)
     */
    public function store(Request $request)
    {
        // Start transaction to ensure atomicity
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            try {
                Log::info('Creating new donor', [
                    'data' => $request->all()
                ]);

                // Format phone number before validation
                $phone = $this->formatPhoneNumber($request->phone);
                
                // Validate the request — no unique constraints here, handled manually below
                $validator = Validator::make(array_merge($request->all(), ['phone' => $phone]), [
                    'name'            => 'required|string|max:255',
                    'surname'         => 'required|string|max:255',
                    'other_name'      => 'nullable|string|max:255',
                    'gender'          => 'nullable|in:male,female',
                    'country'         => 'nullable|string|max:100',
                    'nationality'     => 'nullable|string|max:100',
                    'state'           => 'nullable|string|max:100',
                    'lga'             => 'nullable|string|max:100',
                    'city'            => 'nullable|string|max:100',
                    'address'         => 'nullable|string|max:500',
                    'email'           => 'required|email|max:255',
                    'phone'           => 'required|string|max:30',
                    'donor_type'      => 'required|string',
                ]);

                if ($validator->fails()) {
                    $firstError = collect($validator->errors()->toArray())->flatten()->first();
                    Log::warning('Donor creation validation failed', [
                        'errors' => $validator->errors()->toArray()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => $firstError ?? 'Validation failed',
                        'errors'  => $validator->errors()
                    ], 422);
                }

                // If the email is already registered, handle gracefully
                $existingDonor = \App\Models\Donor::where('email', $request->email)->first();
                if ($existingDonor) {
                    $existingSession = \App\Models\DonorSession::where('donor_id', $existingDonor->id)->first();
                    if ($existingSession) {
                        return response()->json([
                            'success' => false,
                            'message' => 'This email is already registered. Please login instead.',
                        ], 409);
                    }
                    // Donor exists but has no session — allow registration flow to continue
                    Log::info('Donor creation: email exists, no session, returning existing donor', [
                        'donor_id' => $existingDonor->id,
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Donor registered successfully',
                        'data'    => ['donor' => $existingDonor],
                    ], 201);
                }

                // Create the donor
                $donor = Donor::create([
                    'name'            => $request->name,
                    'surname'         => $request->surname,
                    'other_name'      => $request->other_name,
                    'gender'          => $request->gender,
                    'country'         => $request->country ?? $request->nationality,
                    'email'           => $request->email,
                    'alt_email'       => $request->alt_email,
                    'nin'             => $request->nin,
                    'phone'           => $phone,
                    'address'         => $request->address,
                    'state'           => $request->state,
                    'lga'             => $request->lga ?? $request->city,
                    'donor_type'      => $request->donor_type,
                    'nationality'     => $request->nationality ?? $request->country,
                    'reg_number'      => $request->reg_number,
                    'entry_year'      => $request->entry_year,
                    'graduation_year' => $request->graduation_year,
                    'faculty_id'      => null,
                    'department_id'   => null,
                    'ranking'         => null,
                ]);

                Log::info('Donor created successfully', [
                    'id' => $donor->id,
                    'name' => $donor->name,
                    'email' => $donor->email
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Donor registered successfully',
                    'data' => [
                        'donor' => $donor,
                    ]
                ], 201);

            } catch (\Exception $e) {
                Log::error('Error creating donor', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                // Transaction will automatically rollback if exception is thrown
                throw $e;
            }
        });
    }

    /**
     * Return donation history for the currently authenticated donor.
     * Resolves via DonorSession (session_id param) first, then device-based fallback.
     */
    public function donationHistory(Request $request)
    {
        // resolveDonor checks: session_id (DonorSession) → X-Device-Session → fingerprint
        $donor = $this->resolveDonor($request);

        if (!$donor) {
            return response()->json(['donations' => []], 200);
        }

        $donations = \App\Models\Donation::where('donor_id', $donor->id)
            ->where('status', 'completed')
            ->with('project:id,project_title')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['donations' => $donations], 200);
    }

    /**
     * Get alumni donors without ranking for contact tab
     */
    public function getAlumniContacts(Request $request)
    {
        try {
            // Get alumni donors where ranking is null
            $alumni = Donor::with(['faculty', 'department'])
                ->whereIn('donor_type', ['addressable_alumni', 'non_addressable_alumni'])
                ->whereNull('ranking')
                ->orderBy('surname')
                ->orderBy('name')
                ->get();

            // Transform the data for frontend
            $contacts = $alumni->map(function ($donor) {
                return [
                    'id' => $donor->id,
                    'full_name' => $donor->full_name,
                    'name' => $donor->name,
                    'surname' => $donor->surname,
                    'other_name' => $donor->other_name,
                    'email' => $donor->email,
                    'phone' => $donor->phone,
                    'reg_number' => $donor->reg_number,
                    'graduation_year' => $donor->graduation_year,
                    'entry_year' => $donor->entry_year,
                    'donor_type' => $donor->donor_type,
                    'faculty_name' => $donor->faculty?->current_name,
                    'department_name' => $donor->department?->current_name,
                    'faculty' => $donor->faculty ? [
                        'id' => $donor->faculty->id,
                        'name' => $donor->faculty->current_name,
                        'code' => $donor->faculty->code ?? null
                    ] : null,
                    'department' => $donor->department ? [
                        'id' => $donor->department->id,
                        'name' => $donor->department->current_name,
                        'code' => $donor->department->code ?? null
                    ] : null,
                    'address' => $donor->address,
                    'state' => $donor->state,
                    'lga' => $donor->lga,
                    'nationality' => $donor->nationality,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $contacts,
                'total' => $contacts->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching alumni contacts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching alumni contacts: ' . $e->getMessage()
            ], 500);
        }
    }
}
