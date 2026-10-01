<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;
use Livewire\Attributes\On;

class AddRole extends Component
{
    public $showModal = false;
    public $role_title;
    public $permission_id;
    public $permissions;

    protected $rules = [
        'role_title' => 'required|string|max:255|unique:roles,role_title',
        'permission_id' => 'required|exists:permissions,id',
    ];

    #[On('open-add-role-modal')]
    public function openModal()
    {
        $this->reset();
        $this->resetValidation();
        $this->permissions = Permission::all();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function saveRole()
    {
        $this->validate();

        Role::create([
            'role_title' => $this->role_title,
            'permission_id' => $this->permission_id,
        ]);

        session()->flash('message', 'Role added successfully.');
        $this->dispatch('role-added');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.admin.add-role');
    }
} 