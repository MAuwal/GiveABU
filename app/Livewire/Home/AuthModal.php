<?php

namespace App\Livewire\Home;

use Livewire\Component;

class AuthModal extends Component
{
    public $show = false;
    public $mode = 'login'; // 'login' or 'register'

    protected $listeners = [
        'openLoginModal' => 'openLogin',
        'openRegistrationModal' => 'openRegister',
        'closeAuthModal' => 'close',
    ];

    public function openLogin()
    {
        $this->mode = 'login';
        $this->show = true;
    }

    public function openRegister()
    {
        $this->mode = 'register';
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.home.auth-modal');
    }
}
?>
