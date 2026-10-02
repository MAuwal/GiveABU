<?php

namespace App\Livewire\Home;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

class LoginModal extends Component
{
    public $show = false;
    public $username = '';
    public $password = '';
    public $error = '';
    public $loading = false;

    protected $listeners = [
        'openLoginModal' => 'open',
        'save-auth-token' => 'saveAuthToken'
    ];

    public function open()
    {
        $this->show = true;
        $this->reset(['username', 'password', 'error', 'loading']);
        
        // Initialize Google Sign-In button when modal opens
        $this->dispatch('initGoogleLogin', componentId: 'login');
    }

    public function close()
    {
        $this->show = false;
        $this->reset(['username', 'password', 'error', 'loading']);
    }

    public function login()
    {
        $this->error = '';
        $this->loading = true;

        $this->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $request = Request::create('/api/donor-sessions/login', 'POST', [
                'username' => $this->username,
                'password' => $this->password,
            ]);
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');

            $controller = app(\App\Http\Controllers\Api\DonorSessionController::class);
            $response = $controller->login($request);
            $data = json_decode($response->getContent(), true);

            if (!$response->isSuccessful()) {
                throw new \Exception($data['message'] ?? 'Login failed');
            }

            // Store token in session for Livewire persistence
            if (isset($data['token'])) {
                Session::regenerate();
                Session::put('donor_token', $data['token']);
            }

            // Open the donor dashboard after successful authentication.
            $this->close();
            $this->dispatch('login-success');
            $this->redirectRoute('donor.dashboard');
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }

    public function saveAuthToken($token)
    {
        if (app(\App\Services\DonorTokenService::class)->resolve($token)) {
            Session::regenerate();
            Session::put('donor_token', $token);
            $this->close();
            $this->dispatch('login-success');
            $this->redirectRoute('donor.dashboard');
        }
    }

    public function render()
    {
        return view('livewire.home.login-modal');
    }
}
