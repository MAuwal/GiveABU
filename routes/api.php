<?php

use App\Http\Controllers\DonorController;
use App\Http\Controllers\InterswitchPaymentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SquadPaymentController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DonorMessageController;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\FacultiesVisionController;
use App\Http\Controllers\Api\DepartmentVisionController;
use App\Http\Controllers\Api\DonorsController;
use App\Http\Controllers\Api\DonorSessionController;
use App\Http\Controllers\Api\DonorTierController;

// CORS preflight — catch all OPTIONS before auth middleware can reject them
Route::options('{any}', function () {
    return response()->noContent();
})->where('any', '.*');

// Donor tiers
Route::get('/donor-tiers', [DonorTierController::class, 'index']);
Route::post('/donor-tiers', [DonorTierController::class, 'store']);
Route::get('/donor-tiers/{donorTier}', [DonorTierController::class, 'show']);
Route::put('/donor-tiers/{donorTier}', [DonorTierController::class, 'update']);
Route::delete('/donor-tiers/{donorTier}', [DonorTierController::class, 'destroy']);

// Public routes (no authentication required) - like search endpoints
Route::post('/donors', [\App\Http\Controllers\Api\DonorsController::class, 'store']); // NEW REFACTORED CONTROLLER
Route::get('/donors/search/{reg_number}', [DonorController::class, 'searchByRegNumber'])
    ->where('reg_number', '.*');
Route::get('/donors/search/phone/{phone}', [DonorController::class, 'searchByPhone'])
    ->where('phone', '.*');
Route::get('/donors/search/email/{email}', [DonorController::class, 'searchByEmail'])
    ->where('email', '.*');
Route::put('/donors/{id}', [\App\Http\Controllers\Api\DonorsController::class, 'update']); 

// Route::get('/donors', [DonorController::class, 'index']); // Moved to messaging section for consistency

// Session routes (public)
Route::post('/session/create', [SessionController::class, 'create']);
Route::post('/session/check', [SessionController::class, 'check']);
Route::post('/session/login', [SessionController::class, 'login']);
Route::post('/session/login-with-donor', [SessionController::class, 'loginWithDonor']); // New donor-based login
Route::post('/session/logout', [SessionController::class, 'logout']);

// Verification routes (public)
Route::post('/verification/send-sms', [VerificationController::class, 'sendSMS']);
Route::post('/verification/send-email', [VerificationController::class, 'sendEmail']);
Route::post('/verification/verify-sms', [VerificationController::class, 'verifySMS']);
Route::post('/verification/verify-email', [VerificationController::class, 'verifyEmail']);

// Projects (public)
Route::get('/projects', [ProjectController::class, 'index']);

// Public Faculty/Department/Program data
Route::get('/departments', [\App\Http\Controllers\Api\DepartmentController::class, 'index']);
Route::get('/faculties', [\App\Http\Controllers\Api\FacultyController::class, 'index']);
Route::get('/programs', [\App\Http\Controllers\Api\ProgramController::class, 'index']);


// Donations (public - no authentication required)
Route::post('/donations', [DonorController::class, 'makeDonation']);

// Donor types (public)
Route::get('/donor-types', function () {
    return response()->json([
        'types' => [
            'addressable_alumni' => 'Addressable Alumni',
            'non_addressable_alumni' => 'Non-addressable Alumni',
            'staff' => 'Staff',
            'anonymous' => 'Anonymous'
        ]
    ]);
});

// Projects with photos for slider (public)
Route::get('/projects-with-photos', function () {
    return \App\Models\Project::where('status', 'active')->with(['photos', 'details'])->orderBy('created_at', 'desc')->get()->map(function ($project) {
        return [
            'id' => $project->id,
            'project_title' => $project->project_title,
            'project_description' => $project->project_description,
            'icon_image_url' => $project->icon_image_url,
            'target' => $project->target ?? 0, // Fundraising target in naira
            'raised' => $project->raised ?? 0, // Amount raised in naira
            'status' => $project->status,
            'created_at' => $project->created_at,
            'project_details' => $project->details ? [
                'background' => $project->details->background,
                'key_challenges' => $project->details->challenges,
                'challenges' => $project->details->challenges,
                'proposed_interventions' => $project->details->proposed_interventions,
                'expected_outcomes' => $project->details->expected_outcomes,
                'beneficiaries' => $project->details->beneficiaries,
                'budget_estimate' => $project->details->budget_estimates,
                'budget_estimates' => $project->details->budget_estimates,
            ] : null,
            'photos' => $project->photos->map(function ($photo) {
                return [
                    'image_url' => $photo->image_url,
                    'title' => $photo->title,
                    'description' => $photo->description,
                ];
            }),
        ];
    });
});

