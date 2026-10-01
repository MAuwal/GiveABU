<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\DonorSession;
use App\Mail\EmailVerificationMail;

class HeaderSection extends Component
{
    public $user = null;
    public $isLoggedIn = false;
    public $isVerified = true;
    public $verificationResent = false;

    protected $listeners = [
        'login-success' => 'checkAuth',
        'registration-success' => 'checkAuth',
        'profile-updated' => 'checkAuth'
    ];

    public function mount()
    {
        $this->checkAuth();
    }

    public function checkAuth()
    {
        try {
            // Check session first
            if (Session::has('donor_token')) {
                $token = Session::get('donor_token');
                
                // Use direct DB query instead of HTTP request to avoid self-request timeout
                $donorSession = app(\App\Services\DonorTokenService::class)->resolve($token)?->load('donor');
                
                if ($donorSession) {
                    $this->user = [
                        'username' => $donorSession->username,
                        'name' => $donorSession->donor->name ?? $donorSession->username,
                        'phone' => $donorSession->donor->phone ?? '',
                        'email' => $donorSession->donor->email ?? $donorSession->username,
                        'avatar' => $donorSession->donor->profile_image ?? null,
                    ];

                    $this->isLoggedIn = true;
                    $this->isVerified = $donorSession->email_verified_at !== null;
                    Log::info('HeaderSection: Auth successful', ['user' => $this->user]);
                    return;
                } else {
                }
            } else {
                Log::info('HeaderSection: No token in session');
            }
            
            $this->user = null;
            $this->isLoggedIn = false;
            $this->isVerified = true;
        } catch (\Exception $e) {
            Log::error('HeaderSection: Error checking auth', ['error' => $e->getMessage()]);
            $this->user = null;
            $this->isLoggedIn = false;
            $this->isVerified = true;
        }
    }

    public function resendVerification()
    {
        if (!Session::has('donor_token')) return;

        $donorSession = app(\App\Services\DonorTokenService::class)->resolve(Session::get('donor_token'))?->load('donor');

        if (!$donorSession || $donorSession->email_verified_at) return;

        try {
            $token = Str::random(64);
            $donorSession->update(['email_verification_token' => $token]);

            $url  = url('/verify-email/' . $token);
            $name = $donorSession->donor->name ?? $donorSession->username;
            Mail::to($donorSession->username)->send(new EmailVerificationMail($url, $name));

            $this->verificationResent = true;
        } catch (\Exception $e) {
            Log::error('HeaderSection: Failed to resend verification email', ['error' => $e->getMessage()]);
        }
    }

    public function logout()
    {
        $token = Session::get('donor_token');
        if (is_string($token)) {
            \Illuminate\Support\Facades\DB::table('donor_access_tokens')->where('token_hash', hash('sha256', $token))->delete();
        }
        Session::forget('donor_token');
        Session::regenerate();
        Session::save();
        $this->js('window.location.href = "/"');
    }

    public function render()
    {
        return view('livewire.home.header-section');
    }
}
