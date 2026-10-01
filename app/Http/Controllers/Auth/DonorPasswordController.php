<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\DonorSessionController;
use App\Http\Controllers\Controller;
use App\Mail\PasswordResetLinkMail;
use App\Models\Donor;
use App\Models\DonorSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class DonorPasswordController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        // Website links always return to our own reset page.
        $request->request->remove('callback_url');
        $request->validate(['email' => 'required|email|max:255']);
        $email = $request->input('email');
        $donor = Donor::where('email', $email)->first();
        if ($donor && ! DonorSession::where('username', $email)->orWhere('donor_id', $donor->id)->exists()) {
            $key = 'donor-password-setup:'.hash('sha256', strtolower($email));
            if (! RateLimiter::tooManyAttempts($key, 1)) {
                RateLimiter::hit($key, 60);
                $url = URL::temporarySignedRoute('donor.password.setup', now()->addMinutes(10), ['donor' => $donor->id, 'email' => $email]);
                try {
                    Mail::to($email)->send(new PasswordResetLinkMail($url, $email));
                } catch (\Exception $e) {
                    Log::error('Donor password setup email failed', ['exception' => get_class($e)]);
                }
            }
        } else {
            app(DonorSessionController::class)->forgotPassword($request, true);
        }

        return back()->with('status', 'If this email is linked to an eligible account or donor profile, we have sent a password reset link. Check your inbox and spam folder.')->with('recovery_requested', true);
    }

    public function setup(Request $request, Donor $donor)
    {
        abort_unless($donor->email === $request->query('email'), 403);

        return response()->view('auth.password-recovery', [
            'admin' => false, 'reset' => true, 'token' => '', 'formAction' => $request->fullUrl(),
        ])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function storeSetup(Request $request, Donor $donor): RedirectResponse
    {
        $request->validate(['password' => 'required|string|confirmed|min:8']);
        DB::transaction(function () use ($request, $donor) {
            $profile = Donor::whereKey($donor->id)->lockForUpdate()->firstOrFail();
            abort_unless($profile->email === $request->query('email'), 403);
            if (DonorSession::where('username', $profile->email)->orWhere('donor_id', $profile->id)->exists()) {
                throw ValidationException::withMessages(['token' => 'This link has already been used. Request a new password reset link.']);
            }
            DonorSession::create(['username' => $profile->email, 'donor_id' => $profile->id,
                'password' => $request->input('password'), 'auth_provider' => 'email', 'email_verified_at' => now()]);
        }, 5);
        $request->session()->forget('donor_token');
        $request->session()->regenerate();

        return redirect()->route('donor.password.request')->with('status', 'Your password has been set. Return to the website to sign in.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate(['token' => 'required|string|max:128']);
        $response = app(DonorSessionController::class)->resetPasswordWithToken($request, $request->input('token'));
        if (! $response->isSuccessful()) {
            throw ValidationException::withMessages(['token' => 'This reset link is invalid or expired. Request a new link.']);
        }

        $request->session()->forget('donor_token');
        $request->session()->regenerate();

        return redirect()->route('donor.password.request')->with('status', 'Your password has been reset. Return to the website to sign in with your new password.');
    }
}
