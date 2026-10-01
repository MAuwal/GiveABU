<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest', 'throttle:6,1'])
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware(['guest', 'throttle:10,1'])
    ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/forgot-password', function () {
    return response()->view('auth.password-recovery', ['admin' => false, 'reset' => false])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
})->name('donor.password.request');
Route::get('/reset-password', function (\Illuminate\Http\Request $request) {
    return response()->view('auth.password-recovery', ['admin' => false, 'reset' => true, 'token' => $request->query('token', '')])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
})->name('donor.password.reset');
Route::post('/donor/forgot-password', [\App\Http\Controllers\Auth\DonorPasswordController::class, 'send'])
    ->middleware('throttle:6,1')->name('donor.password.email');
Route::post('/donor/reset-password', [\App\Http\Controllers\Auth\DonorPasswordController::class, 'reset'])
    ->middleware('throttle:10,1')->name('donor.password.store');
Route::get('/admin/forgot-password', function () {
    return response()->view('auth.password-recovery', ['admin' => true, 'reset' => false])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
})->middleware('guest')->name('password.request');
Route::get('/password-reset/{token}', function (\Illuminate\Http\Request $request, string $token) {
    return response()->view('auth.password-recovery', ['admin' => true, 'reset' => true, 'token' => $token, 'email' => $request->query('email', '')])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
})->middleware('guest')->name('password.reset');
