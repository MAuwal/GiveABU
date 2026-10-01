<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\On;

class AddUser extends Component
{
    public $showModal = false;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role_id;
    
    public $roles;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    #[On('open-add-user-modal')]
    public function openModal()
    {
        $this->reset();
        $this->roles = Role::all();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function saveUser()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => $this->role_id,
        ]);

        session()->flash('message', 'User created successfully.');
        $this->dispatch('user-added');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.add-user');
    }
} 