// Faculty and Department vision for cascading dropdowns (public)
Route::get('/faculty-vision', function (Request $request) {
    $entryYear = $request->query('entry_year');
    $graduationYear = $request->query('graduation_year');
    
    $query = \App\Models\FacultyVision::query();
    
    if ($entryYear && $graduationYear) {
        // Handle date comparison (assuming date columns are YYYY-MM-DD)
        $query->whereYear('start_year', '<=', $entryYear)
              ->whereYear('end_year', '>=', $graduationYear);
    }
    
    $visions = $query->get();

    // FALLBACK: If no visions found, return all faculties
    if ($visions->isEmpty()) {
        $faculties = \App\Models\Faculty::orderBy('current_name')->get();
        return response()->json([
            'success' => true,
            'fallback' => true,
            'data' => $faculties->map(function ($f) {
                return ['id' => $f->id, 'name' => $f->current_name];
            })
        ]);
    }
    
    return response()->json([
        'success' => true,
        'data' => $visions->map(function ($vision) {
            return [
                'id' => $vision->id,
                'name' => $vision->name,
                'start_year' => $vision->start_year,
                'end_year' => $vision->end_year
            ];
        })
    ]);
});

Route::get('/department-vision', function (Request $request) {
    $facultyId = $request->query('faculty_id');
    $entryYear = $request->query('entry_year');
    $graduationYear = $request->query('graduation_year');
    
    $query = \App\Models\DepartmentVision::query();
    
    if ($facultyId) {
        $query->where('faculty_id', $facultyId);
    }
    
    if ($entryYear && $graduationYear) {
        $query->whereYear('start_year', '<=', $entryYear)
              ->whereYear('end_year', '>=', $graduationYear);
    }
    
    $visions = $query->get();
    
    // FALLBACK: If no visions found, return departments for the faculty
    if ($visions->isEmpty()) {
        $deptQuery = \App\Models\Department::orderBy('current_name');
        if ($facultyId) {
            $deptQuery->where('faculty_id', $facultyId);
        }
        $departments = $deptQuery->get();
        
        return response()->json([
            'success' => true,
            'fallback' => true,
            'data' => $departments->map(function ($d) {
                return ['id' => $d->id, 'name' => $d->current_name];
            })
        ]);
    }
    
    return response()->json([
        'success' => true,
        'data' => $visions->map(function ($vision) {
            return [
                'id' => $vision->id,
                'name' => $vision->name,
                'start_year' => $vision->start_year,
                'end_year' => $vision->end_year
            ];
        })
    ]);
});

// Add donation history as a public route
Route::get('/donations/history', [DonorController::class, 'donationHistory']);

// Alumni contacts for contact tab (public)
Route::get('/alumni/contacts', [DonorController::class, 'getAlumniContacts']);

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working correctly',
        'timestamp' => now()->toISOString(),
        'device_sessions_count' => \App\Models\DeviceSession::count(),
        'donors_count' => \App\Models\Donor::count()
    ]);
});



