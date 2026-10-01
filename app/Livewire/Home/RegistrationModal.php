<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\DonorSession;
use App\Mail\EmailVerificationMail;

class RegistrationModal extends Component
{
    // ... properties ...
    public $show = false;
    public $donorType = '';
    public $surname = '';
    public $name = '';
    public $otherName = '';
    public $email = '';
    public $phone = '';
    public $state = '';
    public $lga = '';
    public $nationality = 'Nigerian';
    public $username = '';
    public $password = '';
    public $passwordConfirm = '';

    // Alumni fields
    public $entryYear = '';
    public $graduationYear = '';

    // Corporate fields
    public $organisationName = '';

    public $error = '';
    public $success = '';
    public $loading = false;
    public $showAlumniFields = false;
    public $showCorporateFields = false;

    // Post-registration state
    public $registrationComplete = false;
    public $registeredSessionId = null;
    public $verificationSent = false;
    public $resendLoading = false;

    protected $listeners = [
        'openRegistrationModal' => 'open',
        'save-auth-token' => 'saveAuthToken'
    ];

    public function updatedDonorType()
    {
        $this->showAlumniFields = in_array($this->donorType, ['addressable_alumni', 'non_addressable_alumni']);
        $this->showCorporateFields = $this->donorType === 'corporate';
    }

    public function open()
    {
        $this->show = true;
        $this->reset([
            'donorType', 'surname', 'name', 'otherName', 'email', 'phone',
            'state', 'lga', 'nationality', 'password', 'passwordConfirm',
            'entryYear', 'graduationYear', 'organisationName', 'error', 'success', 'loading',
            'showAlumniFields', 'showCorporateFields', 'registrationComplete', 'registeredSessionId', 'verificationSent', 'resendLoading'
        ]);
        $this->nationality = 'Nigerian';

        // Initialize Google Sign-In button when modal opens
        $this->dispatch('initGoogleRegistration', componentId: 'register');
    }

    public function close()
    {
        $this->show = false;
        $this->reset([
            'donorType', 'surname', 'name', 'otherName', 'email', 'phone',
            'state', 'lga', 'nationality', 'password', 'passwordConfirm',
            'entryYear', 'graduationYear', 'organisationName', 'error', 'success', 'loading',
            'showAlumniFields', 'showCorporateFields', 'registrationComplete', 'registeredSessionId', 'verificationSent', 'resendLoading'
        ]);
    }

    public function register()
    {
        $this->error = '';
        $this->success = '';
        $this->loading = true;

        $this->validate([
            'donorType' => 'required|string',
            'surname' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'state' => 'required|string',
            'lga' => 'required|string',
            'nationality' => 'required|string',
            'password' => 'required|string|min:8',
            'passwordConfirm' => 'required|string|same:password',
        ]);

        if ($this->password !== $this->passwordConfirm) {
            $this->error = 'Passwords do not match!';
            $this->loading = false;
            return;
        }

        try {
            // Step 1: Create donor
            $donorData = [
                'password' => $this->password,
                'surname' => $this->surname,
                'name' => $this->name,
                'other_name' => $this->otherName ?: null,
                'email' => $this->email,
                'phone' => $this->phone,
                'state' => $this->state,
                'lga' => $this->lga,
                'nationality' => $this->nationality,
                'donor_type' => $this->donorType,
            ];

            if ($this->showAlumniFields) {
                $donorData['entry_year'] = $this->entryYear ? (int)$this->entryYear : null;
                $donorData['graduation_year'] = $this->graduationYear ? (int)$this->graduationYear : null;
            }

            if ($this->showCorporateFields) {
                $donorData['organization_name'] = $this->organisationName;
            }

            $donorRequest = Request::create('/api/donors', 'POST', $donorData);
            $donorRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
            $donorRequest->attributes->set('web_registration', true);

            $donorController = app(\App\Http\Controllers\Api\DonorsController::class);
            $donorResponse = $donorController->store($donorRequest);
            $donorResult = json_decode($donorResponse->getContent(), true);

            if (!$donorResponse->isSuccessful()) {
                // Surface any field-level validation errors to Livewire's $errors bag
                if (!empty($donorResult['errors']) && is_array($donorResult['errors'])) {
                    foreach ($donorResult['errors'] as $field => $messages) {
                        $this->addError($field, is_array($messages) ? $messages[0] : $messages);
                    }
                }
                throw new \Exception($donorResult['message'] ?? 'Registration failed. Please check the highlighted fields.');
            }

            $token = $donorResult['data']['session_token'];
            Session::regenerate();
            Session::put('donor_token', $token);
            $this->registeredSessionId = $donorResult['data']['session_id'];

            // Auto-login: session already set above, notify header immediately
            $this->dispatch('registration-success');

            // Auto-send verification email (silent fail — never block the user)
            $this->dispatchVerificationEmail($this->registeredSessionId, $this->email, $this->name);

            // Show success state inside the modal instead of closing
            $this->registrationComplete = true;
            $this->success = 'Registration successful!';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    /**
     * User clicked "Resend Verification Email" inside the success panel.
     */
    public function resendVerificationEmail()
    {
        if (!$this->registeredSessionId) return;

        $this->resendLoading = true;
        $this->dispatchVerificationEmail($this->registeredSessionId, $this->email, $this->name);
        $this->verificationSent = true;
        $this->resendLoading = false;
    }

    /**
     * User clicked "Skip for Now" — close modal and reload page (they are already logged in).
     */
    public function skipVerification()
    {
        $this->show = false;
        $this->registrationComplete = false;
        $this->js('window.location.reload()');
    }

    /**
     * Internal helper: generate token, persist it, send the email.
     * Failures are logged silently — email verification is optional.
     */
    private function dispatchVerificationEmail(int $sessionId, string $email, string $name): void
    {
        try {
            $session = app(\App\Services\DonorTokenService::class)->resolve(Session::get('donor_token'));
            if (!$session || (int) $session->id !== $sessionId) {
                return;
            }
            $email = $session->username;
            $token = Str::random(64);
            DonorSession::where('id', $sessionId)->update(['email_verification_token' => $token]);

            $verificationUrl = url('/verify-email/' . $token);
            Mail::to($email)->send(new EmailVerificationMail($verificationUrl, $name));

            $this->verificationSent = true;
        } catch (\Exception $e) {
            Log::error('RegistrationModal: failed to send verification email', [
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);
            // Surface mail error so user knows and can retry later
            $this->error = 'Could not send verification email. Please try again later.';
        }
    }

    public function saveAuthToken($token)
    {
        if (app(\App\Services\DonorTokenService::class)->resolve($token)) {
            Session::regenerate();
            Session::put('donor_token', $token);
            $this->close();
            $this->dispatch('login-success');
            $this->js('window.location.reload()');
        }
    }

    public function render()
    {
        return view('livewire.home.registration-modal');
    }
}

