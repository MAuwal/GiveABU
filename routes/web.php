<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Admin\FacultyController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DonorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SquadPaymentController;
use Illuminate\Support\Facades\Storage;

// Serve storage files - this route must be registered before Laravel's default storage route
// This ensures our custom route takes precedence
Route::match(['GET', 'OPTIONS', 'HEAD'], '/storage/{path}', function ($path) {
    // Handle OPTIONS request for CORS preflight
    if (request()->isMethod('OPTIONS')) {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS, HEAD')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
    }
    
    // Decode the path in case it's URL encoded
    $decodedPath = urldecode($path);
    
    // Prevent directory traversal attacks
    if (str_contains($decodedPath, '..') || str_contains($decodedPath, "\0")) {
        abort(403, 'Invalid path');
    }
    
    // Check public/storage/ first (new files — public disk root = public/storage),
    // then fall back to storage/app/public/ for files uploaded before the disk change.
    $publicPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, public_path('storage/' . $decodedPath));
    $legacyPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, storage_path('app/public/' . $decodedPath));

    if (file_exists($publicPath) && is_file($publicPath)) {
        $filePath = $publicPath;
    } elseif (file_exists($legacyPath) && is_file($legacyPath)) {
        $filePath = $legacyPath;
    } else {
        \Log::warning('Storage file not found', [
            'requested_path' => $path,
            'decoded_path' => $decodedPath,
            'public_path' => $publicPath,
            'legacy_path' => $legacyPath,
        ]);
        abort(404, 'File not found');
    }
    
    // Determine MIME type
    $mimeType = mime_content_type($filePath);
    if (!$mimeType) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    // Use response()->file() which properly handles file serving with correct headers
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS, HEAD',
        'Access-Control-Allow-Headers' => 'Content-Type',
        'X-Content-Type-Options' => 'nosniff',
    ]);
})->where('path', '.*')->name('storage.serve');

// Welcome page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// About page
Route::get('/about', [\App\Http\Controllers\AboutController::class, 'index'])->name('about');
Route::get('/projects', [\App\Http\Controllers\ProjectsController::class, 'index'])->name('projects');
Route::get('/projects/{id}', [\App\Http\Controllers\SingleProjectController::class, 'show'])->name('project.single');

// Projects page
Route::view('/projects', 'projects')->name('projects');

// Admin root route - redirect based on authentication status
Route::get('/admin', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('admin.login');
})->name('admin.index');

// Admin routes
Route::prefix('admin')->group(function () {
    // Admin login (public)
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    
    // Protected admin routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        
        // Transactions page
        Route::get('/transactions', function () {
            return view('admin.transactions');
        })->name('admin.transactions');

        // Transactions CSV export
        Route::get('/transactions/export', [\App\Http\Controllers\Admin\TransactionExportController::class, 'export'])->name('admin.transactions.export');
        
        // The alumni upload is now a Livewire component on the dashboard
        
        // Faculty Management
        Route::get('/faculties', [\App\Http\Controllers\Admin\FacultyController::class, 'index'])->name('admin.faculty.index');

        
        // Department Management
        Route::get('/departments', [\App\Http\Controllers\Admin\DepartmentController::class, 'index'])->name('admin.department.index');
        
        // Projects management
        Route::get('/projects', [ProjectController::class, 'index'])->name('admin.projects');
        Route::get('/projects/donations', [\App\Http\Controllers\ProjectController::class, 'donationsOverview'])->name('admin.projects.donations');
        Route::get('/projects/{project}/details', \App\Livewire\Admin\ViewProjectDetails::class)->name('admin.project-details');
        
        // Project Categories
        Route::get('/project-categories', [\App\Http\Controllers\Admin\ProjectCategoryController::class, 'index'])->name('admin.project-categories.index');
        
        // Excel report export (multi-sheet, with charts)
        Route::get('/excel-export', [\App\Http\Controllers\Admin\ExcelExportController::class, 'export'])->name('admin.excel.export');

        // Reports
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportExportController::class, 'export'])->name('admin.reports.export');
        Route::view('/reports', 'admin.reports.index')->name('admin.reports.index');
        
        // Donor statistics
        Route::get('/statistics', \App\Livewire\Admin\StatisticsManager::class)->name('admin.statistics');
        
        // Email/SMS management
        // Email/SMS management
        Route::prefix('notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/templates', [\App\Http\Controllers\Admin\NotificationController::class, 'templates'])->name('templates');
            Route::get('/templates/create', [\App\Http\Controllers\Admin\NotificationController::class, 'createTemplate'])->name('templates.create');
            Route::get('/templates/{id}/edit', [\App\Http\Controllers\Admin\NotificationController::class, 'editTemplate'])->name('templates.edit');
            Route::get('/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('send');
            Route::get('/send-sms', [\App\Http\Controllers\Admin\NotificationController::class, 'sendSmsPage'])->name('send.sms');
            Route::get('/logs', [\App\Http\Controllers\Admin\NotificationController::class, 'logs'])->name('logs');
        });
        
        // Donor Management
        Route::get('/donors', [\App\Http\Controllers\Admin\DonorController::class, 'index'])->name('admin.donors.index');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        
        // Role Management
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('admin.roles.index');
        
        // Permission Management
        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('admin.permissions.index');

        // System Maintenance - Clear Cache
        Route::get('/system/clear-cache', function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                return response()->json(['success' => true, 'message' => 'All caches cleared successfully!']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()]);
            }
        })->name('admin.clear-cache');
    });
});

// Donations (public)
Route::post('/donations', [DonorController::class, 'makeDonation']);

// If you want donation history/summary to remain protected, leave them inside the group:
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/donations/history', [DonorController::class, 'donationHistory']);
    Route::get('/donations/summary', [DonorController::class, 'donationSummary']);
});

// Email verification (link from verification email)
Route::get('/verify-email/{token}', [\App\Http\Controllers\Api\DonorSessionController::class, 'verifyEmail'])->name('email.verify');

// Squad payment confirmation page (Squad redirects here after checkout)
Route::get('/donation/thank-you', [SquadPaymentController::class, 'confirm'])->name('donation.thankyou');

// Paystack thank-you page (Livewire verifies inline, then redirects here)
Route::get('/donation/thank-you/paystack', [\App\Http\Controllers\PaymentController::class, 'thankYou'])->name('donation.thankyou.paystack');

// Redirect /login to /admin/login for session expiry or direct access
Route::get('/login', function () {
    return redirect()->route('admin.login');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});