// Statistics routes (public for admin dashboard)
Route::prefix('statistics')->group(function () {
    Route::get('/donors', [\App\Http\Controllers\Api\StatisticsController::class, 'donors']);
    Route::get('/donor-types', [\App\Http\Controllers\Api\StatisticsController::class, 'donorTypes']);
    Route::get('/departments', [\App\Http\Controllers\Api\StatisticsController::class, 'departments']);
    Route::get('/states', [\App\Http\Controllers\Api\StatisticsController::class, 'states']);
    Route::get('/lgas', [\App\Http\Controllers\Api\StatisticsController::class, 'lgas']);
    Route::get('/gender', [\App\Http\Controllers\Api\StatisticsController::class, 'gender']);
    Route::get('/summary', [\App\Http\Controllers\Api\StatisticsController::class, 'summary']);
});
Route::get('/faculties/{id}/departments', [\App\Http\Controllers\Api\FacultyController::class, 'departments']);
Route::get('/public/faculties', [\App\Http\Controllers\Api\FacultyController::class, 'index']);
Route::get('/public/faculties/{id}/departments', [\App\Http\Controllers\Api\FacultyController::class, 'departments']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Donor management
    Route::apiResource('donors', DonorController::class)->except(['update']); // Exclude update as it's public
    Route::get('/donors/faculty/{faculty_id}', [DonorController::class, 'getByFaculty']);
    Route::get('/donors/department/{department_id}', [DonorController::class, 'getByDepartment']);
    
    // Donations (summary requires auth)
    Route::get('/donations/summary', [DonorController::class, 'donationSummary']);
    
    // Rankings
    Route::get('/rankings', [RankingController::class, 'index']);
    Route::get('/rankings/top-donors', [RankingController::class, 'topDonors']);
    Route::get('/rankings/faculty', [RankingController::class, 'facultyRankings']);
    Route::get('/rankings/department', [RankingController::class, 'departmentRankings']);
    
    // Protected Faculty and Department data (authenticated users)
    // These are duplicates but with authentication - kept for backward compatibility
    
    Route::get('/faculties/{faculty}/visions', function (\App\Models\Faculty $faculty) {
        return $faculty->visions;
    });
    
    Route::get('/faculties/{faculty}/visions', function (\App\Models\Faculty $faculty) {
        return $faculty->visions;
    });
    
    Route::get('/departments/{department}/visions', function (\App\Models\Department $department) {
        return $department->visions;
    });
    
});

// Admin routes (protected by role middleware)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('projects', ProjectController::class)->except(['index', 'show']);
    Route::post('/projects/{project}/photos', [ProjectController::class, 'addPhoto']);
    Route::delete('/projects/{project}/photos/{photo}', [ProjectController::class, 'removePhoto']);
    
    Route::get('/donors/upload', [DonorController::class, 'uploadForm']);
    Route::post('/donors/upload', [DonorController::class, 'uploadAlumni']);
    
    Route::get('/statistics', [DonorController::class, 'statistics']);
    Route::post('/notifications/send', [DonorController::class, 'sendNotifications']);
});

// Device session management (public - no authentication required)
Route::post('/devices/register', [DeviceController::class, 'register']);
Route::post('/devices/test-register', function(Request $request) {
    // Test endpoint to debug device registration
    return response()->json([
        'received_data' => $request->all(),
        'headers' => $request->headers->all(),
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);
});
Route::post('/sessions/check', [DeviceController::class, 'checkSession']);
Route::get('/devices/check/{fingerprint}', [DeviceController::class, 'checkDevice']);
Route::get('/device/check', [DeviceController::class, 'check']); // Keep existing
Route::post('/device/session', [DeviceController::class, 'createSession']); // Keep existing
Route::get('/device/donor-info', [DeviceController::class, 'getDonorInfo']); // Keep existing

// Payment routes (public - no authentication required)
Route::post('/payments/initialize', [PaymentController::class, 'initialize']);
Route::get('/payments/verify/{reference}', [PaymentController::class, 'verify']);

// Squad mobile API verification
Route::get('/squad/verify/{reference}', [SquadPaymentController::class, 'verifyApi']);

// Interswitch mobile API verification
Route::get('/interswitch/verify/{reference}', [InterswitchPaymentController::class, 'verifyApi']);

Route::get('/payments/test', [PaymentController::class, 'test']); // Test configuration
Route::post('/squad/pay', [SquadPaymentController::class, 'initiate'])->name('api.squad.pay');
Route::post('/interswitch/pay', [InterswitchPaymentController::class, 'initiate'])->name('api.interswitch.pay');
// Route::match(['get', 'post'], '/interswitch/process-checkout/{txn_ref}', [InterswitchPaymentController::class, 'processCheckout'])->name('api.interswitch.process-checkout');
Route::any('/interswitch/process-checkout/{txn_ref}', [InterswitchPaymentController::class, 'processCheckout']);

Route::any('/interswitch/process-checkout/{txn_ref}', [InterswitchPaymentController::class, 'processCheckout']);

Route::post('/interswitch/redirect', [InterswitchPaymentController::class, 'handleRedirect'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/interswitch/webhook', [InterswitchPaymentController::class, 'webhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Webhook (no CSRF protection needed)
Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/squad/webhook', [SquadPaymentController::class, 'webhook'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->name('api.squad.webhook');

use App\Http\Controllers\Api\MessageController;

Route::get('/donor/{donor}/messages', [DonorMessageController::class, 'index']);

// Messaging API (Authenticated)
Route::prefix('messages')->group(function () {
    Route::get('/received', [MessageController::class, 'getReceivedMessages']);
    Route::get('/sent', [MessageController::class, 'getSentMessages']);
    Route::post('/', [MessageController::class, 'send']);
    Route::put('/{id}/read', [MessageController::class, 'markAsRead']);
    Route::get('/unread-count', [MessageController::class, 'getUnreadCount']);
    Route::get('/conversation', [MessageController::class, 'getConversation']); // Keeping for internal use
});

// Alumni Directory (Authenticated)
Route::get('/donors', [DonorController::class, 'index']);

Route::post('/send-sms', [SmsController::class, 'sendSms']);
Route::get('/sms-messages', [SmsController::class, 'getMessages']);

Route::get('alumni/lookup', [AlumniController::class, 'lookup']); // ?reg_number=
Route::get('alumni/search', [AlumniController::class, 'search']); // ?query=
Route::get('faculties-visions', [FacultiesVisionController::class, 'index']); // ?year=
Route::get('department-visions', [DepartmentVisionController::class, 'index']); // ?year=&faculty_id=
Route::post('donors', [DonorsController::class, 'store']);
Route::put('donors/{id}', [DonorsController::class, 'update']);
Route::post('donors/check-device', [DonorsController::class, 'checkByDevice']);
Route::get('donors/search/email/{email}', [DonorsController::class, 'searchByEmail']);
Route::get('donors/search/addressable-alumni/{regNumber}', [DonorsController::class, 'searchAddressableAlumni']);

// Donor Sessions routes (public - standard login/logout functionality)
Route::post('/donor-sessions/register', [DonorSessionController::class, 'register']);
Route::post('/donor-sessions/login', [DonorSessionController::class, 'login']);
Route::post('/donor-sessions/google-login', [DonorSessionController::class, 'googleLogin']);
Route::post('/donor-sessions/google-register', [DonorSessionController::class, 'googleRegister']);

// Email Verification
Route::post('/donor-sessions/send-verification', [DonorSessionController::class, 'sendVerificationEmail']);

// Password Reset Routes (link flow)
Route::post('/donor-sessions/forgot-password', [DonorSessionController::class, 'forgotPassword']);
Route::get('/donor-sessions/reset/{token}', [DonorSessionController::class, 'getResetToken']);
Route::post('/donor-sessions/reset/{token}', [DonorSessionController::class, 'resetPasswordWithToken']);

// Debug route for Google token verification (remove in production)
Route::get('/test-google-token', function (Request $request) {
    $token = $request->get('token');
    if (!$token) {
        return response()->json(['error' => 'Token required. Use: ?token=YOUR_TOKEN'], 400);
    }

    $googleAuth = app(\App\Services\GoogleAuthService::class);
    $result = $googleAuth->verifyToken($token);

    return response()->json([
        'success' => $result !== false,
        'data' => $result,
        'debug' => [
            'client_id_from_config' => config('services.google.client_id'),
            'client_id_from_env' => env('GOOGLE_CLIENT_ID'),
            'client_id_empty' => empty(config('services.google.client_id')),
        ],
    ]);
});

Route::post('/donor-sessions/logout', [DonorSessionController::class, 'logout']);
Route::post('/donor-sessions/me', [DonorSessionController::class, 'me']);
Route::get('/donor-sessions/check-device', [DonorSessionController::class, 'checkDevice']);

// Profile update routes (require authentication - using session_id for now)
Route::put('/donor-sessions/{session_id}/username', [DonorSessionController::class, 'updateUsername']);
Route::put('/donor-sessions/{session_id}/password', [DonorSessionController::class, 'updatePassword']);
Route::post('/donor-sessions/profile', [DonorSessionController::class, 'createOrUpdateProfile']); // ✅ Create/update donor profile for authenticated user
Route::post('/donors/{id}/profile-image', [DonorsController::class, 'uploadProfileImage']);
